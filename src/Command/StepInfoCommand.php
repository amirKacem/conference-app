<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Cache\CacheInterface;

class StepInfoCommand extends Command
{
    protected static $defaultName = 'app:step:info';

    public function __construct(private CacheInterface $cache)
    {
        parent::__construct();

    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $step = $this->cache->get('app.current_step', function ($item) {
            $process = new Process(['git', 'tag', '-l','--points-at', 'HEAD']);
            $process->mustRun();
            $item->expiresAfter(30);
            return $process->getOutput();
        });

        $output->writeln($step);

        return Command::SUCCESS;
    }
}
