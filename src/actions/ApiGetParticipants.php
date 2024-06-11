<?php
# Return participants given fixtureid 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetParticipants
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $filter = $args['filter'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUserAccessFixture($fixtureId))) {
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401);
        }
        $f = $m->getFixture($fixtureId);
        if ($filter === 'bookers')
            $users = $f->getBookers();
        else
            $users = $f->getFixtureUsers();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
