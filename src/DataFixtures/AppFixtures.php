<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\Config;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = Faker\Factory::create('fr_FR');
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
        for ($i = 0; $i < 1; ++$i) {
            $user = (new User())
                ->setEmail('martin3129@gmail.com')
                ->setFirstname('Martin')
                ->setLastname('GILBERT')
                ->setEnabled(true)
                ->setRoles(['ROLE_SUPER_ADMIN']);
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    '112358'
                )
            );
            $manager->persist($user);
        }

        $categories = [];
        for ($i = 0; $i < 4; ++$i) {
            $category = new ArticleCategory();
            $category
                ->setDisplayedHome(true)
                ->setDisplayedMenu(true)
                ->setName($this->faker->text(25));
            $categories[] = $category;
            $manager->persist($category);
        }
        for ($i = 0; $i < 30; ++$i) {
            $description = $this->faker->realText(300);
            $content = '';
            for ($j = 0; $j < 5; ++$j) {
                $content .= '<p>';
                $content .= $this->faker->realText(1024);
                $content .= '</p>';
            }
            $createdAt = $this->faker->dateTimeThisYear();
            $article = (new Article())
                ->setAuthor($user)
                ->setTitle($this->faker->text(48))
                ->setDescription($description)
                ->setContent($content)
                ->setCreatedAt($createdAt)
                ->setUpdatedAt($createdAt)
                ->addCategory($categories[rand(0, 3)]);
            $manager->persist($article);
        }

        $manager->flush();
    }
}