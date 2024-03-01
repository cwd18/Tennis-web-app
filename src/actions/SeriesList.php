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
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkAdmin())) {
            $response->getBody()->write($error);
            return $response;
        }
        $tokens = $m->getTokens();
        $token = $tokens->getOrcreateToken(1, 'Auto', 0);
        $autoUrl = "/start/$token";
        $s = $m->getSeries();
        $l = $m->getEventLog();
        $sqlTime = $m->db->runSQL("SELECT RIGHT(NOW(),8);")->fetchColumn();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'serieslist.html', 
            ['serieslist' => $s->getAllSeries(), 'phpTime' => date("H:i:s"), 'sqlTime' => $sqlTime,
            'autoUrl' => $autoUrl, 'log' => $l->list()]);
    }
}
