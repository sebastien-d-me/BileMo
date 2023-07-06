<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route("/api/users/{customerId}", name: "get_all_users_by_customer")]
    public function getAllUsersByCustomer(int $customerId, SerializerInterface $serializer, UserRepository $userRepository): JsonResponse
    {
        $usersList = $userRepository->findBy([
            "customer" => $customerId
        ]);
        $jsonUsersList = $serializer->serialize($usersList, "json");

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }


    #[Route("/api/users/{customerId}/{userId}", name: "get_user_details_by_customer")]
    public function getUserDetailsByCustomer(int $customerId, SerializerInterface $serializer, int $userId, UserRepository $userRepository): JsonResponse
    {
        $usersList = $userRepository->findOneBy([
            "customer" => $customerId, 
            "id" => $userId
        ]);
        $jsonUsersList = $serializer->serialize($usersList, "json");

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }
}
