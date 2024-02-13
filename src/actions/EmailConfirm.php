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
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        $em = $f->getWantToPlayEmail($fixtureId);
        $email = $em['email'];
        $recipients = $em['recipients'];
        $view = Twig::fromRequest($request);
        return $view->render($response, 'emailConfirm.html', ['email' => $email, 
        'recipients' => $recipients,
        'server' => $model->getServer(), 'fixtureid' => $fixtureId,
        'continuelink' => "emailSend?fixtureid=$fixtureId", 
        'cancellink' => "fixture?fixtureid=$fixtureId"]);
    }
}
