<?php
# Present are you sure before deleting series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesDeleteForm
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = (int)$params['seriesid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkAdmin())) {
            $response->getBody()->write($error);
            return $response;
        }
        $s = $m->getSeries();
        $series = $s->getBasicSeriesData($seriesId);
        $lines[] = "Are you sure you want to delete the below series?";
        $lines[] = $s->seriesDescription($series['SeriesWeekday'], $series['SeriesTime']);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opconfirm.html', ['op' => "Delete series", 
        'continuelink' => "seriesdelete?seriesid=$seriesId", 
        'cancellink' => "series?seriesid=$seriesId", 
        'lines' => $lines]);
    }
}
