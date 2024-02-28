<?php
# Present form to add booking or requested booking

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantBook
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
        $userId = $params['userid'];
        $type = $params['type'];
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        $bookingFormData = $f->getBookingFormData($fixtureId, $userId, $type);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'participantBook.html', $bookingFormData);
    }
}
