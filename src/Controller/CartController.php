<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{

    private $entityManager;
    private $categories;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->categories = $this->entityManager->getRepository(Category::class)->findAll();

    }

    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        $user = $this->getUser();
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        if ($user) {
            $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user'=>$user]);
            
            $data = [];
            $totalPrices = 0;

        foreach ($cart->getProducts() as $product) {
            $data[] = [
                'id'=>$product->getId(),
                'name' => $product->getName(),
                'image' => $product->getImage(),
                'category' => $product->getCategory()->getName(),
                'price' => $product->getPrice(),
            ];
            $totalPrices += $product->getPrice();
            }

            
            return $this->render('cart/index.html.twig', [
                'products' => $data,
                'categories' =>$categories,
                'totalPrices' => $totalPrices

            ]);

        }
        return $this->redirectToRoute('app_home');

    }

    #[Route('/cart/{id}/add', name: 'add_cart')]
    public function addToCart(Product $product): Response
    {
        $user = $this->getUser();
        
        if ($user) {
            $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user'=>$user]);
            $cart->addProduct($product);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_home');

    }

    #[Route('/cart/{id}/remove', name: 'remove_cart')]
    public function removeFromCart(Product $product): Response
    {
        $user = $this->getUser();
        
        if ($user) {
            $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user'=>$user]);
            $cart->removeProduct($product);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_cart');

    }

    #[Route('/cart/clear', name: 'clear_cart')]
    public function clearCart(): Response
    {
        $user = $this->getUser();
        
        if ($user) {
            $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user'=>$user]);
            if ($cart) {
                foreach ($cart->getProducts() as $product) {
                    $cart->removeProduct($product); // Remove product from cart
                }
                
                $this->entityManager->flush(); // Save changes
            }
        }

        return $this->redirectToRoute('app_cart');

    }
}
