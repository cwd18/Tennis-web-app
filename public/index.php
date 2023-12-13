<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use TennisApp\Users;
use TennisApp\Series;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/users.php';
require __DIR__ . '/../src/series.php';

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

$app = AppFactory::create();

// Add Error Handling Middleware
$app->addErrorMiddleware(true, true, true);

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false, 'debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/userlist', function (Request $request, Response $response, $args) {
    global $pdo;
    $users = new Users($pdo);
    $view = Twig::fromRequest($request);
    $allUsers = $users->getAllUsers();
    return $view->render($response, 'userlist.html', ['userlist' => $allUsers]);
})->setName('userlist');

$app->get('/adduserform', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'adduserform.html', ['input' => 'none']);
})->setName('adduserform');

$app->post('/adduser', function (Request $request, Response $response, $args) {
    global $pdo;
    $users = new Users($pdo);
    $params = $request->getParsedBody();
    $row = $users->addUser($params['fname'], $params['lname'], $params['email']);
    $view = Twig::fromRequest($request);
    return $view->render($response, 'edituser.html', $row);
})->setName('edituser');

$app->get('/edituser', function (Request $request, Response $response, $args) {
    global $pdo;
    $users = new Users($pdo);
    $params = $request->getQueryParams();
    $row = $users->getUser($params['Userid']);
    $view = Twig::fromRequest($request);
    return $view->render($response, 'edituser.html', $row);
})->setName('edituser');

$app->get('/deleteuser', function (Request $request, Response $response, $args) {
    global $pdo;
    $users = new Users($pdo);
    $params = $request->getQueryParams();
    $users->deleteUser($params['Userid']);
    $view = Twig::fromRequest($request);
    return $view->render($response, 'users.html', ['users' => $users->getAllUsers()]);
})->setName('users');

$app->post('/updateuser', function (Request $request, Response $response, $args) {
    global $pdo;
    $users = new Users($pdo);
    $params = $request->getParsedBody();
    $users->updateUser($params['Userid'], $params['fname'], $params['lname'], $params['email']);
    $view = Twig::fromRequest($request);
    return $view->render($response, 'users.html', ['users' => $users->getAllUsers()]);
})->setName('users');

$app->get('/serieslist', function (Request $request, Response $response, $args) {
    global $pdo;
    $series = new Series($pdo);
    $view = Twig::fromRequest($request);
    return $view->render($response, 'serieslist.html', ['serieslist' => $series->getAllSeries()]);
})->setName('serieslist');

$app->get('/series', function (Request $request, Response $response, $args) {
    global $pdo;
    $series = new Series($pdo);
    $params = $request->getQueryParams();
    $view = Twig::fromRequest($request);
    $header = $series->getSeries($params['seriesid']);
    return $view->render($response, 'series.html', [
        'description' => $header['description'],
        'owner' => $header['owner']
        ]);
})->setName('serieslist');

$app->run();