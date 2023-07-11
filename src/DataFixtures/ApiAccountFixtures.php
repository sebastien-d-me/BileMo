<?php

namespace App\DataFixtures;

use App\Entity\ApiAccount;
use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ApiAccountFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            CustomerFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $customers = $manager->getRepository(Customer::class)->findAll();

        foreach($customers as $customer) {
            $email = $customer->getName()."@business.com";
            $role = ["ROLE_USER"];
            $password = $customer->getName()."123";
            $createdAt = $customer->getCreatedAt();
            $updatedAt = $customer->getUpdatedAt();

            $apiAccount = new ApiAccount();
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
