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
        $server = $m->getServer();
        $f = $m->getFixtures();
        $em = $f->getWantToPlayEmail($fixtureId);
        $email = $em['email'];
        $recipients = $em['recipients'];
        $subject = $email['subject'];
        $e = $m->getEmail();
        $twig = $m->getTwig();
        $sender = "tennisfixtures42@gmail.com";
        foreach ($recipients as $to) {
            $message = $twig->render('emailBody.html', ['email' => $email, 
            'to' => $to, 'server' => $server, 'fixtureid' => $fixtureId]);
            $e->sendEmail($sender, $to['EmailAddress'], $subject, $message);
        }
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
          ->withStatus(302);
    }
}
