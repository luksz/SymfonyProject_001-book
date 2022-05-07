<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/conference');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback!');
    }

    public function testConferencePage()
        {
                $client = static::createClient();
                $crawler = $client->request('GET', '/conference');

                $this->assertCount(2, $crawler->filter('h4'));

                $client->click($crawler->filter('h4 + p a')->link());

                $this->assertResponseIsSuccessful();
                $this->assertPageTitleContains('Amsterdam');
                $this->assertSelectorTextContains('h2','Amsterdam');
                $this->assertSelectorExists('div:contains("There are 1 comments.")');


        }

}
