<?php
# Present form to add a booking request to the specified fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureAddRequests
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
        $f = $m->getFixture($fixtureId);
        $seriesId = $f->getSeriesid();
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $users = $f->getFixtureNonBookers('Request');
        $requestedBookings = $f->getRequestedBookings($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixtureAddRequests.html', 
        ['users' => $users, 
        'requestedBookings' => $requestedBookings,
        'fixtureid' => $fixtureId]);
    }
}
