<?php
# Present form for editing basic series data

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesEditForm
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
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $u = $m->getUsers();
        $users = $u->getAllUsers();
        $s = $m->getSeries();
        $series = $s->getBasicSeriesData($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'serieseditform.html', 
        ['seriesid' => $seriesId, 'owner' => $series['SeriesOwner'],
        'day' => $series['SeriesWeekday'], 'time' => substr($series['SeriesTime'],0,5),
        'courts' => $series['SeriesCourts'], 'targetCourts' => $series['TargetCourts'], 
        'autoEmail' => $series['AutoEmail'], 'users' => $users
        ]);   
    }
}
