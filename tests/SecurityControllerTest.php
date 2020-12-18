<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class SecurityControllerTest extends WebTestCase
{
    private function logout($client)
    {
        // Logout and redirect to homepage
        $client->request('GET', '/logout');
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertSame("/", $client->getRequest()->getPathInfo());
    }

    public function testLogin()
    {
        echo "testLogin\n";
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Veuillez vous connecter');

        // Fill form with wrong email and submit
        $buttonCrawlerNode = $crawler->selectButton('button_submit');
        $form = $buttonCrawlerNode->form();
        
        $client->submit($form, [
            "email" => 'mmmartin3129@gmail.com',
            "password" => 'password',
            "_remember_me" => false,
        ]);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', "Ce email n'a pas pu être trouvé.");
        
        // Fill form with wrong password and submit
        $client->submit($form, [
            "email" => 'martin3129@gmail.com',
            "password" => 'pppassword',
            "_remember_me" => false,
        ]);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', "Identifiants invalides.");
        
        // Fill form and submit
        $client->submit($form, [
            "email" => 'martin3129@gmail.com',
            "password" => 'password',
            "_remember_me" => false,
        ]);
        $this->assertTrue($client->getResponse()->isRedirect());

        $client->followRedirect();
        $this->logout($client);
    }

    public function testForgetPassword()
    {
        echo "testForgetPassword\n";
        $client = static::createClient();
        $crawler = $client->request('GET', '/forget_password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mot de passe oublié');

        $buttonCrawlerNode = $crawler->selectButton('button_submit');
        $form = $buttonCrawlerNode->form();
        $client->enableProfiler();
        $client->submit($form, [
            "forget_password_form[email]" => 'martin3129@gmail.com',
        ]);
        $this->assertTrue($client->getResponse()->isRedirect());
        
        // Checks that an email was sent
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertSame(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('Réinitialisation de votre mot de passe', $message->getSubject());
        $this->assertSame('martin3129@gmail.com', key($message->getTo()));
        
        // Collect confirmation account url
        $bodyMailCrawler = new Crawler($message->getBody());
        $link = $bodyMailCrawler->filter('a')->first();
        $this->assertSame('ici', $link->text());
        $resetPasswordUrl = $link->attr('href');
        
        $client->followRedirect();
        $this->assertSelectorTextContains('#flash_message', "Alerte succès : Un e-mail a été envoyé. Il contient un lien sur lequel il vous faudra cliquer pour réinitialiser votre mot de passe. Si vous ne recevez pas d'email, vérifiez votre dossier spam ou essayez à nouveau.");

        // Reset password and login with new
        $client->request('GET', $resetPasswordUrl);
        $crawler = $client->getCrawler();
        $this->assertSelectorTextContains('h1', "Réinitialiser le mot de passe");
        
        $buttonCrawlerNode = $crawler->selectButton('button_submit');
        $form = $buttonCrawlerNode->form();
        
        // Submit form with two passwords different
        $client->submit($form, [
            "reset_password_form[plainPassword][first]" => "new_password",
            "reset_password_form[plainPassword][second]" => "wrong_password",
        ]);
        
        $this->assertSelectorTextContains('label[for=reset_password_form_plainPassword_first]', "Nouveau mot de passe Erreur Les champs du nouveau mot de passe doivent correspondre");
        
        // Submit form with new_password
        $client->submit($form, [
            "reset_password_form[plainPassword][first]" => "new_password",
            "reset_password_form[plainPassword][second]" => "new_password",
        ]);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $crawler = $client->getCrawler();
        $this->assertContains("Alerte info : Le mot de passe a été modifié.", $crawler->filter('#flash_message')->children('div')->first()->text());
        $this->logout($client);
    }

    public function testResetPassword()
    {
        echo "testResetPassword\n";
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        
        // retrieve the test1 user
        $user = $userRepository->findOneByEmail('test1@empty.com');

        // simulate $user being logged in
        $client->loginUser($user);

        // Reset password
        $client->request('GET', '/reset_password/' . $user->getId());
        $crawler = $client->getCrawler();
        $this->assertSelectorTextContains('h1', "Réinitialiser le mot de passe");
        
        $buttonCrawlerNode = $crawler->selectButton('button_submit');
        $form = $buttonCrawlerNode->form();
        
        // Submit form with wrong password
        $client->submit($form, [
            "reset_password_form[password]" => "new_password",
            "reset_password_form[plainPassword][first]" => "password",
            "reset_password_form[plainPassword][second]" => "password",
        ]);
        $this->assertSelectorTextContains('label[for=reset_password_form_password]', "Ancien mot de passe Erreur Votre mot de passe actuel n'est pas valide");
        
        $client->submit($form, [
            "reset_password_form[password]" => "password1",
            "reset_password_form[plainPassword][first]" => "password",
            "reset_password_form[plainPassword][second]" => "password",
        ]);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $crawler = $client->getCrawler();
        $this->assertContains("Alerte info : Le mot de passe a été modifié.", $crawler->filter('#flash_message')->children('div')->first()->text());
        $this->logout($client);
    }
}
