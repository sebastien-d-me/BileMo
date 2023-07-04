<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture implements DependentFixtureInterface
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
        $customersList = $manager->getRepository(Customer::class)->findAll();

        for($userIndex = 0; $userIndex < 100; $userIndex++) {
            $firstName = strtolower($faker->firstName());
            $lastName = strtolower($faker->lastName());
            $email = $firstName.".".$lastName."@".$faker->safeEmailDomain();
            $customer = $customersList[array_rand($customersList)];
            $currentDate = date("Y-m-d H:i:s");

            $user = new User();
            $user->setEmail($email);
            $user->setUsername($firstName.".".$lastName);
            $user->setPassword($faker->password());
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setCustomerId($customer);
            $user->setCreatedAt(\DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $currentDate));
            $user->setUpdatedAt(\DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $currentDate));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
