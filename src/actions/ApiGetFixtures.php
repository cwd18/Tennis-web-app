<?php
# Return future fixtures given seriesid

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetFixtures
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response, array $args): Response
    {
        $seriesId = (int)$args['seriesid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUserAccessSeries($seriesId))) {
            $response->getBody()->write($error);        
            return $response;
        }
        $s = $m->getSeries($seriesId);
        $fixtureId = $s->nextFixture();
        $f = $m->getFixture($fixtureId);
        $fixtures[] = $f->getBasicFixtureData();
        $fixtureId = $s->latestFixture();
        $f = $m->getFixture($fixtureId);
        $fixtures[] = $f->getBasicFixtureData();
        $response->getBody()->write(json_encode($fixtures));        
        return $response->withHeader('Content-Type', 'HTML/json');
    }
}
