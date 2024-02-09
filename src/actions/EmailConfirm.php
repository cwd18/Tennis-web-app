<?php
# View email invitation and confirm to send email

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
        $f = $this->container->get('Model')->getFixtures();
        $em = $f->getWantToPlayEmail($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'emailConfirm.html', ['email' => $em['email'], 'recipients' => $em['recipients'],
        'server' => $_SERVER['SERVER_NAME'], 'fixtureid' => $fixtureId,
        'continuelink' => "fixture?fixtureid=$fixtureId", 
        'cancellink' => "fixture?fixtureid=$fixtureId"]);
    }
}
