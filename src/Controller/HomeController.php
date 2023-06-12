<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    private $entityManager;
    private $categories;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->categories = $this->entityManager->getRepository(Category::class)->findAll();

    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'description' => $product->getDescription(),
                'image' => $product->getImage(),
                'category' => $product->getCategory()->getName(),
            ];
        }
        return $this->render('home/index.html.twig', [
            'products' => $data,
            'categories' => $this->categories,
        ]);
    }

    #[Route('/category/{id}', name: 'app_home_category')]
    public function productsCategory(Category $category=null): Response
    {
        if (!$category) {
            return $this->render('404/notfound.html.twig',[
                'categories' => $this->categories,
            ]);
        }

        $products = $this->entityManager->getRepository(Product::class)->findBy(['category' => $category]);

        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'description' => $product->getDescription(),
                'image' => $product->getImage(),
                'category' => $product->getCategory()->getName(),
            ];
        }

        return $this->render('home/index.html.twig', [
            'products' => $data,
            'categories' => $this->categories,
        ]);
    }

    
    #[Route('/product/{id}/info', name: 'info_product')]
    public function show(Product $product): Response
    {
        return $this->render('home/show.html.twig', [
            'product' => $product,
            'categories' => $this->categories
        ]);
    }

    #[Route('/access-denied', name: 'app_access_denied')]
    public function accessDenied(): Response
    {
        return $this->render('404/notfound.html.twig',[
            'categories' => $this->categories,
        ]);
    }
}
