<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]

class UserController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $users = $this->entityManager->getRepository(User::class)->findBy(['roles' => ['', '["ROLE_USER"]']]);

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}/delete', name: 'delete_user')]
    public function delete(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_user');
    }
}
