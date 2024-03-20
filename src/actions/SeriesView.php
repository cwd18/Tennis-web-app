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
        $seriesId = (int)$params['seriesid'];
        $m = $this->container->get('Model');
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkOwner($seriesId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $s = $m->getSeries($seriesId);
        // $s->ensure2FutureFixtures(); // useful for testing
        $series = $s->getSeriesData();
        if (strcmp($m->sessionRole(),'Admin') == 0) {
            $token=$m->getTokens()->getOrCreateToken($series['base']['SeriesOwner'], 'Owner', $seriesId);
            $series['base']['Link'] = sprintf("%s/start/%s",$m->getServer(), $token);
        }
        return $view->render($response, 'series.html', $series);
    }
}
