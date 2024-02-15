<?php
# Present participant invitation

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantInvite
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
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        $invite = $f->getInvitationData($fixtureId, $userId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'participantInvite.html', $invite);
    }
}
