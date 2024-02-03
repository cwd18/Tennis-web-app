<?php
# Set users to want to play from form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class FixtureWantsToPlay
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
        foreach ($params as $pk => $p) {
            if (substr($pk, 0, 5) == "user_") {
                $userIds[] = $p;
            }
        }
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        $f->setWantsToPlay($fixtureId, $userIds);
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
          ->withStatus(302);
      }
}
