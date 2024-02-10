<?php
declare(strict_types=1);

namespace TennisApp;

class Model
{
    protected $db = null; 
    protected $email = null;
    protected $users = null;
    protected $series = null;
    protected $fixtures = null; 

    public function __construct($dsn, $username, $password, $email_config)
    {
        $this->db = new Database($dsn, $username, $password);
        $this->email = new Email($email_config);
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

    public function getEmail()
    {
        return $this->email;
    }

}