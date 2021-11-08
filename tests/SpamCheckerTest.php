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
//    public function testSomething(): void
//    {
//        $this->assertTrue(true);
//    }

      public function testSpamScopeWithInvalidRequest()
      {
          $comment = new Comment();
          $comment->setCreatedAtValue();
          $context = [];

          $client = new MockHttpClient([new MockResponse('invalid', ['response_headers' => ['x-akismet-debug-help: Invalid key']])]);
          $checker = new SpamChecker($client, 'abcde');

          $this->expectException(\RuntimeException::class);
          $this->expectExceptionMessage('Unable to check for spam: invalid (Invalid key).');
          $checker->getSpamScope($comment, $context);
      }

      public function testSpamScope (int $expectedScope, ResponseInterface $response, Comment $comment, array $context)
      {
           $client = new MockHttpClient([$response]);
           $checker= new SpamChecker($client, 'abcde');

           $scope = $checker->getSpamScope($comment, $context);
           $this->assertSame($expectedScope, $scope);
      }
      public function getComments ():iterable
      {
              $comment = new Comment();
              $comment->setCreatedAtValue();
              $context=[];

              $response= new MockResponse('', ['response_headers'=>['x-akismet-pro-tip: discard']]);
              yield 'blatant_spam'=>[2, $response, $comment, $context];

              $response = new MockResponse('true');
              yield 'spam'=>[1,$response, $comment, $context];

              $response = new MockResponse('false');
              yield 'ham'=>[0, $response, $comment, $context];
      }


}
