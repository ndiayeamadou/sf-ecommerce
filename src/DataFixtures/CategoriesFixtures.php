<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
    /** counter to count categories */
    private $counter = 1;

    public function __construct(private SluggerInterface $slugger) {}

    public function load(ObjectManager $manager): void
    {
        /* for($i = 1; $i <= 10; $i++) { */
            /* variables nommées */
            /* $parent = $this->createCategory(name:'Computer Science', parent: null, manager: $manager); */
            $parent = $this->createCategory('Computer Science', null, $manager);

            $this->createCategory('Keyboard', $parent, $manager);
            $this->createCategory('Computer', $parent, $manager);
            $this->createCategory('Mouse', $parent, $manager);
            
        /* } */

        $manager->flush();
    }

    public function createCategory(string $name, Category $parent = null, ObjectManager $manager) {
    //public function createCategory($name, $parent = null, ObjectManager $manager) {
        $category = new Category();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);

        /** use reference in datafixtures - mise en mémoire sous un nom d'un élt */
        $this->addReference('cat-'.$this->counter, $category);
        $this->counter++;

        return $category;
    }

}
