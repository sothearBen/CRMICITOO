<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 2; ++$i) {
            $user = (new User())
                ->setEmail("test$i@empty.com")
                ->setFirstname("Bob$i")
                ->setLastname("Dupont$i")
                ->setEnabled(true);
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    "password$i"
                )
            );
            $manager->persist($user);
        }

        $manager->flush();
    }
}
