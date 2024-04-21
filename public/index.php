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

require_once __DIR__ . '/../vendor/autoload.php';

// Create Container using PHP-DI
$container = new Container();

// Create model
$container->set('Model', function () {
    include('../settings.php');
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/templates');
    $twigForEmail = new \Twig\Environment($loader);
    return new Model($db_config, $email_config, $server, $twigForEmail);
});

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

// Parse json, form data and xml
$app->addBodyParsingMiddleware();

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
    $te->addGlobal('SessionUser', $userData['FirstName'] . ' ' . $userData['LastName']);}

$twig->addExtension(new \Twig\Extension\DebugExtension());

$twig->addExtension(new \Twig\Extra\Markdown\MarkdownExtension());
$twig->addRuntimeLoader(new class implements RuntimeLoaderInterface {
    public function load($class) {
        if (MarkdownRuntime::class === $class) {
            return new MarkdownRuntime(new DefaultMarkdown());
        }
    }
});

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// Token-based app entry
$app->get('/start/{token}', \TennisApp\Action\Start::class);

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

// Create email routes
$app->get('/emailConfirm', \TennisApp\Action\EmailConfirm::class);
$app->get('/emailSend', \TennisApp\Action\EmailSend::class);

// Test React page
$app->get('/testReact', \TennisApp\Action\TestReact::class);

// APIs
$app->get('/api/serieslist', \TennisApp\Action\ApiSeriesList::class);
$app->get('/api/participantBookings/{fixtureid}/{userid}', \TennisApp\Action\ApiGetParticipantBookings::class);
$app->put('/api/participantBookings/{fixtureid}/{userid}', \TennisApp\Action\ApiPutParticipantBookings::class);
$app->get('/api/bookingRequests/{fixtureid}', \TennisApp\Action\ApiGetBookingRequests::class);
$app->get('/api/bookingRequestsTable/{fixtureid}', \TennisApp\Action\ApiGetBookingRequestsTable::class);
$app->put('/api/bookingRequests/{fixtureid}', \TennisApp\Action\ApiPutBookingRequests::class);
$app->get('/api/participants/{fixtureid}', \TennisApp\Action\ApiGetParticipants::class);
$app->get('/api/participantData/{fixtureid}/{userid}', \TennisApp\Action\ApiGetParticipantData::class);
$app->put('/api/participantWantsToPlay/{fixtureid}/{userid}/{value}', \TennisApp\Action\ApiPutParticipantWantsToPlay::class);
$app->get('/api/participantWantsToPlay/{fixtureid}/{userid}', \TennisApp\Action\ApiGetParticipantWantsToPlay::class);
$app->get('/api/playerLists/{fixtureid}', \TennisApp\Action\ApiGetPlayerLists::class);
$app->get('/api/bookingViewGrid/{fixtureid}', \TennisApp\Action\ApiGetBookingViewGrid::class);
$app->get('/api/getEmailList/{fixtureid}', \TennisApp\Action\ApiGetEmailList::class);

$app->run();