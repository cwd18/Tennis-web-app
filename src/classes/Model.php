<?php
declare(strict_types=1);

namespace TennisApp;

class Model
{
    protected $db = null; 
    protected $email = null;
    protected $server = null;
    protected $twig = null;
    protected $users = null;
    protected $series = null;
    protected $fixtures = null; 
    protected $tokens = null; 

    public function __construct($db_config, $email_config, $server, $twig)
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;port=%s;charset=%s',
        $db_config['host'], $db_config['name'], $db_config['port'], $db_config['charset']);
        $username = $db_config['username'];
        $password = $db_config['password'];
        $this->db = new Database($dsn, $username, $password);
        $this->email = new Email($email_config);
        $this->server = $server;
        $this->twig = $twig;
    }

    public function getUsers()
    {
        if ($this->users === null) {
            $this->users = new Users($this->db);
        }
        return $this->users;
    }

    public function getSeries()
    {
        if ($this->series === null) {
            $this->series = new Series($this->db);
        }
        return $this->series;
    }

    public function getFixtures()
    {
        if ($this->fixtures === null) {
            $this->fixtures = new Fixtures($this->db);
        }
        return $this->fixtures;
    }

    public function getTokens()
    {
        if ($this->tokens === null) {
            $this->tokens = new Tokens($this->db);
        }
        return $this->tokens;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getTwig()
    {
        return $this->twig;
    }
}