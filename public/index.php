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

// Add Error Handling Middleware
$app->addErrorMiddleware(true, true, true);

// Create Twig
$twig = Twig::create(__DIR__ . '/../src/templates', ['cache' => false, 'debug' => true]);

$te = $twig->getEnvironment();
$m = $container->get('Model');
$u = $m->getUsers();
$te->addGlobal('Role', $m->sessionRole());
$te->addGlobal('SessionUser', $u->getUsername($m->sessionUser()));

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

// Token-based entry
$app->get('/start/{token}', \TennisApp\Action\Start::class);

// Create user routes
$app->get('/userlist', \TennisApp\Action\UserList::class);
$app->get('/useraddform', \TennisApp\Action\UserAddForm::class);
$app->post('/useradd', \TennisApp\Action\UserAdd::class);
$app->get('/useredit', \TennisApp\Action\UserEdit::class);
$app->get('/userdelete', \TennisApp\Action\UserDelete::class);
$app->post('/userupdate', \TennisApp\Action\UserUpdate::class);

// Create series routes
$app->get('/serieslist', \TennisApp\Action\SeriesList::class);
$app->get('/seriesaddform', \TennisApp\Action\SeriesAddForm::class);
$app->post('/seriesadd', \TennisApp\Action\SeriesAdd::class);
$app->get('/series', \TennisApp\Action\SeriesView::class);
$app->get('/seriesdeleteform', \TennisApp\Action\SeriesDeleteForm::class);
$app->get('/seriesdelete', \TennisApp\Action\SeriesDelete::class);
$app->get('/addfixture', \TennisApp\Action\FixtureAdd::class);
$app->get('/seriesaddusersform', \TennisApp\Action\SeriesAddUsersForm::class);
$app->post('/seriesaddusers', \TennisApp\Action\SeriesAddUsers::class);
$app->get('/seriesdelusersform', \TennisApp\Action\SeriesDelUsersForm::class);
$app->post('/seriesdelusers', \TennisApp\Action\SeriesDelUsers::class);
$app->get('/serieseditform', \TennisApp\Action\SeriesEditForm::class);
$app->post('/seriesedit', \TennisApp\Action\SeriesEdit::class);

// Create fixture routes
$app->get('/fixture', \TennisApp\Action\FixtureView::class);
$app->get('/fixturenotice', \TennisApp\Action\FixtureNotice::class);
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
$app->get('/fixturesetbookersplaying', \TennisApp\Action\FixtureSetBookersPlaying::class);
$app->get('/fixtureresetplaying', \TennisApp\Action\FixtureResetPlaying::class);

// Create participant routes
$app->get('/participantPage', \TennisApp\Action\ParticipantPage::class);
$app->get('/participant', \TennisApp\Action\ParticipantView::class);
$app->get('/participantWantsToPlay', \TennisApp\Action\ParticipantWantsToPlay::class);
$app->get('/participantBook', \TennisApp\Action\ParticipantBook::class);
$app->get('/participantAddBooking', \TennisApp\Action\ParticipantAddBooking::class);
$app->get('/participantDelBooking', \TennisApp\Action\ParticipantDelBooking::class);

// Create email routes
$app->get('/emailConfirm', \TennisApp\Action\EmailConfirm::class);
$app->get('/emailSend', \TennisApp\Action\EmailSend::class);

$app->run();