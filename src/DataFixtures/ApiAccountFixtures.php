<?php

namespace App\DataFixtures;

use App\Entity\ApiAccount;
use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApiAccountFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            CustomerFixtures::class,
        ];
    }

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $customers = $manager->getRepository(Customer::class)->findAll();

        foreach($customers as $customer) {
            $email = $customer->getName()."@business.com";
            $role = ["ROLE_USER", "ROLE_".strtoupper($customer->getName())];
            $createdAt = $customer->getCreatedAt();
            $updatedAt = $customer->getUpdatedAt();

            $apiAccount = new ApiAccount();

            if($customer->getName() === "apitest") {
                $password = $this->userPasswordHasher->hashPassword($apiAccount, $customer->getName()."123");
            } else {
                $password = $this->userPasswordHasher->hashPassword($apiAccount, $faker->password());
            }
            
            $apiAccount->setCustomer($customer);
            $apiAccount->setEmail($email);
            $apiAccount->setRoles($role);
            $apiAccount->setPassword($password);
            $apiAccount->setCreatedAt($createdAt);
            $apiAccount->setUpdatedAt($updatedAt);

            $manager->persist($apiAccount);
        }

        $manager->flush();
    }
}
