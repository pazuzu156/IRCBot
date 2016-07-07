<?php namespace Pazuzu156\IRC\Networking;

use Symfony\Component\Console\Output\ConsoleOutput;

class Socket
{
	private $_socket;

	private $_error;

	private $_buffer;

	private $_console;

	public function __construct($bufferSize=1024)
	{
		$this->_console = new ConsoleOutput;
		$this->_buffer = $bufferSize;
	}

	public function registerSocket()
	{
		$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$this->_error = socket_connect($this->_socket, env('server'), env('port'));

		// Error registering socket
		// Gotta do error handling to console
		if($this->_socket == false)
		{
			$errorCode = socket_last_error();
			$errorString = socket_strerror($errorCode);

			$this->_console->writeln("<error>Error registering socket!<br>Error Code: $errorCode<br>Message: $errorString</error>");
		}
	}

	public function sendData($action, $data)
	{
		$this->_console->writeln("<info>Sending: $action $data</info>");
		socket_write($this->_socket, $action . ' ' . $data . "\r\n");
	}

	public function getData()
	{
		$data = trim(socket_read($this->_socket, $this->_buffer, PHP_NORMAL_READ));
		$this->_console->writeln("$data");
		$r = explode(' ', $data);
		$r = array_pad($r, 10, '');
		return $r;
	}

	public function isOpen()
	{
		return is_resource($this->_socket);
	}

	public function getSocket()
	{
		return $this->_socket;
	}

	public function close()
	{
		socket_close($this->_socket);
	}
}