<?php
# Return participants given fixtureid 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutParticipantWantsToPlay
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $userId = (int)$args['userid'];
        $value = (int)$args['value'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUserAccessFixture($fixtureId))) {
            $response->getBody()->write($error);        
            return $response;
        }
        $f = $m->getFixture($fixtureId);
        if ($value == 1) {
            $f->setWantsToPlay($userId);} // will time stamp is NULL
        else {
            $f->setWantsNotToPlay($userId);}
        return $response->withHeader('Content-Type', 'application/json');
    }
}
