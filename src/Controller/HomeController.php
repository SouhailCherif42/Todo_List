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

final class HomeController extends AbstractController
{

  #[Route('/', name: 'home', methods: ['GET'])]
  public function login(TaskRepository $taskRepository, Security $security): Response
  {
    //if ($this->getUser()) {
    //    return $this->redirectToRoute('app_task_index');
    //}

    return $this->render('base.html.twig');
  }
}
