<?php

namespace App\Tests;

use App\Entity\Comment;
use App\SpamChecker;
 use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;


class SpamCheckerTest extends TestCase
{

    private function getComment(): Comment
    {
        $comment = new Comment();
       $comment->setCreatedAtValue();
       $comment->setAuthor("Łukasz");
       $comment->setEmail('email@email.pl');
       $comment->setText("test");
       
       return $comment;
    }

    public function testSpamScoreWithInvalidRequest(): void
    {
       $comment = $this->getComment();
       $context = [];
        $client = new MockHttpClient([new MockResponse('invalid', ['response_headers' => ['x-akismet-debug-help: Invalid key']])]);
       $checker = new SpamChecker($client, 'InvalidKey');

     
       $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to check for spam: invalid (Invalid key).');
        $checker->getSpamScore($comment, $context);
    }

     /**
     * @dataProvider getComments
     */
    public function testSpamScore(int $expectedScore, ResponseInterface $response, Comment $comment, array $context)
    {
        $client = new MockHttpClient([$response]);
        $checker = new SpamChecker($client, 'abcde');

        $score = $checker->getSpamScore($comment, $context);
        $this->assertSame($expectedScore, $score);
    }

    public function getComments(): iterable
    {
        $comment = $this->getComment();
        
        $context = [];

        $response = new MockResponse('', ['response_headers' => ['x-akismet-pro-tip: discard']]);
        yield 'blatant_spam' => [2, $response, $comment, $context];

        $response = new MockResponse('true');
        yield 'spam' => [1, $response, $comment, $context];

        $response = new MockResponse('false');
        yield 'ham' => [0, $response, $comment, $context];
   }

}
