<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RegisterController extends AbstractController
{
    private $passwordEncoder;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function index(Request $request): Response
    {
        // Check if the user is already authenticated
        if ($this->getUser()) {
            return new RedirectResponse($this->generateUrl('app_home'));
        }

        $user = new User();
        $cart = new Cart();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

             // Perform password confirmation validation manually
             $password = $form->get('password')->getData();
             $confirmPassword = $form->get('confirm_password')->getData();
 
             if ($password !== $confirmPassword) {
                 $form->get('confirm_password')->addError(new FormError('The password fields do not match.'));
             }
            // Encode the new user's password
            $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));

            $cart->setUser($user);
            
            $user->setCart($cart);
            // Set their role
            $user->setRoles(['ROLE_USER']);

            // Save the user
            $this->entityManager->persist($user);
            $this->entityManager->persist($cart);

            $this->entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
