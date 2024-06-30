<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new Users())
            ->setNickname('user')
            ->setEmail('user@demo.fr')
            ->setVerified(true);
        $user->setPassword($this->hasher->hashPassword($user, 'azerty'));
        $this->setReference('user', $user);
        $manager->persist($user);

        $user = (new Users())
            ->setNickname('admin')
            ->setEmail('admin@admin.fr')
            ->setVerified(true)
            ->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->hasher->hashPassword($user, 'azerty'));
        $this->setReference('Admin', $user);
        $manager->persist($user);

        $manager->flush();
    }
}
