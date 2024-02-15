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
        $fixtureId = $params['fixtureid'];
        $m = $this->container->get('Model');
        $f = $m->getFixtures();
        $em = $f->getWantToPlayEmail($fixtureId);
        $email = $em['email'];
        $recipients = $em['recipients'];
        $tokens = $m->getTokens();
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrcreateToken($recipient['Userid'], 'User', $fixtureId);
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'emailConfirm.html', ['email' => $email, 
        'recipients' => $recipients,
        'server' => $m->getServer(), 'fixtureid' => $fixtureId,
        'continuelink' => "emailSend?fixtureid=$fixtureId", 
        'cancellink' => "fixture?fixtureid=$fixtureId"]);
    }
}
