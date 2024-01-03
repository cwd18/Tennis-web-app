<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use TennisApp\Users;
use TennisApp\Series;
use TennisApp\Fixtures;

require_once __DIR__ . '/../vendor/autoload.php';

include('../settings.php');

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;dbname=%s;port=%s;charset=%s',
        $settings['host'],
        $settings['name'],
        $settings['port'],
        $settings['charset']
    ),
    $settings['username'],
    $settings['password']
);
$GLOBALS['pdo']=$pdo;

$app = AppFactory::create();

// Add Error Handling Middleware
$app->addErrorMiddleware(true, true, true);

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false, 'debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/userlist', \TennisApp\Action\UserList::class);

$app->get('/useraddform', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'useraddform.html', ['input' => 'none']);
});

$app->post('/adduser', \TennisApp\Action\UserAdd::class);

$app->get('/useredit', \TennisApp\Action\UserEdit::class);

$app->get('/userdelete', \TennisApp\Action\UserDelete::class);

$app->post('/userupdate', function (Request $request, Response $response, $args) {
    global $pdo;
    $users = new Users($pdo);
    $params = $request->getParsedBody();
    $users->updateUser($params['Userid'], $params['fname'], $params['lname'], $params['email']);
    $view = Twig::fromRequest($request);
    return $view->render($response, 'users.html', ['users' => $users->getAllUsers()]);
});

$app->get('/serieslist', function (Request $request, Response $response, $args) {
    global $pdo;
    $series = new Series($pdo);
    $view = Twig::fromRequest($request);
    return $view->render($response, 'serieslist.html', ['serieslist' => $series->getAllSeries()]);
});

$app->get('/series', function (Request $request, Response $response, $args) {
    global $pdo;
    $params = $request->getQueryParams();
    $seriesId = $params['seriesid'];
    $series = new Series($pdo);
    $view = Twig::fromRequest($request);
    $s = $series->getSeries($seriesId);
    return $view->render($response, 'series.html', [
        'seriesid' => $seriesId,
        'description' => $s['description'],
        'owner' => $s['owner'],
        'participants' => $s['participants'],
        'fixtures' => $s['fixtures']
        ]);
});

$app->get('/addfixture', function (Request $request, Response $response, $args) {
    global $pdo;
    $params = $request->getQueryParams();
    $seriesId = $params['seriesid'];
    $fixtures = new Fixtures($pdo);
    $fixtures->addNextFixtureToSeries($seriesId);
    $series = new Series($pdo);
    $view = Twig::fromRequest($request);
    $s = $series->getSeries($seriesId);
    return $view->render($response, 'series.html', [
        'seriesid' => $seriesId,
        'description' => $s['description'],
        'owner' => $s['owner'],
        'participants' => $s['participants'],
        'fixtures' => $s['fixtures']
        ]);
});



$app->run();