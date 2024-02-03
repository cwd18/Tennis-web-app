<?php
# List all series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesList
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response): Response
    {
        $model = $this->container->get('Model');
        $s = $model->getSeries();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'serieslist.html', ['serieslist' => $s->getAllSeries()]);
    }
}
