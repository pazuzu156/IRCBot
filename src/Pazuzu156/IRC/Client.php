<?php namespace Pazuzu156\IRC;

use Pazuzu156\IRC\Networking\Socket;

class Client
{
	private $_socket;

	private $_builder;

	public function __construct()
	{
	    $this->_socket = new Socket;
		$this->_builder = new Bot($this->_socket);
		$this->_command = new Commands\Command($this->_builder);
	}

	public function start()
	{
		$this->_socket->registerSocket(); // Register new socket connection

		$this->_builder->login();
		while($this->_socket->isOpen())
		{
			$data = $this->_builder->getData(); // get data
			$this->_builder->ping(); // Ensure IRC ping is responded to to keep connection alive
			$this->_builder->join(); // Make sure we join the requested channel
			$this->_command->listen($data); // Listen for commands
		}
	}
}