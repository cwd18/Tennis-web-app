<?php
# List all series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesListView
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response): Response
    {
        $m = $this->container->get('Model');
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkAdmin())) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $tokens = $m->getTokens();
        $token = $tokens->getOrCreateToken(1, 'Auto', 0);
        $autoUrl = "/start/$token";
        $s = $m->getSeriesList();
        $l = $m->getEventLog();
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Europe/London'));
        $time = $now->format("H:i:s");
        $sqlTime = $m->db->runSQL("SELECT RIGHT(NOW(),8);")->fetchColumn();
        return $view->render($response, 'serieslist.html', 
            ['serieslist' => $s->getAllSeries(), 'phpTime' => $time, 'sqlTime' => $sqlTime,
            'autoUrl' => $autoUrl, 'log' => $l->list()]);
    }
}
