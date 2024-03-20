<?php
# Set whether participant wants to play

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantWantsToPlay
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
        $userId = $params['userid'];
        $wantsToPlay = $params['WantsToPlay'];
        $m = $this->container->get('Model');
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkUser($fixtureId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $f = $m->getFixture($fixtureId);
        if ($wantsToPlay) {
            $f->setWantsToPlay($userId);
        } else {
            $f->setWantsNotToPlay($userId);
        }
        $outPath = strcmp($m->sessionRole(),'User') == 0 ? "/fixturenotice?fixtureid=$fixtureId" :
         "/participant?fixtureid=$fixtureId&userid=$userId";
        return $response
          ->withHeader('Location', $outPath)
          ->withStatus(302);
    }
}
