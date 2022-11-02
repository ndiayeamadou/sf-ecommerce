<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories', name: 'category_')]
Class CategoryController extends AbstractController {

    #[Route('/{slug}', name: 'list_product')]
    public function showProduct(Category $category) : Response {
        //dd($category);
        //$products = $category->getProducts();
        //return $this->render('category/listproduct.html.twig', compact('category', 'products'));
        return $this->render('category/listproduct.html.twig', compact('category'));
        
        /* alternative syntaxe */
        /* 
        return $this->render('category/listproduct.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
         */
    }
}