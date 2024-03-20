<?php
# Edit basic fixture data from form parameters and then display the fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureEdit
{
  private $container;

  public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

  public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $fixtureId = (int)$params['fixtureid'];
        $time = $params['time'];
        $courts = $params['courts'];
        $targetCourts = $params['targetcourts'];
        $m = $this->container->get('Model');
        $f = $m->getFixture($fixtureId);
        $seriesId = $f->getSeriesid();
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkOwner($seriesId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $f->updateBasicFixtureData($time, $courts, $targetCourts);
        return $response
        ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
        ->withStatus(302);
    }
}
