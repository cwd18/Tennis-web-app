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
        $fixtureId = $params['fixtureid'];
        $userId = $params['userid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUser($fixtureId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $view = Twig::fromRequest($request);
        $f = $m->getFixtures();
        if ($f->getWantsToPlay($fixtureId, $userId) == NULL) {
            return $view->render($response, 'participantInvite.html', $f->getInvitationData($fixtureId, $userId));
        }
        if ($f->getCourtsBooked($fixtureId, $userId) == NULL and $f->inBookingWindow($fixtureId)) {
            return $view->render($response, 'participantBook.html', $f->getBookingFormData($fixtureId, $userId));
        }
        $fixture = $f->getFixture($fixtureId);
        return $view->render($response, 'fixtureNotice.html', $fixture);   
    }
}
