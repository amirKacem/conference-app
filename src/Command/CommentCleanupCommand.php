<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CommentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommentCleanupCommand extends Command
{
    protected static $defaultName = 'app:comment:cleanup';

    public function __construct(private CommentRepository $commentRepository)
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setDescription('Deletes rejected and spam comments from the database')
             ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run');
    }

    public function execute(InputInterface $input, OutputInterface $output):int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');

            $count = $this->commentRepository->countOldRejected();
        } else {
            $count = $this->commentRepository->deleteOldRejected();
        }

        $io->success(sprintf('Deleted "%d" old rejected/spam comments.', $count));

        return Command::SUCCESS;
    }
}
