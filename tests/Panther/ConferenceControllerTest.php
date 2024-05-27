<?php

namespace App\Tests\Panther;

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
        $client->request('GET', '/en/');

        $this->assertSelectorTextContains('h2', 'Give your feedback');
        $this->assertSelectorTextContains('a', 'Guestbook');
    }
}
