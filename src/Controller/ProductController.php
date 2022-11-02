<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products', name: 'product_')]
Class ProductController extends AbstractController {

    /* #[Route('/', name: 'index')]
    public function index() : Response
    {
        return $this->render('product/index.html.twig', [
            'product' => 'product :)',
        ]);
    } */

    #[Route('/{slug}', name: 'show')]
    public function show(Product $product) : Response
    {
        dd($product);
        return $this->render('product/index.html.twig', [
            'product' => $product,
        ]);
    }
}