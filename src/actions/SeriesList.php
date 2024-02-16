<?php
# List all series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesList
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response): Response
    {
        $role = $_SESSION['Role'] ?? 'Unknown';
        if (strcmp($role, 'Admin') !=0 ) {
            $response->getBody()->write("Not authorised: $role");
            return $response;
        }
        $s = $this->container->get('Model')->getSeries();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'serieslist.html', ['serieslist' => $s->getAllSeries()]);
    }
}
