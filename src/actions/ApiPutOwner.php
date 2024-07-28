<?php
# Set the owner for this fixture or for all future fixtures in this series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutOwner
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $scope = $args['scope'];
        $ownerId = $args['ownerid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkOwnerAccessFixture($fixtureId))) {
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401);
        }
        $f = $m->getFixture($fixtureId);
        if ($scope === 'fixture') {
            $f->updateOwner($ownerId); // update fixture 
        } else if ($scope === 'all') {
            $s = $m->getSeries($f->getSeriesId());
            $s->updateOwner($ownerId); // Update series 
            $m->getFixture($s->nextFixture())->updateOwner($ownerId); // Update next fixture 
            $m->getFixture($s->latestFixture())->updateOwner($ownerId); // Update latest fixture 
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}
