<?php
# Send invitation emails

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class EmailSend
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $m = $this->container->get('Model');
        $f = $m->getFixtures();
        $em = $f->getWantToPlayEmail($fixtureId);
        $email = $em['email'];
        $recipients = $em['recipients'];
        $subject = $email['subject'];
        $from = $email['from'];
        $e = $m->getEmail();
        $linkBase = sprintf(
            "%s/participantWantsToPlay?fixtureid=%s&userid=%%s&WantsToPlay=", 
            $_SERVER['SERVER_NAME'], $fixtureId);
        foreach ($recipients as $to) {
            $message = "";
            foreach ($email['message'] as $line) { $message .= sprintf("<p>%s</p>\n", $line);}
            $linkBase = sprintf($linkBase, $to['Userid']);
            $message .= sprintf("<p><a href = %s>Yes please</a></p>\n", $linkBase, 1);
            $message .= sprintf("<p><a href = %s>No thank you</a></p>\n", $linkBase, 0);
            foreach ($email['salutation'] as $line) { $message .= sprintf("<p>%s</p>\n", $line);}
            $e->sendEmail($from, $to['EmailAddress'], $subject, $message);
        }
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
          ->withStatus(302);
    }
}
