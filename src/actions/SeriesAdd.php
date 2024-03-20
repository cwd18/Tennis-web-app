<?php
# Add series from form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesAdd
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $owner = $params['owner'];
        $day = $params['day'];
        $time = $params['time'];
        $courts = $params['courts'];
        $targetCourts = $params['targetcourts'];
        $m = $this->container->get('Model');
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkAdmin())) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $s = $m->getSeriesList();
        $s->addSeries($owner, $day, $time, $courts, $targetCourts);
        return $response
          ->withHeader('Location', "/serieslist")
          ->withStatus(302);
    }
}
