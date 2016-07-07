<?php namespace Pazuzu156\IRC\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Env extends Command
{
    /**
     * Configure Symfony Command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('env-copy')
            ->setDescription('Copy example .env file');
    }
    /**
     * Execute console command
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input,
                               OutputInterface $output)
    {
        $path = getcwd().'/';
        copy($path.'.env.example', $path.'.env');
    }
}