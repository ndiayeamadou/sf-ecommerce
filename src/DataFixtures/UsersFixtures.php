<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UsersFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordEncoder)
    {
        
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setLastname('Jean')->setFirstname('Alain')
        ->setAddress('Kër Massaar')->setZipcode('25000')->setCity('Dakar')
        ->setEmail('a.j@mail.com')
        ->setPassword(
            $this->passwordEncoder->hashPassword($user, 'passer')
        )
        ->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        /** using faker to create ohter users */
        $faker = Factory::create();

        for($usr = 1; $usr <= 5; $usr++) {
            $user = new User();
            $user->setLastname($faker->lastName())->setFirstname($faker->firstName())
            ->setAddress($faker->streetAddress())->setZipcode(str_replace(' ', '', '25 800'))
            ->setCity($faker->city())->setEmail($faker->email())
            ->setPassword(
                $this->passwordEncoder->hashPassword($user, 'passer')
            )
            ->setRoles(['ROLE_USER']);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
