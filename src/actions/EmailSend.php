<?php
# Send invitation emails

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class EmailSend
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = (int)$params['fixtureid'];
        $m = $this->container->get('Model');
        $f = $m->getFixtures();
        $seriesId = $f->getSeriesid($fixtureId);
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $a = $m->getAutomate();
        $invitationsSent = $f->getInvitationsSent($fixtureId);
        if ($invitationsSent == 0 ) {
            $a->sendInvitationEmails($m, $fixtureId);
        } else {
            $a->sendBookingEmails($m, $fixtureId);
        }
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
          ->withStatus(302);
    }
}
