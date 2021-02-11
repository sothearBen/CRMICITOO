<?php

namespace App\DataFixtures;

use App\Entity\Config;
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
        $config = new Config();
        $value = '{"name": "DevFusion", "admin_email": "martin.gilbert@dev-fusion.com", "developer_email": "martin3129@gmail.com"}';
        $value = json_decode($value, true);
        $config->setName('app')
            ->setValue([
                'type' => 'json',
                'value' => $value,
            ]);
        $manager->persist($config);
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