<?php
# Present form for users to be deleted from the specified series

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesDelUsersForm
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $pdo = $GLOBALS['pdo'];
        $series = new \TennisApp\Series($pdo);
        $users = $series->getSeriesUsers($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'usersselectform.html', 
        ['seriesid' => $seriesId, 'users' => $users, 
        'legend' => 'Select users to delete from series',
        'link' => 'seriesdelusers']);   
    }
}
