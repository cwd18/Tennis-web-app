<?php
# Send invitation emails

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class EmailSend
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = (int)$params['fixtureid'];
        $m = $this->container->get('Model');
        $f = $m->getFixture($fixtureId);
        $seriesId = $f->getSeriesid();
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkOwner($seriesId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $a = $m->getAutomate();
        $invitationsSent = $f->getInvitationsSent($fixtureId);
        if ($invitationsSent == 0 ) {
            $a->sendEmails($m, $fixtureId, $a::EMAIL_INVITATION);
        } else {
            $a->sendEmails($m, $fixtureId, $a::EMAIL_BOOKING);
        }
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId")
          ->withStatus(302);
    }
}
