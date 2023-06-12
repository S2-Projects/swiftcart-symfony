<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin', name: 'admin_')]

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/product', name: 'app_product')]
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


        return $this->render('product/index.html.twig', [
            'products' => $data,
        ]);
    }

    #[Route('/product/{id}/show', name: 'show_product')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/create', name: 'create_product')]
    public function create(Request $request, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categoryId = $form->get('category')->getData();
            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
            if (!$category) {
                return $this->render('404/notfound.html.twig');
            }

            // Handle image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/img/products',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle the exception
                }

                $product->setImage($newFilename);
            } else {
                // Set default image when no image is provided
                $product->setImage('no-image.png');
            }

            $product->setCategory($category);
            $product->setUser($this->getUser());
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_app_product');
        }

        return $this->render('product/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}', name: 'edit_product')]
    public function edit(Request $request, Product $product, Filesystem $filesystem): Response
    {
        $originalImage = $product->getImage();
        $form = $this->createForm(ProductType::class, $product);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle the uploaded image
            $newImage = $form->get('image')->getData();
            if ($newImage instanceof UploadedFile) {
                // Delete the original image if it is different from 'no-image.png'
                if ($originalImage !== 'no-image.png') {
                    $imagePath = $this->getParameter('kernel.project_dir') . '/public/img/products/' . $originalImage;
                    
                    // Check if the original image file exists and delete it
                    if ($filesystem->exists($imagePath)) {
                        $filesystem->remove($imagePath);
                    }
                }
                
                // Generate a unique filename for the new image
                $newImageFileName = md5(uniqid()) . '.' . $newImage->guessExtension();
                
                // Move the new image to the desired location
                $newImage->move($this->getParameter('kernel.project_dir') . '/public/img/products/', $newImageFileName);
                
                // Update the product's image property
                $product->setImage($newImageFileName);
            }
    
            $this->entityManager->flush();
    
            return $this->redirectToRoute('admin_app_product');
        }
    
        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }


    #[Route('/product/{id}/delete', name: 'delete_product')]
    public function delete(Product $product, Filesystem $filesystem): Response
    {
        $imageFileName = $product->getImage();

        // Delete the image if it is different from 'no-image.png'
        if ($imageFileName && $imageFileName !== 'no-image.png') {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/img/products/' . $imageFileName;

            // Check if the image file exists and delete it
            if ($filesystem->exists($imagePath)) {
                $filesystem->remove($imagePath);
            }
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_app_product');
    }
}
