<?php namespace Pazuzu156\IRC\Utils;

use Dotenv\Dotenv;

use Symfony\Component\Console\Output\ConsoleOutput;

class Env
{
    private $_env;

    private $_console;

    public function __construct()
    {
        $this->_console = new ConsoleOutput();
        $this->_env = $this->load();
    }

    private function load()
    {
        $d = new Dotenv(getcwd());
        return $d->load();
    }

    public function get($key)
    {
        return (isset($_ENV[$key])) ? $_ENV[$key] : false;
    }
}