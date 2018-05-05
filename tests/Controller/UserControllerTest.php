<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client = null;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    
    public function setUp()
    {
        $this->client = static::createClient(
            [],
            ['HTTP_HOST' => 'localhost:8000']
        );
        
        $kernel = self::bootKernel();
        
        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
    
    public function testUserIndexResponse()
    {
        $this->client->request('GET', '/user/index');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserShowResponse()
    {
        $this->client->request('GET', '/user/1');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserEditResponse()
    {
        $this->client->request('GET', '/user/1/edit');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testNewUserCanBeAdded()
    {   
        $userData = [
            'firstName' => 'test',
            'lastName'  => 'user',
            'email'     => 'test.user@email.com',
            'role'      => 'QA',
        ];

        $this->client->followRedirects();

        $crawler = $this->client->request('POST', '/user/new', $userData);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("test.user@email.com")')->count()
        );
    }

    public function testUserCanBeEdited()
    {
        $userData = [
            'firstName' => 'test',
            'lastName'  => 'user',
            'email'     => 'delete.me@email.com',
            'role'      => 'QA',
        ];

        $this->client->followRedirects();

        $crawler = $this->client->request('POST', '/user/1/edit', $userData);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("delete.me@email.com")')->count()
        );
    }

    public function testUserCanBeDeleted()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('DELETE', '/user/1');

        $this->assertEquals(
            0,
            $crawler->filter('html:contains("delete.me@email.com")')->count()
        );
    }

    public function testUserLimitExceptionIsThrown()
    {    
        $this->cleanDb();

        $this->expectException('\UserLimitReachedException');

        for ($i = 0; $i <= 10; $i++) { 
            $userData = [
                'firstName' => 'test',
                'lastName'  => 'user',
                'email'     => 'test.user@email.com',
                'role'      => 'QA' . $i, //assign different departments so role limit not hit
            ];
    
            $this->client->followRedirects();
    
            $crawler = $this->client->request('POST', '/user/new', $userData);
        }
    }

    public function testRoleLimitExceptionIsThrown()
    {
        $this->cleanDb();
        
        $this->expectException('\RoleLimitReachedException');

        for ($i = 0; $i <= 5; $i++) { 
            $userData = [
                'firstName' => 'test',
                'lastName'  => 'user',
                'email'     => 'test.user@email.com',
                'role'      => 'QA',
            ];
    
            $this->client->followRedirects();
    
            $crawler = $this->client->request('POST', '/user/new', $userData);
        }
    }

    private function cleanDb()
    {
        $this->em->createQuery('delete FROM user');
        
        $this->em->flush();
    }
}
