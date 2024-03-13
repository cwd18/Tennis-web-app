<?php
declare(strict_types=1);

namespace TennisApp;

class Automate
{
    public function runAutomation(Model $m)
    {
        // Called to run automated tasks
        $eventLog = $m->getEventLog();

        $todayWeekday = date('N') - 1; // 0 for Monday, 6 for Sunday
        $tomorrowWeekday = ($todayWeekday + 1) % 7;

        $eventLog->write("Day $todayWeekday automation starting");

        $seriesList = $m->getSeriesList()->getAllSeries();

        foreach ($seriesList as $series) {
            $seriesId = $series['base']['Seriesid'];
            $s = $m->getSeries($seriesId);
            $eventLog->write(sprintf("Processing series %s (%s)", $seriesId, $series['base']['description']));
            $s->ensure2FutureFixtures();
            if ($series['base']['AutoEmail']) {
                $fixtureId = $s->latestFixture();
                $weekday = $series['base']['SeriesWeekday'];
                if ($todayWeekday == $weekday) {
                    $eventLog->write("Sending court booking emails for series $seriesId");
                    $this->sendBookingEmails($m, $fixtureId);
                }
                if ($tomorrowWeekday == $weekday) {
                    $eventLog->write("Sending invitation emails for series $seriesId");
                    $this->sendInvitationEmails($m, $fixtureId);
                }
            }
        }
        $eventLog->write("Day $todayWeekday automation completed");
    }

    public function sendInvitationEmails(Model $m, int $fixtureId)
    {
        $f = $m->getFixture($fixtureId);
        $server = $m->getServer();
        $em = $f->getPlayInvitations();
        $email = $em['email'];
        $recipients = $em['recipients'];
        $tokens = $m->getTokens();
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrCreateToken($recipient['Userid'], 'User', $fixtureId);
        }
        $subject = $email['subject'];
        $e = $m->getEmail();
        $twig = $m->getTwig();
        $replyTo = $email['owner']['EmailAddress'];
        foreach ($recipients as $to) {
            $message = $twig->render('emailWannaPlay.html', ['altmessage' => false, 'email' => $email, 
            'to' => $to, 'server' => $server, 'fixtureid' => $fixtureId]);
            $altmessage = strip_tags($twig->render('emailWannaPlay.html', ['altmessage' => true, 
            'email' => $email, 'to' => $to, 'server' => $server, 'fixtureid' => $fixtureId]));
            $e->sendEmail($replyTo, $to['EmailAddress'], $subject, $message, $altmessage);
        }
        $f->setInvitationsSent();
    }

    public function sendBookingEmails(Model $m, int $fixtureId)
    {
        $f = $m->getFixture($fixtureId);
        $server = $m->getServer();
        $em = $f->getBookingRequests();
        $email = $em['email'];
        $recipients = $em['recipients'];
        $tokens = $m->getTokens();
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrCreateToken($recipient['Userid'], 'User', $fixtureId);
        }
        $subject = "Book a court at 07:30 for " . $email['shortDate'];
        $e = $m->getEmail();
        $twig = $m->getTwig();
        $replyTo = $email['owner']['EmailAddress'];
        foreach ($recipients as $to) {
            $message = $twig->render('emailBookingBase.html', ['altmessage' => false, 'email' => $email, 
            'to' => $to, 'server' => $server, 'fixtureid' => $fixtureId]);
            $altmessage = strip_tags($twig->render('emailBookingBase.html', ['altmessage' => true, 
            'email' => $email, 'to' => $to, 'server' => $server, 'fixtureid' => $fixtureId]));
            $e->sendEmail($replyTo, $to['EmailAddress'], $subject, $message, $altmessage);
        }
        $f->setInvitationsSent();
    }
}