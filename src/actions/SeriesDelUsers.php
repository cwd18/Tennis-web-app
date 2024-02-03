<?php
# Add users from form parameters and then display the series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesDelUsers
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $seriesId = $params['seriesid'];
        foreach ($params as $pk => $p) {
            if (substr($pk, 0, 5) == "user_") {
                $userIds[] = $p;
            }
        }
        $model = $this->container->get('Model');
        $s = $model->getSeries();
        $users = $s->deleteSeriesUsers($seriesId, $userIds);
        foreach ($users as $user) {
            $lines[] = $user['FirstName'] . ' ' . $user['LastName'];
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => 'Users deleted from series',
        'link' => "series?seriesid=$seriesId", 'lines' => $lines]);
      }
}
