<?php
# View the specified fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureView
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        if (array_key_exists('fixtureid', $params)) {
            $fixtureId = $params['fixtureid'];
        } else {
            $fixtureId = $f->nextFixture($params['seriesid']);
        }
        $fixture = $f->getFixture($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixture.html', $fixture);   
    }
}
