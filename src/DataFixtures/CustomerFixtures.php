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

        for($customerIndex = 0; $customerIndex < 20; $customerIndex++) {
            $currentDate = date("Y-m-d H:i:s");
            
            $customer = new Customer();
            $customer->setNom($faker->domainWord());
            $user->setCreatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $currentDate));
            $user->setUpdatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $currentDate));

            $manager->persist($customer);
        }

        $manager->flush();
    }
}
