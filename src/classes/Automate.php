<?php

declare(strict_types=1);

namespace TennisApp;

class Automate
{
    public const EMAIL_INVITATION = 0;
    public const EMAIL_BOOKING = 1;
    public const EMAIL_UPDATE = 2;
    public const EMAIL_DROPOUT = 3;

    public function runAutomation(Model $m)
    {
        // Called to run automated tasks
        $eventLog = $m->getEventLog();

        $todayWeekday = date('N') - 1; // 0 for Monday, 6 for Sunday
        $eventLog->write("Day $todayWeekday automation starting");

        $seriesList = $m->getSeriesList()->getAllSeries();

        foreach ($seriesList as $series) {
            $seriesId = $series['Seriesid'];
            $s = $m->getSeries($seriesId);
            $eventLog->write(sprintf("Processing series %s (%s)", $seriesId, $series['description']));
            $s->ensure2FutureFixtures();
            if ($series['AutoEmail']) {
                $fixtureId = $s->getFixtureNumDaysAhead(8);
                if ($fixtureId != 0) {
                    $eventLog->write("Sending invitation emails for series $seriesId");
                    $this->sendEmails($m, $fixtureId, Automate::EMAIL_INVITATION);
                }
                $fixtureId = $s->getFixtureNumDaysAhead(7);
                if ($fixtureId != 0) {
                    $eventLog->write("Sending court booking emails for series $seriesId");
                    $this->sendEmails($m, $fixtureId, Automate::EMAIL_BOOKING);
                }
                $fixtureId = $s->getFixtureNumDaysAhead(2);
                if ($fixtureId != 0) {
                    $eventLog->write("Sending update emails for series $seriesId");
                    $this->sendEmails($m, $fixtureId, Automate::EMAIL_UPDATE);
                }
            }
        }
        $eventLog->write("Day $todayWeekday automation completed");
    }


    public function sendEmails(Model $m, int $fixtureId, int $emailType, $args = NULL)
    {
        $f = $m->getFixture($fixtureId);
        $server = $m->getServer();
        $base = $f->getBasicFixtureData();
        $tokens = $m->getTokens();
        $e = $m->getEmail();
        $twig = $m->getTwig();
        $replyTo = $base['OwnerEmail'];

        if ($emailType == Automate::EMAIL_DROPOUT) {
            $subject = sprintf(
                "%s %s dropped out",
                $args['FirstName'],
                $args['LastName']
            );
            $altmessage = sprintf(
                "%s %s has dropped out of tennis for %s",
                $args['FirstName'],
                $args['LastName'],
                $base['shortDate']
            );
            $message = "<!DOCTYPE html><html><body><p>$altmessage</p></body></html>";
            $e->sendEmail($replyTo, $base['OwnerEmail'], $subject, $message, $altmessage);
            return;
        }
        if ($emailType == Automate::EMAIL_INVITATION) {
            $recipients = $f->getWannaPlayRecipients();
            $subject = "Tennis " . $base['shortDate'];
            $twigFile = 'emailWannaPlay.html';
        } else if ($emailType == Automate::EMAIL_BOOKING) {
            $recipients = $f->getBookingRequestRecipients();
            $subject = "Book a court for " . $base['shortDate'];
            $twigFile = 'emailBookingBase.html';
            $base['requests'] = $f->getBookings('Request');
        } else if ($emailType == Automate::EMAIL_UPDATE) {
            $recipients = $f->getUpdateRecipients();
            $subject = "Tennis " . $base['shortDate'] . " update";
            $twigFile = 'emailUpdate.html';
        } else {
            throw new \Exception("Unknown email type");
        }
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrCreateToken($recipient['Userid'], 'User', $base['Seriesid']);
        }
        foreach ($recipients as $to) {
            $message = $twig->render($twigFile, [
                'altmessage' => false,
                'base' => $base, 'to' => $to, 'server' => $server
            ]);
            $altmessage = $twig->render($twigFile, [
                'altmessage' => true,
                'base' => $base, 'to' => $to, 'server' => $server
            ]);
            $altmessage = str_replace(['</tr><tr>', '</thead><thead>'], "\n", $altmessage);
            $altmessage = str_replace(['</td><td>', '</th><th>'], "\t", $altmessage);
            $altmessage = strip_tags($altmessage);
            $e->sendEmail($replyTo, $to['EmailAddress'], $subject, $message, $altmessage);
        }
    }
}
