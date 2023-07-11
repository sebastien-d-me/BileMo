<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route("/api/users/{customerId}", name: "get_all_users_by_customer", methods: ["GET"])]
    public function getAllUsersByCustomer(int $customerId, SerializerInterface $serializer, UserRepository $userRepository): JsonResponse
    {
        $usersList = $userRepository->findBy([
            "customer" => $customerId
        ]);

        $jsonUsersList = $serializer->serialize($usersList, "json");

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }


    #[Route("/api/users/{customerId}/{userId}", name: "get_user_details_by_customer", methods: ["GET"])]
    public function getUserDetailsByCustomer(int $customerId, SerializerInterface $serializer, int $userId, UserRepository $userRepository): JsonResponse
    {
        $usersList = $userRepository->findOneBy([
            "customer" => $customerId, 
            "id" => $userId
        ]);

        $jsonUsersList = $serializer->serialize($usersList, "json");

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }


    #[Route("/api/users", name: "add_user_by_customer", methods: ["POST"])]
    public function addUserByCustomer(CustomerRepository $customerRepository, EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $content = $request->toArray();
        $customerId = $content["customerId"] ?? -1;

        $user = $serializer->deserialize($request->getContent(), User::class, "json");

        $errors = $validator->validate($user);
        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, "json"), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $user->setCustomer($customerRepository->find($customerId));

        $entityManager->persist($user);
        $entityManager->flush();
        
        $jsonUser = $serializer->serialize($user, "json");

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }


    #[Route("/api/users/{userId}", name: "delete_user_by_customer", methods: ["DELETE"])]
    public function deleteUserByCustomer(EntityManagerInterface $entityManager, int $userId, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->findOneBy([
            "id" => $userId
        ]);

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
