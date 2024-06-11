<?php
# Set fixture participants playing, depending on mode

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutPlaying
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $mode = $args['mode'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUserAccessFixture($fixtureId))) {
            $response->getBody()->write($error);
            return $response->withStatus(401);
        }
        $f = $m->getFixture($fixtureId);
        if ($mode === 'auto') {
            $f->setAutoPlaying();
        } else if ($mode === 'reset') {
            $f->resetPlaying();
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}
