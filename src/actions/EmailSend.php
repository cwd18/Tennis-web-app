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
        $seriesId = $f->getSeriesid($fixtureId);
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
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
            $message = $twig->render('emailBody.html', ['email' => $email, 
            'to' => $to, 'server' => $server, 'fixtureid' => $fixtureId]);
            $e->sendEmail($replyTo, $to['EmailAddress'], $subject, $message);
        }
        $f->setInvitationsSent($fixtureId);
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
          ->withStatus(302);
    }
}
