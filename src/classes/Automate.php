<?php
declare(strict_types=1);

namespace TennisApp;

class Automate
{
    public function runAutomation($model)
    {
        // Called to run automated tasks
        $m = $model;
        $pdo = $m->db;
        $eventLog = $m->getEventLog();
        $s = $m->getSeries();

        $eventLog->write('runAutomation called');

        $sql = "SELECT Seriesid, SeriesWeekday, AutoEmail FROM FixtureSeries;";
        $statement = $pdo->runSQL($sql);
        $rows = $statement->fetchall(\PDO::FETCH_ASSOC);
        $todayWeekday = date('N') - 1; // 0 for Monday, 6 for Sunday
        $tomorrowWeekday = ($todayWeekday + 1) % 7;
        foreach ($rows as $row) {
            $seriesId = $row['Seriesid'];
            $s->ensure2FutureFixtures($seriesId);
            if ($row['AutoEmail']) {
                $fixtureId = $s->latestFixture($seriesId);
                if ($todayWeekday == $row['SeriesWeekday']) {
                    $eventLog->write("Sending court booking emails for series $seriesId");
                    // add code here to send emails
                }
                if ($tomorrowWeekday == $row['SeriesWeekday']) {
                    $eventLog->write("Sending invitation emails for series $seriesId");
                    $this->sendInvitationEmails($model, $fixtureId);
                }
            }
        }
    }

    public function sendInvitationEmails($model, $fixtureId)
    {
        $m = $model;
        $f = $m->getFixtures();
        $server = $m->getServer();
        $em = $f->getPlayInvitations($fixtureId);
        $email = $em['email'];
        $recipients = $em['recipients'];
        $tokens = $m->getTokens();
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrcreateToken($recipient['Userid'], 'User', $fixtureId);
        }
        $subject = $email['subject'];
        $e = $m->getEmail();
        $twig = $m->getTwig();
        $replyTo = $email['owner']['EmailAddress'];
        foreach ($recipients as $to) {
            $message = $twig->render('emailBody.html', ['altmessage' => false, 'email' => $email, 
            'to' => $to, 'server' => $server, 'fixtureid' => $fixtureId]);
            $altmessage = strip_tags($twig->render('emailBody.html', ['altmessage' => true, 'email' => $email, 
            'to' => $to, 'server' => $server, 'fixtureid' => $fixtureId]));
            $e->sendEmail($replyTo, $to['EmailAddress'], $subject, $message, $altmessage);
        }
        $f->setInvitationsSent($fixtureId);

    }
}