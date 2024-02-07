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
        $seriesId = $params['seriesid'];
        $model = $this->container->get('Model');
        $u = $model->getUsers();
        $users = $u->getAllUsers();
        $s = $model->getSeries();
        $series = $s->getBasicSeriesData($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'serieseditform.html', 
        ['seriesid' => $seriesId, 'owner' => $series['SeriesOwner'],
        'day' => $series['SeriesWeekday'], 'time' => substr($series['SeriesTime'],0,5),
        'courts' => $series['SeriesCourts'], 'users' => $users
        ]);   
    }
}
