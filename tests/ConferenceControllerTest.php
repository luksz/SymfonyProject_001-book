<?php

namespace App\Tests;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/conference');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback!');
    }


    public function  testCommentSend()
    {
        $client = static::createClient();
        $client->enableProfiler();

        $crawler = $client->request('GET', '/en/conference/amsterdam-2019');

        $this->assertResponseIsSuccessful();


        $client->submitForm('Submit', [
            'comment_form[author]' => 'Angelika',
            'comment_form[text]' => 'Komentarz z testu atomatycznego',
            'comment_form[email]' => $email = 'test@test.pl',
            'comment_form[photo]' => dirname(__DIR__, 2) . '/public/images/under-construction.gif'
        ]);

        /**
         * @var Comment
         */
        $comment = self::getContainer()->get(CommentRepository::class)->findOneByEmail($email);
        $comment->setState(Comment::PUBLISHED);
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorExists('div:contains("There are 2 comments.")');
    }


    public function testConferencePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/conference');

        $this->assertCount(2, $crawler->filter('h4'));

        $client->click($crawler->filter('h4 + p a')->link());

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Amsterdam');
        $this->assertSelectorTextContains('h2', 'Amsterdam');
        $this->assertSelectorExists('div:contains("There is one comment.")');
    }

    // public function testMailerAssertions()
    // {
    //     $client = static::createClient();
    //     $client->request('GET', '/');

    //     $this->assertEmailCount(1);
    //     $event = $this->getMailerEvent(0);
    //     $this->assertEmailIsQueued($event);

    //     $email = $this->getMailerMessage(0);
    //     $this->assertEmailHeaderSame($email, 'To', 'fabien@example.com');
    //     $this->assertEmailTextBodyContains($email, 'Bar');
    //     $this->assertEmailAttachmentCount($email, 1);
    // }
}
