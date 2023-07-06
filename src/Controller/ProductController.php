<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    #[Route("/api/products", name: "get_all_products")]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productsList = $productRepository->findAll();
        $jsonProductsList = $serializer->serialize($productsList, "json");

        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }


    #[Route("/api/products/{product}", name: "get_product")]
    public function getProductDetails(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, "json");
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
