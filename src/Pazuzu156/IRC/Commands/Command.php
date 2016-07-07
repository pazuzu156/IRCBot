<?php namespace Pazuzu156\IRC\Commands;

class Command
{
    protected $_bot;

    protected $_commands;

    protected $_commandList;

    protected $_customCommands; // unused for now

    public function __construct($bot)
    {
        $this->_bot = $bot;
        $this->registerCommands();
    }

    private function registerCommands()
    {
        // load default commands
        $this->_commands = $this->load()->commands;
        for($i = 0; $i < count($this->_commands); $i++)
        {
            $this->_commandList[$i] = $this->_commands[$i]->command; // another method of registering. With matching keys
        }

        if(($cmd = env('commands')))
        {
            // load custom commands
            $this->_customCommands = $this->load(env('commands'))->commands;
            for($i = 0; $i < count($this->_customCommands); $i++)
            {
                $this->_commandList[$i] = $this->_customCommands[$i]->command; // another method of registering. With matching keys
            }
        }
    }

    private function load($cmd=null)
    {
        $path = ($cmd==null) ? getcwd().'/commands.json' : getcwd().'/'.$cmd;
        $data = file_get_contents($path);
        $json = json_decode($data);
        return $json;
    }

    public function listen($data)
    {
        // If a command is sent over network
        if(strrpos($data[3], '!') == 1)
        {
            for($i = 0; $i < count($this->_commands); $i++)
            {
                $cmd = str_replace(':', '', $data[3]);
                if($this->_commands[$i]->command == $cmd)
                {
                    $command = $this->_commands[$i];
                    switch($command->type)
                    {
                        case 'PRIVMSG': // private message
                            $this->_bot->privateMessage($command->message, $this->_bot->getRecipient());
                            break;
                        case 'QUIT': // Bot QUIT Message. Only admin can call this command!
                            if($this->_bot->getCaller() == $command->admin)
                                $this->_bot->logout();
                            else
                                $this->_bot->privateMessage("You are not allowed to issue this command!", $this->_bot->getCaller());
                            break;
                        case 'OP':
                            if($this->_bot->isOp($this->_bot->getCaller()) || ($this->_bot->getCaller() == $command->admin))
                                $this->_bot->op($command->message);
                            else
                                $this->_bot->privateMessage("You are not allowed to issue this command!", $this->_bot->getCaller());
//                            if(in_array($this->_bot->getCaller(), $command->ops))
//                                $this->_bot->op($command->message);
//                            else
//                                $this->_bot->privateMessage("You are not allowed to issue this command!", $this->_bot->getCaller());
                            //$this->_bot->op($command->message);
                            break;
                    }
                }
                else
                {
                    // command MIGHT be found, but not matched with called command. That needs addressed
                    if(!in_array($cmd, $this->_commandList))
                    {
                        $this->_bot->privateMessage("\"$cmd\" is an invalid command!");
                        break;
                    }
                }
            }
        }
    }
}