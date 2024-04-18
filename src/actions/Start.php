<?php
# External token-based entry point

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class Start
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        $m = $this->container->get('Model');
        $t = $m->getTokens();
        $row = $t->checkToken($token);
        $view = Twig::fromRequest($request);
        if ($row == FALSE) {
            return $view->render($response, 'error.html', ['error' => "Token not found: $token"]);}

        $userId = $row['Userid'];

        $_SESSION['User'] = $userId;
        $_SESSION['Role'] = $row['TokenClass'];
        $_SESSION['Otherid'] = $row['Otherid'];

        $role = $row['TokenClass'];

        if (strcmp($role, 'Auto') == 0) {
            $m->getAutomate()->runAutomation($m);
            $response->getBody()->write("Success!");
            return $response;
        }

        if (strcmp($role, 'User') == 0) {
            $seriesId = $row['Otherid'];
            $daysToNextFixture = $m->getSeries($seriesId)->getDaysToNextFixture();
            $index = $daysToNextFixture > 1 ? 0 : 1; // show the next fixture if it's more than 1 day away, otherwise show the latest fixture
            $route = "/participantSeries?seriesid=$seriesId&index=$index";
        } elseif (strcmp($role, 'Owner') == 0) {
            $seriesId = $row['Otherid'];
            $route = "/series?seriesid=$seriesId";
        } elseif (strcmp($role, 'Admin') == 0) {
            $route = "/serieslist";
        }
    return $response
          ->withHeader('Location', $route)
          ->withStatus(302);
    }
}