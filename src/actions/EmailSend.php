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
            "%s/participant?fixtureid=%s&userid=%%s", 
            "https://direct-terminal-412715.nw.r.appspot.com", $fixtureId);
        foreach ($recipients as $to) {
            $message = sprintf("<p>Hi %s</p>\n", $to['FirstName']);
            foreach ($email['message'] as $line) { $message .= sprintf("<p>%s</p>\n", $line);}
            $message .= "<p>Please answer by following ";
            $linkBase = sprintf($linkBase, $to['Userid']);
            $message .= sprintf("<a href = \"%s1\">this link</a></p>\n", $linkBase);
            foreach ($email['salutation'] as $line) { $message .= sprintf("<p>%s</p>\n", $line);}
            $e->sendEmail($from, $to['EmailAddress'], $subject, $message);
        }
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
          ->withStatus(302);
    }
}
