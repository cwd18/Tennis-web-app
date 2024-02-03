<?php
# Present form to select multiple users to want to play

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureWantsToPlayForm
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
        $users = $f->getWantToPlayCandidates($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'usersselectform.html', 
        ['users' => $users, 
        'legend' => 'Select people who want to play',
        'formlink' => 'fixturewantstoplay',
        'sfidvalue' => $fixtureId,
        'sfidname' => 'fixtureid',
        'cancellink' => "fixture?fixtureid=$fixtureId"]);
    }
}
