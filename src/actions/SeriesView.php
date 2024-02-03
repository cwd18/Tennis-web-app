<?php
# View specified series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesView
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $model = $this->container->get('Model');
        $s = $model->getSeries();
        $series = $s->getSeries($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'series.html', $series);
    }
}
