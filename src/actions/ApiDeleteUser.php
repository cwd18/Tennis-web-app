<?php
# If scope is 'global', delete a user record given userid
# If scope is 'fixture', delete a user from a fixture given userid
# If scope is 'all', delete a user from a fixture and all future fixtures in the series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiDeleteUser
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {

        $scope = $args['scope'];
        $fixtureId = (int)$args['fixtureid'];
        $userId = (int)$args['userid'];
        $m = $this->container->get('Model');
        if ($fixtureId !== 0) {
            $f = $m->getFixture($fixtureId);
            $userIds = array($userId);
        }
        if ($scope === 'global') {
            $u = $m->getUsers();
            $u->deleteUser($userId);
        } elseif ($scope === 'fixture') {
            $f->deleteFixtureUsers($userIds);
        } elseif ($scope === 'all') {
            $s = $m->getSeries($f->getSeriesId());
            $s->deleteSeriesUsers($userIds);
            $f1 = $m->getFixture($s->nextFixture());
            $f1->deleteFixtureUsers($userIds);
            $f2 = $m->getFixture($s->latestFixture());
            $f2->deleteFixtureUsers($userIds);
        } else {
            $response->getBody()->write(json_encode('Invalid scope'));
            return $response->withStatus(400);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}
