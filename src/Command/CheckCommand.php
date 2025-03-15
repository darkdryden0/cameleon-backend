<?php
/*
 *  php bin/console app:check
 */
namespace App\Command;

use App\Middleware\Context;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ReverseContainer;

class CheckCommand extends Command
{
    protected static $defaultName = 'app:check';

    public function __construct(
        ReverseContainer $reverseContainer,
        LoggerInterface $appLogger,
    )
    {
        parent::__construct();
        Context::setContainer($reverseContainer);
        Context::setLogger($appLogger);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('cron check ' . date('Y-m-d H:i:s'));

        return Command::SUCCESS;
    }
}