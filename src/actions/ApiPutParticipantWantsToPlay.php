<?php
# Set participant wants to play given fixtureid, userid and value

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
        $previousValue = $f->getWantsToPlay($userId); //null, 0, or 1
        if ($value === 1) {
            $f->setWantsToPlay($userId); // will time stamp if the current time stamp is NULL
            if ($previousValue === 0) { // user now wants to play
                $user = $m->getUsers()->getUserData($userId);
                $a = $m->getAutomate();
                $a->sendEmails($m, $fixtureId, $a::EMAIL_NOW_PLAYING, $user);
            }
        } elseif ($value === 0) {
            $f->setWantsNotToPlay($userId);
            if ($previousValue === 1) { // user is dropping out
                $user = $m->getUsers()->getUserData($userId);
                $a = $m->getAutomate();
                $a->sendEmails($m, $fixtureId, $a::EMAIL_DROPOUT, $user);
            }
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}
