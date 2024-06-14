<?php
# Set courts or target courts for this fixture or for all future fixtures in this series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutCourts
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $type = $args['type'];
        $scope = $args['scope'];
        $courts = $args['courts'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkOwnerAccessFixture($fixtureId))) {
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401);
        }
        $f = $m->getFixture($fixtureId);
        if ($scope === 'fixture') {
            $f->updateCourts($type, $courts); // update fixture courts
        } else if ($scope === 'all') {
            $s = $m->getSeries($f->getSeriesId());
            $s->updateCourts($type, $courts); // Update series courts
            $f = $m->getFixture($s->nextFixture());
            $f->updateCourts($type, $courts); // Update next fixture courts
            $f = $m->getFixture($s->latestFixture());
            $f->updateCourts($type, $courts); // Update latest fixture courts
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}
