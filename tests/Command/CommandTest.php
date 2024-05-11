<?php

namespace App\Tests\Command;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
        ->get('doctrine')
        ->getManager();
    }
    public function testCommentCleanUpWithOption(): void
    {

        $comment = $this->entityManager
            ->getRepository(Comment::class)
            ->findOneBy(['state' => 'spam']);

        $comment->setCreatedAt(new \DateTimeImmutable('-10 days'));
        $this->entityManager->flush();

        $application = new Application(self::$kernel);

        $command = $application->find('app:comment:cleanup');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--dry-run' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Dry mode enabled', $output);
        $this->assertStringContainsString('Deleted "1" old rejected/spam comments.', $output);
    }

    public function testCleanUpCommand()
    {
        $comment = $this->entityManager
        ->getRepository(Comment::class)
        ->findOneBy(['state' => 'spam']);

        $comment->setCreatedAt(new \DateTimeImmutable('-10 days'));
        $this->entityManager->flush();


        $application = new Application(self::$kernel);

        $command = $application->find('app:comment:cleanup');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Deleted "1" old rejected/spam comments.', $output);
    }
}
