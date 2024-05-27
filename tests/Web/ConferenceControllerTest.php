<?php

namespace App\Tests\Web;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testConferencePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/');
        $this->assertCount(3, $crawler->filter('h4'));
        $link = $crawler->selectLink('__View')->eq(2)->link();
        $client->click($link);

        $this->assertPageTitleContains('London');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'London 2023');
        $this->assertSelectorExists('div:contains("No comments have been posted yet for this conference")');
    }

    public function testCommentSubmission()
    {
        $client = static::createClient();

        $crawler =  $client->request('GET', '/en/conference/london-2023');

        $client->submitForm('Submit', [
                'comment_form[author]' => 'Fabien',
                'comment_form[text]' => 'Some feedback from an automated functional test',
                'comment_form[email]' => $email = 'me@automat.ed'
        ]);

        $container = self::getContainer();
        $comment = $container->get(CommentRepository::class)->findOneByEmail($email);
        $comment->setState('published');
        $container->get(EntityManagerInterface::class)->flush();

        $this->assertResponseRedirects();

        $client->followRedirect();


        $this->assertSelectorExists('div:contains("There is one comment")');
    }
}
