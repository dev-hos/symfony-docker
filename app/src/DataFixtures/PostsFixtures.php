<?php

namespace App\DataFixtures;

use App\Entity\Posts;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\CategoriesFixtures;
use App\Repository\CategoriesRepository;
use App\Repository\KeywordsRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PostsFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct (
        private readonly CategoriesRepository $catRepo,
        private readonly KeywordsRepository $keyRepo
    )
    {}

    public function load(ObjectManager $manager): void
    {
        $categories = $this->catRepo->findAll();
        $keywords = $this->keyRepo->findAll();
        $post = (new Posts())
        ->setTitle('Mon premier article')
        ->setSlug('mon-premier-article')
        ->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vel libero non neque commodo tempus.')
        ->setFeaturedImage('default.png')
        ->setUsers($this->getReference('user'))
        ->addCategory($categories[0])
        ->addKeyword($keywords[0])
        ;
        $manager->persist($post);

        ;

        $post = (new Posts())
        ->setTitle('Post de l\'admin')
        ->setSlug('premier-article-du-admin')
        ->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vel libero non neque commodo tempus.')
        ->setFeaturedImage('default.png')
        ->setUsers($this->getReference('Admin'));
        $manager->persist($post);

        ;
        

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UsersFixtures::class,
            CategoriesFixtures::class,
        ];
    }

}
