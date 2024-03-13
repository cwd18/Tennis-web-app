<?php
# Present participant page

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantPage
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
        $userId = (int)$params['userid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUser($fixtureId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $view = Twig::fromRequest($request);
        $f = $m->getFixture($fixtureId);
        if ($f->getWantsToPlay($userId) == NULL) {
            return $view->render($response, 'participantInvite.html', 
            $f->getInvitationData($userId));
        }
        if ($f->getCourtsBooked($userId) == NULL and $f->inBookingWindow($fixtureId) == 0) {
            return $view->render($response, 'participantBook.html', 
            $f->getBookingFormData($userId, 'Booked'));
        }
        $fixture = $f->getFixture($fixtureId);
        return $view->render($response, 'fixtureNotice.html', $fixture);   
    }
}
