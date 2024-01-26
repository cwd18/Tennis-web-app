<?php
# Present form for users to be added to the specified series

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesAddUsersForm
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $pdo = $GLOBALS['pdo'];
        $s = new \TennisApp\Series($pdo);
        $users = $s->getSeriesCandidates($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'usersselectform.html', 
        ['users' => $users, 
        'legend' => 'Select users to add to series',
        'formlink' => 'seriesaddusers',
        'sfidname' => 'seriesid',
        'sfidvalue' => $seriesId,
        'cancellink' => "series?seriesid=$seriesId"]);   
    }
}
