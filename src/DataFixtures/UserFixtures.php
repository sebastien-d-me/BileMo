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
        $customers = $manager->getRepository(Customer::class)->findAll();

        for($userIndex = 0; $userIndex < 100; $userIndex++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $customer = $customers[array_rand($customers)];
            $email = $firstName.".".$lastName."@".$faker->safeEmailDomain();
            $username = $firstName.".".$lastName;
            $password = password_hash($faker->password(), PASSWORD_DEFAULT);
            $userDate = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $faker->dateTimeThisYear()->format("Y-m-d H:i:s"));

            $user = new User();
            $user->setCustomer($customer);
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPassword($password);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setCreatedAt($userDate);
            $user->setUpdatedAt($userDate);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
