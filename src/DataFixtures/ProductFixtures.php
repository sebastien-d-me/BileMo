<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($productIndex = 0; $productIndex < 250; $productIndex++) {
            $brand = $faker->shuffle("bilemo");
            $model = $faker->numerify($brand."-####");
            $system = $faker->randomElement(["android", "iOS"]);
            $storage = $faker->randomElement([16, 32, 64, 128, 256, 512]);
            $price = $faker->randomFloat(2, 20, 1000);
            $stock = $faker->numberBetween(0, 4000);
            $productDate = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $faker->dateTimeThisYear()->format("Y-m-d H:i:s"));

            $product = new Product();
            $product->setBrand($brand);
            $product->setModel($model);
            $product->setSystem($system);
            $product->setStorage($storage);
            $product->setPrice($price);
            $product->setStock($stock);
            $product->setCreatedAt($productDate);
            $product->setUpdatedAt($productDate);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
