<?php
# Set bookers to want to play

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureSetAutoPlaying
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
        $f = $m->getFixture($fixtureId);
        $seriesId = $f->getSeriesid();
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkOwnerAccess($seriesId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $f->setAutoPlaying();
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
          ->withStatus(302);
    }
}
