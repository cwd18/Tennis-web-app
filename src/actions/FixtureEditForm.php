<?php
# Present form for editing basic fixture data

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureEditForm
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
        $model = $this->container->get('Model');
        $u = $model->getUsers();
        $users = $u->getAllUsers();
        $f = $model->getFixtures();
        $fixture = $f->getBasicFixtureData($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixtureeditform.html', 
        ['fixtureid' => $fixtureId, 'owner' => $fixture['FixtureOwner'],
        'date' => $fixture['FixtureDate'], 'time' => substr($fixture['FixtureTime'],0,5),
        'users' => $users
        ]);   
    }
}
