<?php
# Send email message to fixture participants 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutEmailMessage
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $params = $request->getParsedBody();
        $messageInsert = $params['messageInsert'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkOwnerAccessFixture($fixtureId))) {
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401);
        }
        $e = $m->getEmail();
        $server = $m->getServer();
        $f = $m->getFixture($fixtureId);
        $base = $f->getBasicFixtureData();
        $fixture = $f->getFixtureData();
        $tokens = $m->getTokens();
        $recipients = $f->getFixtureUsers();
        $subject = "Tennis " . $base['shortDate'] . " at " . $base['FixtureTime'];
        $twigFile = 'emailManualUpdate.twig';
        $replyTo = $base['OwnerEmail'];
        $twig = $m->getTwig();
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrCreateToken($recipient['Userid'], 'User', $base['Seriesid']);
        }
        foreach ($recipients as $to) {
            $message = $twig->render($twigFile, [
                'altmessage' => false,
                'base' => $base,
                'to' => $to,
                'server' => $server,
                'message' => $messageInsert,
                'f' => $fixture
            ]);
            $altmessage = $twig->render($twigFile, [
                'altmessage' => true,
                'base' => $base,
                'to' => $to,
                'server' => $server,
                'message' => $messageInsert,
                'f' => $fixture
            ]);
            $altmessage = str_replace(['</tr><tr>', '</thead><thead>'], "\n", $altmessage);
            $altmessage = str_replace(['</td><td>', '</th><th>'], "\t", $altmessage);
            $altmessage = strip_tags($altmessage);
            $e->sendEmail($replyTo, $to['EmailAddress'], $subject, $message, $altmessage);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}
