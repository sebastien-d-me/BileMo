<?php

namespace App\Controller;

use App\Entity\ApiAccount;
use App\Entity\User;
use App\Repository\ApiAccountRepository;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;

class UserController extends AbstractController
{
    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }


    public function checkSameCustomer(ApiAccountRepository $apiAccountRepository, int $customerId, Request $request, SerializerInterface $serializer): bool
    {
        $jwtToken = explode(".", str_replace("bearer ", "", $request->headers->get("Authorization")));
        $decodedJwtToken = json_decode(base64_decode($jwtToken[1]), true);
        $apiAccountData = $apiAccountRepository->findOneBy([
            "email" => $decodedJwtToken["email"]
        ]);
        $apiAccountId = $apiAccountData->getCustomer()->getId();

        if($apiAccountId === $customerId) {
            return true;
        } else {
            return false;
        }
    }


    #[Route("/api/customers/{customerId}/users", name: "get_all_users_by_customer", methods: ["GET"])]
    public function getAllUsersByCustomer(ApiAccountRepository $apiAccountRepository, int $customerId, Request $request, SerializerInterface $serializer, UserRepository $userRepository): JsonResponse
    {
        $limit = $request->get("limit", 5);
        $page = $request->get("page", 1);

        $checkSameCustomer = $this->checkSameCustomer($apiAccountRepository, $customerId, $request, $serializer);
        if($checkSameCustomer === false) {
            return new JsonResponse($serializer->serialize("You can only manage users linked to your customer account.", "json"), Response::HTTP_FORBIDDEN, [], true);
        }

        $cache = new FilesystemAdapter();
        $usersList = $cache->get("users", function(ItemInterface $item) use ($customerId, $limit, $page, $userRepository) {
            $item->expiresAfter(5);
            return $userRepository->findAllWithPagination($customerId, $limit, $page);
        });
        
        $jsonUsersList = $serializer->serialize($usersList, "json");

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }


    #[Route("/api/customers/{customerId}/users/{userId}", name: "get_user_details_by_customer", methods: ["GET"])]
    public function getUserDetailsByCustomer(ApiAccountRepository $apiAccountRepository, int $customerId, Request $request, SerializerInterface $serializer, int $userId, UserRepository $userRepository): JsonResponse
    {
        $checkSameCustomer = $this->checkSameCustomer($apiAccountRepository, $customerId, $request, $serializer);
        if($checkSameCustomer === false) {
            return new JsonResponse($serializer->serialize("You can only manage users linked to your customer account.", "json"), Response::HTTP_FORBIDDEN, [], true);
        }

        $cache = new FilesystemAdapter();
        $user = $cache->get("user", function(ItemInterface $item) use ($customerId, $userId, $userRepository) {
            $item->expiresAfter(5);
            return $userRepository->findOneBy([
                "customer" => $customerId, 
                "id" => $userId
            ]);
        });

        $jsonUser = $serializer->serialize($user, "json");

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }


    #[Route("/api/customers/{customerId}/users/create", name: "add_user_by_customer", methods: ["POST"])]
    public function addUserByCustomer(ApiAccountRepository $apiAccountRepository, CustomerRepository $customerRepository, EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $content = $request->toArray();
        $customerId = $content["customerId"] ?? -1;

        $checkSameCustomer = $this->checkSameCustomer($apiAccountRepository, $customerId, $request, $serializer);
        if($checkSameCustomer === false) {
            return new JsonResponse($serializer->serialize("You can only manage users linked to your customer account.", "json"), Response::HTTP_FORBIDDEN, [], true);
        }

        $user = $serializer->deserialize($request->getContent(), User::class, "json");

        if($validator->validate($user)->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, "json"), Response::HTTP_BAD_REQUEST, [], true);
        }

        $user->setCustomer($customerRepository->find($customerId));
        $user->setPassword(password_hash($content["password"], PASSWORD_DEFAULT));

        $cache = new FilesystemAdapter();
        $cache->deleteItem("users");
        $cache->deleteItem("user");

        $entityManager->persist($user);
        $entityManager->flush();
        
        $jsonUser = $serializer->serialize($user, "json");

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }


    #[Route("/api/customers/{customerId}/users/{userId}/delete", name: "delete_user_by_customer", methods: ["DELETE"])]
    public function deleteUserByCustomer(ApiAccountRepository $apiAccountRepository, EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer, int $userId, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->findOneBy([
            "id" => $userId
        ]);
        $customerId = $user->getCustomer()->getId();

        $checkSameCustomer = $this->checkSameCustomer($apiAccountRepository, $customerId, $request, $serializer);
        if($checkSameCustomer === false) {
            return new JsonResponse($serializer->serialize("You can only manage users linked to your customer account.", "json"), Response::HTTP_FORBIDDEN, [], true);
        }

        $cache = new FilesystemAdapter();
        $cache->deleteItem("users");
        $cache->deleteItem("user");

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}