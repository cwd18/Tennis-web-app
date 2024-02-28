<?php
# Present form for users to be deleted from the specified series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesDelUsersForm
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
        $s = $m->getSeries();
        $users = $s->getSeriesUsers($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'usersselectform.html', 
        ['users' => $users, 
        'legend' => 'Select users to delete from series',
        'formlink' => 'seriesdelusers',
        'sfidname' => 'seriesid',
        'sfidvalue' => $seriesId,
        'cancellink' => "series?seriesid=$seriesId"]);   
    }
}
