<?php
# Set whether participant wants to play

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantWantsToPlay
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $userId = $params['userid'];
        $wantsToPlay = $params['WantsToPlay'];
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        $f->setParticipantWantsToPlay($fixtureId, $userId, $wantsToPlay);
        $lines[] = $wantsToPlay?"Person wants to play":"Person doesn't want to play";
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => 'Wants to play', 
        'link' => "participant?fixtureid=$fixtureId&userid=$userId", 'lines' => $lines]);
    }
}
