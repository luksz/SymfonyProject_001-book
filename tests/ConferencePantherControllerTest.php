<?php

namespace App\Tests;

use Symfony\Component\Panther\PantherTestCase;

class ConferencePantherControllerTest extends PantherTestCase
{

    // public function testIndex(): void
    // {
    //     $client = static::createPantherClient();
    //     $crawler = $client->request('GET', '/conference');

    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorTextContains('h2', 'Give your feedback!');
    // }

    
    // public function  testCommentSend()
    // {
    //         $client = static::createPantherClient();
    //         $crawler = $client->request('GET', '/conference/amsterdam-2019');
    //         $client->submitForm('Submit',[
    //             'comment_form[author]' => 'Angelika',
    //             'comment_form[text]' => 'Komentarz z testu atomatycznego',
    //             'comment_form[email]' => 'test@test.pl',
    //             'comment_form[photo]' => dirname(__DIR__,2).'/public/images/under-construction.gif'
    //         ]);
    //         $this->assertResponseRedirects();
    //         $client->followRedirect();
    //         $this->assertSelectorExists('div:contains("There are 2 comments.")');

            
    // }

    
    // public function testConferencePage()
    // {
    //             $client = static::createPantherClient();
    //             $crawler = $client->request('GET', '/conference');

    //             $this->assertCount(2, $crawler->filter('h4'));

    //             $client->click($crawler->filter('h4 + p a')->link());

    //             $this->assertResponseIsSuccessful();
    //             $this->assertPageTitleContains('Amsterdam');
    //             $this->assertSelectorTextContains('h2','Amsterdam');
    //             $this->assertSelectorExists('div:contains("There are 1 comments.")');


    // }

}
