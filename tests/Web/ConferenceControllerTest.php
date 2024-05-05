<?php

namespace App\Tests\Web;

use Symfony\Component\Panther\PantherTestCase;

class ConferenceControllerTest extends PantherTestCase
{
    public function testIndex(): void
    {
        $client = static::createPantherClient(
            [
            'browser' => 'firefox',
            'external_base_uri' => $_SERVER['SYMFONY_PROJECT_DEFAULT_ROUTE_URL']
        ],
            [],
            [
          'capabilities' => [
              'acceptInsecureCerts' => true,
          ],
        ]
        );
        $client->request('GET', '/');

        $this->assertSelectorTextContains('h2', 'Give your feedback');
        $this->assertSelectorTextContains('a', 'Guestbook');
    }


    public function testConferencePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertCount(3, $crawler->filter('h4'));
        $link = $crawler->selectLink('View')->eq(2)->link();
        $client->click($link);

        $this->assertPageTitleContains('London');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'London 2023');
        $this->assertSelectorExists('div:contains("No comments have been posted yet for this conference")');
    }

    public function testCommentSubmission()
    {
        $client = static::createClient();
        $crawler =  $client->request('GET', '/conference/london-2023');

        $client->submitForm('Submit', [

                'comment_form[author]' => 'Fabien',
                'comment_form[text]' => 'Some feedback from an automated functional test',
                'comment_form[email]' => 'me@automat.ed'

        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorExists('div:contains("There are 1 comments.")');
    }
}
