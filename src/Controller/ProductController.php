<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

class ProductController extends AbstractController
{
    /**
     * Get all the products.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retrieve all the products.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"getProducts"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page of the products list.",
     *     @OA\Schema(type="int", default=1)
     * )
     * 
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Number of items per page.",
     *     @OA\Schema(type="int", default=5)
     * )
     * 
     * @OA\Tag(name="Products")
     */
    #[Route("/api/products", name: "get_all_products", methods: ["GET"])]
    public function getAllProducts(ProductRepository $productRepository, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $limit = $request->get("limit", 5);
        $page = $request->get("page", 1);

        $cache = new FilesystemAdapter();
        $productsList = $cache->get("products", function(ItemInterface $item) use ($limit, $page, $productRepository) {
            $item->expiresAfter(5);
            return $productRepository->findAllWithPagination($limit, $page);
        });

        if($productsList === null) {
            throw new NotFoundHttpException("No results found.");
        }
        
        $context = SerializationContext::create()->setGroups(["getProducts"]);
        $jsonProductsList = $serializer->serialize($productsList, "json", $context);

        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }


    /**
     * Get a product.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retrieve the product details.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"getProducts"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="productId",
     *     in="path",
     *     description="The ID of the product.",
     *     @OA\Schema(type="integer", default=1)
     * )
     * @OA\Tag(name="Products")
     */
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

        if($product === null) {
            throw new NotFoundHttpException("No results found.");
        }

        $context = SerializationContext::create()->setGroups(["getProducts"]);
        $jsonProduct = $serializer->serialize($product, "json", $context);
        
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
