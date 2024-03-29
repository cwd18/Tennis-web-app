<?php
# Email invitations or booking request

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class EmailConfirm
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
        $base = $f->getBasicFixtureData();
        if ($base['InvitationsSent'] == 0 ) {
            $recipients = $f->getWannaPlayRecipients();
            $subject = "Tennis " . $base['shortDate'];
        } else {
            $recipients = $f->getBookingRequestRecipients();
            $subject = "Book a court for " . $base['shortDate'];
            $base['requests'] = $f->getRequestedBookings();
        }
        $tokens = $m->getTokens();
        foreach ($recipients as &$recipient) {
            $recipient['Token'] = $tokens->getOrCreateToken($recipient['Userid'], 'User', $fixtureId);
        }
        return $view->render($response, 'emailConfirm.html', ['base' => $base, 
        'subject' => $subject, 'recipients' => $recipients,
        'server' => $m->getServer(), 
        'continuelink' => "emailSend?fixtureid=$fixtureId", 
        'cancellink' => "fixture?fixtureid=$fixtureId"]);
    }
}
