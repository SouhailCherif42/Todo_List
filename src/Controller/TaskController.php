<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Comment;
use App\Form\TaskType;
use App\Form\CommentType;
use App\Repository\TaskRepository;
use App\Repository\CommentRepository;
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
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
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

            // Gestion de l'upload du fichier
            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move(
                    $this->getParameter('uploads_directory'), // Dossier où stocker les fichiers
                    $newFilename
                );
                $task->setFile($newFilename);
            }

            $task->addOwner($user);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
        }


        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET', 'POST'])]
    public function show(Task $task, Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            $comment->setAuthor($user);
            $comment->setTask($task);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_show', ['id' => $task->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'comments' => $task->getComments(),
            'comment_form' => $form->createView(),
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
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->get('_token'))) {
            // Suppression de la tâche
            $entityManager->remove($task);
            $entityManager->flush();

            // Redirection immédiate vers l'index
            return $this->redirectToRoute('app_task_index');
        }

        // Redirection en cas d'échec
        return $this->redirectToRoute('app_task_show', ['id' => $task->getId()]);
    }

    // Helper function to send email notifications to the task creator
    private function sendTaskNotificationEmail(MailerInterface $mailer, Task $task, string $action): void
    {
        // Get the task creator (first owner)
        $creator = $task->getOwners()->first();
        if ($creator) {
            $email = (new Email())
                ->from('no-reply@yourdomain.com')
                ->to($creator->getEmail())
                ->subject(sprintf('Task %s Notification', ucfirst($action)))
                ->text(sprintf(
                    'The task "%s" has been %s.',
                    $task->getTitle(),
                    $action
                ));

            $mailer->send($email);
        }
    }

    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function deleteComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_show', ['id' => $comment->getTask()->getId()], Response::HTTP_SEE_OTHER);
    }
    #[Route('/comment/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function editComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_show', ['id' => $comment->getTask()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }
}
