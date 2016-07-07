<?php namespace Pazuzu156\IRC;

use Symfony\Component\Console\Output\ConsoleOutput;

class Bot
{
    private $_socket;

    private $_console;

    private $_data; // wiped each pass-through

    private $_rdata;

    public function __construct(\Pazuzu156\IRC\Networking\Socket $socket)
    {
        $this->_socket = $socket;
        $this->_console = new ConsoleOutput;
    }

    public function getData()
    {
        return $this->_data = $this->_socket->getData();
    }

    public function login()
    {
        // register nick to IRC server
        $this->send([
            'action' => 'NICK',
            'query' => env('nick')
        ]);

        // Identify identity
        $this->send([
            'action' => 'USER',
            'query' => env('ident') . ' 8 * :' . env('gecos')
        ]);
    }

    public function join()
    {
        if($this->_data[1] == '376'
            || $this->_data[1] == '422')
        {
            $this->send([
                'action' => 'JOIN',
                'query' => env('channel')
            ]);

            // be sure to set identity with password
            $this->send([
                'action' => 'PRIVMSG',
                'to' => 'NickServ',
                'message' => 'identify ' . env('pass')
            ]);
        }
    }

    public function ping()
    {
        if($this->_data[0] == 'PING')
        {
            $this->send([
                'action' => 'PONG',
                'query' => $this->_data[1]
            ]);
        }
    }

    public function privateMessage($message, $to=null)
    {
        if($to==null)
        {
            $this->send([
                'action' => 'PRIVMSG',
                'channel' => true,
                'message' => $message,
            ]);
        }
        else
        {
            // create data bag for holding default values
            $bag = array(
                'action' => 'PRIVMSG',
                'to' => $to,
                'message' => $message,
            );

            // if a sender is defined, add them to the data bag
            if(isset($targets['from']))
                $bag['from'] = $targets['from'];

            $this->send($bag); // send data bag to server in PRIVMSG form
        }
    }

    public function op($message)
    {
        $this->send([
            'action' => 'PRIVMSG',
            'to' => 'ChanServ',
            'message' => $message
        ]);

        $this->_socket->resetRdata(353, $this->_rdata);
    }

    public function getCaller()
    {
        return str_replace(':', '', explode('!', $this->_data[0])[0]);
    }

    public function getRecipient()
    {
        return (empty($this->_data[4])) ? null : $this->_data[4];
    }

    public function isOp()
    {
        $this->send([
            'action' => 'NAMES',
            'query' => env('channel')
        ]);

        $this->_rdata = $this->_socket->getRdata()[353][0];
        foreach($this->_rdata as $nick)
        {
            if($nick == '@'.$this->getCaller())
                return true;
        }

        return false;
    }

    public function logout()
    {
        $this->send([
            'action' => 'QUIT'
        ]);
        $this->_socket->close();
    }

    public function parse($text)
    {
        $exp = explode(' ', $text);
        for($i = 0; $i < count($exp); $i++)
        {
            if(strrpos($exp[$i], ':') == 0)
            {
                switch($exp[$i])
                {
                    case ':from':
                        $exp[$i] = $this->getCaller();
                        break;
                    case ':to':
                        $exp[$i] = $this->getRecipient();
                        break;
                    case ':channel':
                        $exp[$i] = env('channel');
                        break;
                }
            }
        }

        return implode(' ', $exp);
    }

    public function send($data=array())
    {
        if(!is_array($data))
        {
            $this->_console->writeln("<error>Query build data must be in array format!</error>");
            exit;
        }
        elseif(empty($data))
        {
            $this->_console->writeln("<error>You must supply builder data!</error>");
            exit;
        }
        else
        {
            $builder = "";

            foreach($data as $key => $value)
            {
                if($key == "channel" && $value == true)
                    $builder .= env('channel') . ' ';
                elseif($key == "message")
                    $builder .= ':' . $this->parse($value) . ' ';
                elseif($key == "query")
                    $builder .= $value . ' ';
                elseif($key == "to")
                    $builder .= $value . ' ';
            }

            $this->_socket->sendData($data['action'], trim($builder));
        }
    }
}