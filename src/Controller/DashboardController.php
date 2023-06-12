<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]

class DashboardController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        // Fetch the necessary data for the dashboard, such as the number of products and categories
        $productCount = $this->entityManager->getRepository(Product::class)->count([]);
        $categoryCount = $this->entityManager->getRepository(Category::class)->count([]);
        $userCount = $this->entityManager->getRepository(User::class)->count([]);

        // Render the dashboard view and pass the data to the template
        return $this->render('dashboard/index.html.twig', [
            'productCount' => $productCount,
            'categoryCount' => $categoryCount,
            'userCount' => $userCount,
        ]);
        
        
     
    }
}
