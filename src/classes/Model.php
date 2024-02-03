<?php
declare(strict_types=1);

namespace TennisApp;

class Model
{
    protected $db = null; 
    protected $users = null;
    protected $series = null;
    protected $fixtures = null; 

    public function __construct($dsn, $username, $password)
    {
        $this->db = new Database($dsn, $username, $password);
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

}