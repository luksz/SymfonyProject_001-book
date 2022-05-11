<?php

namespace App\Command;

use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(
    name: 'app:step:info',
    description: 'Get Git Branch Info',
)]
class StepInfoCommand extends Command
{

    private CacheInterface $cacheInterface;

    public function __construct(CacheInterface $cacheInterface)
    {
        $this->cacheInterface = $cacheInterface;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $step = $this->cacheInterface->get('step', function (CacheItem $cacheIteam) {
            $proccess = new Process(['git', 'branch', '-a']);
            $proccess->mustRun();
            $cacheIteam->expiresAfter(5);
            return $proccess->getOutput();
        });


        $io = new SymfonyStyle($input, $output);
        $output->writeln($step);
        $io->note($step);
        $io->success($step);
        return Command::SUCCESS;
    }
}
