<?php 

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

final class HomeController extends AbstractController{

    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(TaskRepository $taskRepository, Security $security): Response
    {
        $user = $security->getUser();
        $tasks = $taskRepository->findBy(['owner' => $user]);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

}


