<?php namespace Pazuzu156\IRC\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pazuzu156\IRC\Client;

/**
 * Clears Views cache
 *
 * @package SimpleMVC\Console
 */
class Start extends Command
{
	/**
	 * Configure Symfony Command
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('start')
			 ->setDescription('Starts the IRC bot');
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
		require_once __DIR__.'/../Utils/helpers.php'; // require helper functions
		$bot = new Client;
		$bot->start();
	}
}