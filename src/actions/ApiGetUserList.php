<?php
# Return list lists given scope

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetUserList
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $m = $this->container->get('Model');
        $fixtureId = (int)$args['fixtureid'];
        if ($fixtureId === 0) {
            if (is_string($error = $m->checkAdmin())) {
                $response->getBody()->write($error);
                return $response;
            }
            $u = $m->getUsers();
            $userList = $u->getAllUsers();
        } else {
            if (is_string($error = $m->checkOwnerAccessFixture($fixtureId))) {
                $response->getBody()->write($error);
                return $response;
            }
            $f = $m->getFixture($fixtureId);
            $userList = $f->getFixtureUsers();
        }
        $response->getBody()->write(json_encode($userList));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
