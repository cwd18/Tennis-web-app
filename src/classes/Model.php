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
    protected $eventLog = null; 


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
        $this->eventLog = new EventLog($this->db);
        $sessionHandler = new SessionHandler($this->db);
        session_set_save_handler($sessionHandler, true);
        session_start();
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

    public function getEventLog()
    {
        return $this->eventLog;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getTwig()
    {
        return $this->twig;
    }

    public function sessionRole()
    {
        return $_SESSION['Role'] ?? 'Unknown';
    }

    public function sessionUser()
    {
        return $_SESSION['User'] ?? 0;
    }
    
    public function checkAdmin() : ?string
    {
        $role = $this->sessionRole();
        if (strcmp($role, 'Admin') == 0) {
            return NULL;
        }
        return "Not authorised: $role";
    }

    public function checkOwner($seriesId) : ?string
    {
        $role = $this->sessionRole();
        if (strcmp($role, 'Admin') == 0) {
            return NULL;
        }
        if (strcmp($role, 'Owner') == 0) {
            if ($_SESSION['Otherid'] == $seriesId) {
                return NULL;
            }
            return "Owner not authorised for this series: $seriesId";
        }
        return "Not authorised: $role";
    }

    public function checkUser($fixtureId) : ?string
    {
        $role = $this->sessionRole();
        if (strcmp($role, 'Admin') == 0) {
            return NULL;
        }
        if (strcmp($role, 'Owner') == 0) {
            $seriesId = $this->getFixtures()->getSeriesid($fixtureId);
            if ($_SESSION['Otherid'] == $seriesId) {
                return NULL;
            }
            return "Owner not authorised for this series: $seriesId";
        }
        if (strcmp($role, 'User') == 0) {
            if ($_SESSION['Otherid'] == $fixtureId) {
                return NULL;
            }
            return "User not authorised for this series: $fixtureId";
        }
        return "Not authorised: $role";
    }

}