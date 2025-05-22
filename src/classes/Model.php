<?php

declare(strict_types=1);

namespace TennisApp;

class Model
{
    public Database $db;
    protected $email = null;
    protected $server = null;
    protected $twig = null;
    protected $users = null;
    protected SeriesList $seriesList;
    protected $tokens = null;
    protected $eventLog = null;
    protected $automate;


    public function __construct($db, $email_config, $server, $twig)
    {
        $this->db = $db;
        $this->seriesList = new SeriesList($this->db);
        $this->email = new Email($email_config);
        $this->server = $server;
        $this->twig = $twig;
        $this->automate = new Automate();
    }

    public function getUsers()
    {
        if ($this->users === null) {
            $this->users = new Users($this->db);
        }
        return $this->users;
    }

    public function getSeriesList()
    {
        return $this->seriesList;
    }

    public function getSeries(int $seriesId)
    {
        return new Series($this->db, $seriesId);
    }

    public function getFixture(int $fixtureId)
    {
        return new Fixture($this->db, $fixtureId);
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
        if ($this->eventLog === null) {
            $this->eventLog = new EventLog($this->db);
        }
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

    public function getAutomate()
    {
        return $this->automate;
    }

    public function sessionRole()
    {
        return $_SESSION['Role'] ?? 'Unknown';
    }

    public function sessionUser()
    {
        return $_SESSION['User'] ?? 0;
    }

    public function checkAdmin(): ?string
    {
        $role = $this->sessionRole();
        if (strcmp($role, 'Admin') == 0) {
            return NULL;
        }
        return "Not authorised: $role";
    }

    public function checkOwnerAccess(int $seriesId): ?string
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

    public function checkOwnerAccessFixture(int $fixtureId): ?string
    {
        $seriesId = $this->getFixture($fixtureId)->getSeriesid();
        return $this->checkOwnerAccess($seriesId);
    }

    public function checkUserAccessFixture(int $fixtureId): ?string
    {
        $seriesId = $this->getFixture($fixtureId)->getSeriesid();
        return $this->checkUserAccessSeries($seriesId);
    }

    public function checkUserAccessSeries(int $seriesId): ?string
    {
        $role = $this->sessionRole();
        if (strcmp($role, 'Admin') == 0) {
            return NULL;
        }
        if (strcmp($role, 'Owner') == 0 or strcmp($role, 'User') == 0) {
            if ($_SESSION['Otherid'] == $seriesId) {
                return NULL;
            }
            return "$role not authorised to access this series: $seriesId";
        }
        return "Not authorised: $role";
    }
}
