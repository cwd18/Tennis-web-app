<?php
# Return email list of participants given fixtureid

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetEmailList
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $m = $this->container->get('Model');
        $f = $m->getFixture($fixtureId);
        $seriesId = $f->getSeriesid();
        if (is_string($error = $m->checkOwnerAccess($seriesId))) {
            $response->getBody()->write($error);        
            return $response;
        }
        $emailList = $f->getEmailList();
        $response->getBody()->write($emailList);        
        return $response->withHeader('Content-Type', 'application/text');
    }
}
