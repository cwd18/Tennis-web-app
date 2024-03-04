<?php
# Email invitations

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class EmailConfirm
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = (int)$params['fixtureid'];
        $m = $this->container->get('Model');
        $f = $m->getFixtures();
        $seriesId = $f->getSeriesid($fixtureId);
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $invitationsSent = $f->getInvitationsSent($fixtureId);
        if ($invitationsSent == 0 ) {
            $em = $f->getPlayInvitations($fixtureId);
        } else {
            $em = $f->getBookingRequests($fixtureId);
        }
        $email = $em['email'];
        $recipients = $em['recipients'];
        $tokens = $m->getTokens();
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrCreateToken($recipient['Userid'], 'User', $fixtureId);
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'emailConfirm.html', ['invitationsSent' => $invitationsSent, 
        'email' => $email, 'recipients' => $recipients,
        'server' => $m->getServer(), 'fixtureid' => $fixtureId,
        'continuelink' => "emailSend?fixtureid=$fixtureId", 
        'cancellink' => "fixture?fixtureid=$fixtureId"]);
    }
}
