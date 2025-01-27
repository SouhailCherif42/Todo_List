<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/task')]
final class TaskController extends AbstractController
{
    #[Route(name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository, Security $security): Response
    {
        $user = $security->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $tasks = $taskRepository->findBy([], ['deadline' => 'ASC']);
        } else {
            $tasks = $taskRepository->createQueryBuilder('t')
                ->leftJoin('t.owners', 'owner')
                ->leftJoin('t.assignees', 'assignee')
                ->andWhere('owner = :user OR assignee = :user')
                ->setParameter('user', $user)
                ->orderBy('t.deadline', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $doctrine, Security $security, MailerInterface $mailer): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            if (!$user instanceof User) {
                throw new \Exception('L\'utilisateur connecté n\'est pas valide ou non connecté.');
            }

            // Associate the current user (task creator) with the task
            $task->addOwner($user);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            // Send email to the task creator
            $this->sendTaskNotificationEmail($mailer, $task, 'created');

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Task $task,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the status changed
            $originalStatus = $entityManager->getUnitOfWork()->getOriginalEntityData($task)['status'] ?? null;
            if ($task->getStatus() !== $originalStatus) {
                // Send email to the task creator about the status change
                $this->sendTaskNotificationEmail($mailer, $task, 'status changed');
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->get('_token'))) {
            $creator = $task->getOwners()->first();
            $entityManager->remove($task);
            $entityManager->flush();

            // Send email to the task creator about the task deletion
            $this->sendTaskNotificationEmail($mailer, $task, 'deleted');

        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    // Helper function to send email notifications to the task creator
    private function sendTaskNotificationEmail(MailerInterface $mailer, Task $task, string $action): void
    {
        // Get the task creator (first owner)
        $creator = $task->getOwners()->first();
        if ($creator) {
            $email = (new Email())
                ->from('reda@astus.fr') 
                ->to($creator->getEmail())      
                ->subject(sprintf('Task %s Notification', ucfirst($action)))
                ->text(sprintf(
                    'The task "%s" has been %s.',
                    $task->getName(),
                    $action
                ));

            $mailer->send($email);
        }
    }
}
