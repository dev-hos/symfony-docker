<?php

namespace App\DataFixtures;

use App\Entity\Keywords;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class KeywordsFixtures extends Fixture
{
    public function __construct(private readonly SluggerInterface $slugger)
    {}

    public function load(ObjectManager $manager): void
    {
        $keywords = ['France', 'Politique', 'Monde', 'Economie', 'Association', 'Informatique'];

        foreach ($keywords as $keyWd) {
            $keyword = new Keywords();
            $keyword->setName($keyWd);
            $keyword->setSlug(strtolower($this->slugger->slug($keyword->getName())));
            $manager->persist($keyword);
        }

        $manager->flush();
    }
}
