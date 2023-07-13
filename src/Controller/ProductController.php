<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ProductController extends AbstractController
{
    #[Route("/api/products", name: "get_all_products", methods: ["GET"])]
    public function getAllProducts(ProductRepository $productRepository, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $limit = $request->get("limit", 999);
        $page = $request->get("page", 1);

        $cache = new FilesystemAdapter();
        $productsList = $cache->get("products", function(ItemInterface $item) use ($limit, $page, $productRepository) {
            $item->expiresAfter(5);
            return $productRepository->findAllWithPagination($limit, $page);
        });
        
        $jsonProductsList = $serializer->serialize($productsList, "json");

        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }


    #[Route("/api/products/{productId}", name: "get_product", methods: ["GET"])]
    public function getProductDetails(int $productId, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $cache = new FilesystemAdapter();
        $product = $cache->get("product", function(ItemInterface $item) use ($productId, $productRepository) {
            $item->expiresAfter(5);
            return $productRepository->findOneBy([
                "id" => $productId
            ]);
        });

        $jsonProduct = $serializer->serialize($product, "json");
        
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
