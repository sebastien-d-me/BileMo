<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $customerDate = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $faker->dateTimeThisYear()->format("Y-m-d H:i:s"));

        $customer = new Customer();
        $customer->setName("apitest");
        $customer->setCreatedAt($customerDate);
        $customer->setUpdatedAt($customerDate);
        
        $manager->persist($customer);

        for($customerIndex = 0; $customerIndex < 6; $customerIndex++) {
            $name = $faker->domainWord();
            
            $customer = new Customer();
            $customer->setName($name);
            $customer->setCreatedAt($customerDate);
            $customer->setUpdatedAt($customerDate);

            $manager->persist($customer);
        }        

        $manager->flush();
    }
}
