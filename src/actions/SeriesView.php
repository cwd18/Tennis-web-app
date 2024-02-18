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
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $s = $m->getSeries();
        $series = $s->getSeries($seriesId);
        if (strcmp($m->sessionRole(),'Admin') == 0) {
            $token=$m->getTokens()->getOrcreateToken($series['owner']['Userid'], 'Owner', $seriesId);
            $series['owner']['Link'] = sprintf("%s/start/%s",$m->getServer(), $token);
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'series.html', $series);
    }
}
