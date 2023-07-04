<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($userIndex = 0; $userIndex < 100; $userIndex++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $currentDate = date("Y-m-d H:i:s");

            $user = new User();
            $user->setEmail($firstName.".".$lastName.$faker->safeEmailDomain());
            $user->setUsername($faker->userName());
            $user->setPassword($faker->password());
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setClientId();
            $user->setCreatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $currentDate));
            $user->setUpdatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $currentDate));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
