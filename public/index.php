<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use TennisApp\Model;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Google\Cloud\Logging\LoggingClient;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use TennisApp\SessionHandler;
use TennisApp\SessionLog;
use TennisApp\Database;

require_once __DIR__ . '/../vendor/autoload.php';
require_once('../settings.php'); // defines $db_config, $email_config, $server

// Need to start a session asap, which requires the database connection for the handler
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;port=%s;charset=%s',
    $db_config['host'],
    $db_config['name'],
    $db_config['port'],
    $db_config['charset']
);
$username = $db_config['username'];
$password = $db_config['password'];
$db = new Database($dsn, $username, $password);
$sessionLog = new SessionLog($db);
$sessionHandler = new SessionHandler($db, $sessionLog);
session_set_save_handler(
    $sessionHandler,
    true
); // register database session handler to enable serverless sessions
session_start(); // creates a new session if no PHPSESSID cookie exists
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/templates');
$twigForEmail = new \Twig\Environment($loader);
$model = new Model($db, $email_config, $server, $twigForEmail);


// Create Container using PHP-DI
$container = new Container();

// Register Model in container
$container->set('Model', function () use ($model) {
    return $model;
});

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

// Parse json, form data and xml
$app->addBodyParsingMiddleware();

// This middleware will append the response header Access-Control-Allow-Methods with all allowed methods
$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

    $response = $handler->handle($request);

    $response = $response->withHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    $response = $response->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, DELETE');
    $response = $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);

    // Allow Ajax CORS requests with Authorization header
    $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');

    return $response;
});

// Add Error Handling Middleware
if (array_key_exists('GAE_APPLICATION', $_SERVER)) {
    $logging = new LoggingClient(); // Google logging client
    $logger = $logging->psrLogger('TennisApp');
} else {
    $logger = new Logger('TennisApp'); // Monolog
    $streamHandler = new StreamHandler('../logs/app.log');
    $logger->pushHandler($streamHandler);
}
$app->addErrorMiddleware(true, true, true, $logger);

// Create Twig
$twig = Twig::create(__DIR__ . '/../src/templates', ['cache' => false, 'debug' => true]);

// Add Twig global variables for session user and role
$te = $twig->getEnvironment();
$m = $container->get('Model');
$te->addGlobal('Role', $m->sessionRole());
$userData = $m->getUsers()->getUserData($m->sessionUser());
if ($userData != false) {
    $te->addGlobal('SessionUser', $userData['FirstName'] . ' ' . $userData['LastName']);
}

$twig->addExtension(new \Twig\Extension\DebugExtension());

$twig->addExtension(new \Twig\Extra\Markdown\MarkdownExtension());
$twig->addRuntimeLoader(new class implements RuntimeLoaderInterface
{
    public function load($class)
    {
        if (MarkdownRuntime::class === $class) {
            return new MarkdownRuntime(new DefaultMarkdown());
        }
    }
});

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// The RoutingMiddleware should be added after our CORS middleware so routing is performed first
$app->addRoutingMiddleware();


// Define app routes

// CORS preflight requests
$app->map(['OPTIONS'], '/{routes:.+}', function ($request, $response) {
    return $response;
});

// Token-based app entry
$app->get('/oldstart/{token}', \TennisApp\Action\Start::class);

// Create user routes
$app->get('/userlist', \TennisApp\Action\UserList::class);
$app->get('/useraddform', \TennisApp\Action\UserAddForm::class);
$app->post('/useradd', \TennisApp\Action\UserAdd::class);
$app->get('/useredit', \TennisApp\Action\UserEdit::class);
$app->get('/userdelete', \TennisApp\Action\UserDelete::class);
$app->post('/userupdate', \TennisApp\Action\UserUpdate::class);

// Create series routes
$app->get('/serieslist', \TennisApp\Action\SeriesListView::class);
$app->get('/seriesaddform', \TennisApp\Action\SeriesAddForm::class);
$app->post('/seriesadd', \TennisApp\Action\SeriesAdd::class);
$app->get('/series', \TennisApp\Action\SeriesView::class);
$app->get('/seriesdeleteform', \TennisApp\Action\SeriesDeleteForm::class);
$app->get('/seriesdelete', \TennisApp\Action\SeriesDelete::class);
$app->get('/seriesaddusersform', \TennisApp\Action\SeriesAddUsersForm::class);
$app->post('/seriesaddusers', \TennisApp\Action\SeriesAddUsers::class);
$app->get('/seriesdelusersform', \TennisApp\Action\SeriesDelUsersForm::class);
$app->post('/seriesdelusers', \TennisApp\Action\SeriesDelUsers::class);
$app->get('/serieseditform', \TennisApp\Action\SeriesEditForm::class);
$app->post('/seriesedit', \TennisApp\Action\SeriesEdit::class);

// Create fixture routes
$app->get('/fixture', \TennisApp\Action\FixtureView::class);
$app->get('/fixturedeleteform', \TennisApp\Action\FixtureDeleteForm::class);
$app->get('/fixturedelete', \TennisApp\Action\FixtureDelete::class);
$app->get('/fixtureaddusersform', \TennisApp\Action\FixtureAddUsersForm::class);
$app->post('/fixtureaddusers', \TennisApp\Action\FixtureAddUsers::class);
$app->get('/fixturedelusersform', \TennisApp\Action\FixtureDelUsersForm::class);
$app->post('/fixturedelusers', \TennisApp\Action\FixtureDelUsers::class);
$app->get('/fixtureeditform', \TennisApp\Action\FixtureEditForm::class);
$app->post('/fixtureedit', \TennisApp\Action\FixtureEdit::class);
$app->get('/fixturewantstoplayform', \TennisApp\Action\FixtureWantsToPlayForm::class);
$app->post('/fixturewantstoplay', \TennisApp\Action\FixtureWantsToPlay::class);
$app->get('/fixturesetplayingform', \TennisApp\Action\FixtureSetPlayingForm::class);
$app->post('/fixturesetplaying', \TennisApp\Action\FixtureSetPlaying::class);
$app->get('/fixturesetautoplaying', \TennisApp\Action\FixtureSetAutoPlaying::class);
$app->get('/fixtureresetplaying', \TennisApp\Action\FixtureResetPlaying::class);
$app->get('/fixtureCreateRequests', \TennisApp\Action\FixtureCreateRequests::class);

// Create participant routes
$app->get('/participant', \TennisApp\Action\ParticipantView::class);
$app->get('/participantWantsToPlay', \TennisApp\Action\ParticipantWantsToPlay::class);
$app->get('/participantSeries', \TennisApp\Action\ParticipantSeries::class);

// APIs
$app->get('/api/start/{token}', \TennisApp\Action\ApiStart::class);
$app->get('/api/session', \TennisApp\Action\ApiGetSession::class);
$app->get('/api/serieslist', \TennisApp\Action\ApiSeriesList::class);
$app->get('/api/participantBookings/{fixtureid}/{userid}', \TennisApp\Action\ApiGetParticipantBookings::class);
$app->put('/api/participantBookings/{fixtureid}/{userid}', \TennisApp\Action\ApiPutParticipantBookings::class);
$app->get('/api/bookings/{type}/{fixtureid}', \TennisApp\Action\ApiGetBookings::class);
$app->get('/api/bookingRequestsTable/{fixtureid}', \TennisApp\Action\ApiGetBookingRequestsTable::class);
$app->put('/api/bookings/{type}/{fixtureid}', \TennisApp\Action\ApiPutBookings::class);
$app->get('/api/participants/{fixtureid}/{filter}', \TennisApp\Action\ApiGetParticipants::class);
$app->get('/api/participantData/{fixtureid}/{userid}', \TennisApp\Action\ApiGetParticipantData::class);
$app->put('/api/participantWantsToPlay/{fixtureid}/{userid}/{value}', \TennisApp\Action\ApiPutParticipantWantsToPlay::class);
$app->get('/api/participantWantsToPlay/{fixtureid}/{userid}', \TennisApp\Action\ApiGetParticipantWantsToPlay::class);
$app->put('/api/participantSetPlaying/{fixtureid}/{userid}/{value}', \TennisApp\Action\ApiPutParticipantPlaying::class);
$app->get('/api/playerLists/{fixtureid}', \TennisApp\Action\ApiGetPlayerLists::class);
$app->get('/api/bookingViewGrid/{type}/{fixtureid}', \TennisApp\Action\ApiGetBookingViewGrid::class);
$app->get('/api/getEmailList/{fixtureid}', \TennisApp\Action\ApiGetEmailList::class);
$app->get('/api/fixtures/{seriesid}', \TennisApp\Action\ApiGetFixtures::class);
$app->get('/api/absentBookers/{fixtureid}', \TennisApp\Action\ApiGetAbsentBookers::class);
$app->put('/api/playing/{fixtureid}/{mode}', \TennisApp\Action\ApiPutPlaying::class);
$app->put('/api/courts/{fixtureid}/{type}/{scope}/{courts}', \TennisApp\Action\ApiPutCourts::class);
$app->put('/api/toggleBooking/{fixtureid}/{time}/{court}', \TennisApp\Action\ApiPutToggleBooking::class);
$app->get('/api/userlist/{fixtureid}', \TennisApp\Action\ApiGetUserList::class);
$app->put('/api/user/{userid}', \TennisApp\Action\ApiPutUser::class);
$app->delete('/api/user/{scope}/{fixtureid}/{userid}', \TennisApp\Action\ApiDeleteUser::class);
$app->put('/api/candidates/{fixtureid}', \TennisApp\Action\ApiPutCandidates::class);
$app->put('/api/owner/{fixtureid}/{scope}/{ownerid}', \TennisApp\Action\ApiPutOwner::class);
$app->put('/api/alternateFixtureTime/{fixtureid}', \TennisApp\Action\ApiPutAlternateFixtureTime::class);
$app->post('/api/emailMessage/{fixtureid}', \TennisApp\Action\ApiPutEmailMessage::class);

try {
    $app->run();
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'An unexpected error occurred.';
    echo $e->getMessage();
    echo $e->getTraceAsString();
    session_write_close();
}
