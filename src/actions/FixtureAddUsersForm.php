<?php
# Present form for users to be added to the specified fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureAddUsersForm
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
        $f = $model->getFixtures();
        $users = $f->getFixtureCandidates($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'usersselectform.html', 
        ['users' => $users, 
        'legend' => 'Select users to add to fixture',
        'formlink' => 'fixtureaddusers',
        'sfidvalue' => $fixtureId,
        'sfidname' => 'fixtureid',
        'cancellink' => "fixture?fixtureid=$fixtureId"]);
    }
}
