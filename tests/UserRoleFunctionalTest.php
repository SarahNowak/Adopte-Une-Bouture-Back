<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRoleFunctionalTest extends WebTestCase
{
    public function testAdminAnonymous()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/user');

        $this->assertResponseRedirects();
        // On vérifie que l'en-tête Location pointe vers l'url /login
        $this->assertResponseHeaderSame('Location', '/login');
    }

    public function testAdminRoleAdmin()
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'sarah@bouture.com']);
        
        // On simule la connexion de l'utilisateur
        $client->loginUser($user);

        // On va vérifier que l'accès à /admin/user/add est possible pour un ROLE_ADMIN
        $crawler = $client->request('GET', '/admin/user/add');

        $this->assertResponseIsSuccessful();
    }

    public function testAddAdsAnonymous()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/ads');
        $this->assertResponseStatusCodeSame(401);
    }

}
