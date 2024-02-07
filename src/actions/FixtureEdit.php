<?php
# Edit basic fixture data from form parameters and then display the fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
        $fixtureId = $params['fixtureid'];
        $owner = $params['owner'];
        $date = $params['date'];
        $time = $params['time'];
        $courts = $params['courts'];
        $f = $this->container->get('Model')->getFixtures();
        $f->updateBasicFixtureData($fixtureId, $owner, $date, $time, $courts);
        return $response
        ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
        ->withStatus(302);
    }
}
