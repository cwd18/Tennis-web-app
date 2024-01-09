<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
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

// Create user routes
$app->get('/userlist', \TennisApp\Action\UserList::class);
$app->get('/useraddform', \TennisApp\Action\UserAddForm::class);
$app->post('/useradd', \TennisApp\Action\UserAdd::class);
$app->get('/useredit', \TennisApp\Action\UserEdit::class);
$app->get('/userdelete', \TennisApp\Action\UserDelete::class);
$app->post('/userupdate', \TennisApp\Action\UserUpdate::class);

// Create series routes
$app->get('/serieslist', \TennisApp\Action\SeriesList::class);
$app->get('/series', \TennisApp\Action\SeriesView::class);
$app->get('/seriesdelete', \TennisApp\Action\SeriesDelete::class);
$app->get('/addfixture', \TennisApp\Action\FixtureAdd::class);
$app->get('/fixture', \TennisApp\Action\FixtureView::class);
$app->get('/fixturedelete', \TennisApp\Action\FixtureDelete::class);
$app->get('/seriesaddusersform', \TennisApp\Action\SeriesAddUsersForm::class);
$app->post('/seriesaddusers', \TennisApp\Action\SeriesAddUsers::class);
$app->get('/seriesdelusersform', \TennisApp\Action\SeriesDelUsersForm::class);
$app->post('/seriesdelusers', \TennisApp\Action\SeriesDelUsers::class);

$app->run();