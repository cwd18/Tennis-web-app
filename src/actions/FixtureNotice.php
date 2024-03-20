<?php
# View a notice of the specified fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureNotice
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
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkUser($fixtureId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $f = $m->getFixture($fixtureId);
        $fixture = $f->getFixtureData();
        return $view->render($response, 'fixtureNotice.html', $fixture);   
    }
}
