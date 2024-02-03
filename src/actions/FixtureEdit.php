<?php
# Edit basic fixture data from form parameters and then display the fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureEdit
{
  private $container;

  public function __construct(ContainerInterface $container)
  {
      $this->container = $container;
  }

  public function __invoke(Request $request, Response $response): Response
  {
      $params = $request->getParsedBody();
      $fixtureId = $params['fixtureid'];
      $owner = $params['owner'];
      $date = $params['date'];
      $time = $params['time'];
      $model = $this->container->get('Model');
      $f = $model->getFixtures();
      $row = $f->updateBasicFixtureData($fixtureId, $owner, $date, $time);
      $owner0 = $row['FixtureOwner'];
      $date0 = $row['FixtureDate'];
      $time0 = substr($row['FixtureTime'],0,5);
      if ($owner0 != $owner) { $changes[] = "Owner: $owner0 -> $owner"; }
      if ($date0 != $date) { $changes[] = "Day: $date0 -> $date"; }
      if ($time0 != $time) { $changes[] = "Time: $time0 -> $time"; }
      if (empty($changes)) { $changes[] = "Nothing changed"; }
      $view = Twig::fromRequest($request);
      return $view->render($response, 'opcontinue.html', ['op' => 'Fixture data edits', 
      'link' => "fixture?fixtureid=$fixtureId", 'lines' => $changes]);
    }
}
