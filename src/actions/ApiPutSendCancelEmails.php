<?php
# Manually send court cancellation emails for the given fixture,
# for any bookings still marked 'Cancel' (not already 'Cancelled')

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutSendCancelEmails
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
        if (is_string($error = $m->checkOwnerAccessFixture($fixtureId))) {
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401);
        }
        $a = $m->getAutomate();
        $a->sendEmails($m, $fixtureId, $a::EMAIL_CANCEL);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
