<?php

namespace App\DataFixtures;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ImagesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for($img = 1; $img <= 3; $img++) {
            $image = new Image();
            $image->setName('/path/123456789image.png');
            $product = $this->getReference('prod-'.rand(1, 10));
            $image->setProduct($product);
            $manager->persist($image);
        }

        $manager->flush();
    }

    /** for DependentFixtureInterface */
    public function getDependencies()
    {
        /** tableau des fixtures devant ê exécuté avt image */
        return [
            ProductsFixtures::class
        ];
    }

}
