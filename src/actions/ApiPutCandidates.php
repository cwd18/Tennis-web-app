<?php
# Add candidates to a fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutCandidates
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args["fixtureid"];
        $userIds = $request->getParsedBody();
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkOwnerAccessFixture($fixtureId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $f = $m->getFixture($fixtureId);
        $f->addUsers($userIds);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
