<?php
declare(strict_types=1);

namespace TennisApp;

class Automate
{
    public const EMAIL_INVITATION = 0;
    public const EMAIL_BOOKING = 1;

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
                    $this->sendEmails($m, $fixtureId, Automate::EMAIL_BOOKING);
                }
                if ($tomorrowWeekday == $weekday) {
                    $eventLog->write("Sending invitation emails for series $seriesId");
                    $this->sendEmails($m, $fixtureId, Automate::EMAIL_INVITATION);
                }
            }
        }
        $eventLog->write("Day $todayWeekday automation completed");
    }

    public function sendEmails(Model $m, int $fixtureId, int $emailType)
    {
        $f = $m->getFixture($fixtureId);
        $server = $m->getServer();
        $base = $f->getBasicFixtureData();
        $tokens = $m->getTokens();
        $e = $m->getEmail();
        $twig = $m->getTwig();
        $replyTo = $base['OwnerEmail'];

        if ($emailType == Automate::EMAIL_INVITATION) {
            $recipients = $f->getWannaPlayRecipients();
            $subject = "Tennis " . $base['shortDate'];
            $twigFile = 'emailWannaPlay.html';
        } else if ($emailType == Automate::EMAIL_BOOKING){
            $recipients = $f->getBookingRequestRecipients();
            $subject = "Book a court for " . $base['shortDate'];
            $twigFile = 'emailBookingBase.html';
            $base['requests'] = $f->getRequestedBookings();
        } else {
            throw new \Exception("Unknown email type");
        }
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrCreateToken($recipient['Userid'], 'User', $fixtureId);
        }
        foreach ($recipients as $to) {
            $message = $twig->render($twigFile, ['altmessage' => false,
            'base' => $base, 'to' => $to, 'server' => $server]);
            $altmessage = $twig->render($twigFile, ['altmessage' => true, 
            'base' => $base, 'to' => $to, 'server' => $server]);
            $altmessage = str_replace(['</tr><tr>', '</thead><thead>'], "\n", $altmessage);
            $altmessage = str_replace(['</td><td>', '</th><th>'], "\t", $altmessage);
            $altmessage = strip_tags($altmessage);
            $e->sendEmail($replyTo, $to['EmailAddress'], $subject, $message, $altmessage);
        }
        $f->setInvitationsSent();
    }

}