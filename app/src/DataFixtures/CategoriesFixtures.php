<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
    public function __construct(private readonly SluggerInterface $slugger)
    {}

    public function load(ObjectManager $manager): void
    {
        $categories = [
            [
                'name' => 'France',
                'parent' => null
            ],
            [
                'name' => 'Monde',
                'parent' => null
            ],
            [
                'name' => 'Politique',
                'parent' => 'France'
            ],
            [
                'name' => 'Associations',
                'parent' => 'France'
            ],
            [
                'name' => 'Economie',
                'parent' => 'Monde'
            ]
        ];
        foreach ($categories as $cat) {
            $category = new Categories();
            $category->setName($cat['name']);
            $category->setSlug(strtolower($this->slugger->slug($category->getName())));
            $this->setReference($cat['name'], $category);
            $this->setReference('cat', $category);
            $parent = null;
            if ($cat['parent'] !== null) {
                $parent = $this->getReference($cat['parent']);
            }
            $category->setParent($parent);
            $manager->persist($category);
        }

  

        $manager->flush();
    }
}
