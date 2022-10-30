<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductsFixtures extends Fixture
{
    public function __construct(private SluggerInterface $slugger) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr-FR');

        for($prod = 1; $prod <= 10; $prod++) {
            $product = new Product();
            $product->setName($faker->text(15))->setPrice($faker->numberBetween(900, 150000))
            ->setDescription($faker->text())->setStock($faker->numberBetween(1, 33))
            ->setSlug($this->slugger->slug($product->getName())->lower());

            /** search a reference in category */
            $category = $this->getReference('cat-'.rand(1, 4));
            $product->setCategory($category);
            $this->setReference('prod-'.$prod, $product);
            
            $manager->persist($product);
        }

        $manager->flush();
    }
}
