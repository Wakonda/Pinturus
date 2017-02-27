<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Loader\YamlFileLoader;

// Register service providers.
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\RoutingServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\HttpFragmentServiceProvider());
// 

$app['security.role_hierarchy'] = array(
    'ROLE_ADMIN' => array('ROLE_USER'),
);

$app['security.access_rules'] = array(
    array('^/admin', 'ROLE_ADMIN'),
);

$app['session.storage.options'] = [
    'name' => "sykeXs"
];

$app['security.firewalls'] = array(
    'main' => array(
        'pattern' => '^/',
		'anonymous' => true,
		'remember_me' => array('key' => 'PGp#%sU^O5BR9^V%G6Jy'),
		'form' => array('login_path' => '/user/login', 'check_path' => '/admin/login_check','default_target_path'=> '/','always_use_default_target_path'=>true),
		'logout' => array('logout_path' => '/admin/logout'),
		'users' => function ($app) {
			return new Pinturus\Controller\UserProvider($app['db']);
		}
    )
);

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => $app['security.firewalls'],
	'security.role_hierarchy' => $app['security.role_hierarchy'],
	'security.access_rules' => $app['security.access_rules'],
	'session.storage.options' => $app['session.storage.options']
));

$app->register(new Silex\Provider\RememberMeServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'fr'
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.orm.proxies_namespace'     => 'DoctrineProxy',
    'db.orm.auto_generate_proxies' => true,
    'db.orm.entities'              => array(array(
        'type'      => 'annotation',       // как определяем поля в Entity
        'path'      => __DIR__,   // Путь, где храним классы
        'namespace' => 'Pinturus\Entity', // Пространство имен
    ))
));

$app['security.default_encoder'] = function ($app) {
    return $app['security.encoder.digest'];
};

$app->before(function () use ($app) {
	$request = $app['request_stack']->getCurrentRequest();
	$request->setLocale($app['locale']);
	$app['translator']->setLocale($app['locale']);
});

$app->boot();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true,
    ),
    'twig.path' => array(__DIR__ . '/Pinturus/Resources/views')
));

$app['twig'] = $app->extend('twig', function (\Twig_Environment $twig) use ($app) {
	$twig->addExtension(new Pinturus\Service\PinturusExtension($app));
 
	return $twig;
});

$app['twig']->addGlobal("dev", 1);

// Register repositories.
$app['repository.painting'] = function ($app) {
    return new Pinturus\Repository\PaintingRepository($app['db']);
};

// Register the error handler.
$app->error(function (\Exception $e, $code) use ($app) {

    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }
	
	return $app['twig']->render('Index/error.html.twig', array('code' => $code, 'message' => $e->getMessage()));
});

$app->before(function () use ($app) {
    $app['twig']->addGlobal('generic_layout', $app['twig']->loadTemplate('generic_layout.html.twig'));
});

// Register repositories
$app['repository.type'] = function ($app) {
    return new Pinturus\Repository\TypeRepository($app['db']);
};
$app['repository.country'] = function ($app) {
    return new Pinturus\Repository\CountryRepository($app['db']);
};
$app['repository.biography'] = function ($app) {
    return new Pinturus\Repository\BiographyRepository($app['db']);
};
$app['repository.city'] = function ($app) {
    return new Pinturus\Repository\CityRepository($app['db']);
};
$app['repository.version'] = function ($app) {
    return new Pinturus\Repository\VersionRepository($app['db']);
};
$app['repository.movement'] = function ($app) {
    return new Pinturus\Repository\MovementRepository($app['db']);
};
$app['repository.user'] = function ($app) {
    return new Pinturus\Repository\UserRepository($app['db']);
};
$app['repository.contact'] = function ($app) {
    return new Pinturus\Repository\ContactRepository($app['db']);
};
$app['repository.paintingvote'] = function ($app) {
    return new Pinturus\Repository\PaintingVoteRepository($app['db']);
};
$app['repository.comment'] = function ($app) {
	return new Pinturus\Repository\CommentRepository($app['db']);
};
$app['repository.page'] = function ($app) {
	return new Pinturus\Repository\PageRepository($app['db']);
};
$app['repository.location'] = function ($app) {
	return new Pinturus\Repository\LocationRepository($app['db']);
};

// Register controllers
$app["controllers.index"] = function($app) {
    return new Pinturus\Controller\IndexController();
};

$app["controllers.biographyadmin"] = function($app) {
    return new Pinturus\Controller\BiographyAdminController();
};

$app["controllers.countryadmin"] = function($app) {
    return new Pinturus\Controller\CountryAdminController();
};

$app["controllers.cityadmin"] = function($app) {
    return new Pinturus\Controller\CityAdminController();
};

$app["controllers.typeadmin"] = function($app) {
    return new Pinturus\Controller\TypeAdminController();
};

$app["controllers.movementadmin"] = function($app) {
    return new Pinturus\Controller\MovementAdminController();
};

$app["controllers.collectionadmin"] = function($app) {
    return new Pinturus\Controller\CollectionAdminController();
};

$app["controllers.paintingadmin"] = function($app) {
    return new Pinturus\Controller\PaintingAdminController();
};

$app["controllers.useradmin"] = function($app) {
    return new Pinturus\Controller\UserAdminController();
};

$app["controllers.admin"] = function($app) {
    return new Pinturus\Controller\AdminController();
};

$app["controllers.contact"] = function($app) {
    return new Pinturus\Controller\ContactController();
};

$app["controllers.contactadmin"] = function($app) {
    return new Pinturus\Controller\ContactAdminController();
};

$app["controllers.locationadmin"] = function($app) {
	return new Pinturus\Controller\LocationAdminController();
};

$app["controllers.versionadmin"] = function($app) {
    return new Pinturus\Controller\VersionAdminController();
};

$app["controllers.user"] = function($app) {
    return new Pinturus\Controller\UserController();
};

$app["controllers.paintingvote"] = function($app) {
    return new Pinturus\Controller\PaintingVoteController();
};

$app["controllers.comment"] = function($app) {
    return new Pinturus\Controller\CommentController();
};

$app["controllers.sitemap"] = function($app) {
	return new Pinturus\Controller\SitemapController();
};

$app["controllers.pageadmin"] = function($app) {
	return new Pinturus\Controller\PageAdminController();
};

$app["controllers.sendpainting"] = function($app) {
	return new Pinturus\Controller\SendPaintingController();
};

// Register Services
$app['generic_function'] = function ($app) {
    return new Poeticus\Service\GenericFunction($app);
};

// Form extension
$app['form.type.extensions'] = $app->extend('form.type.extensions', function ($extensions) use ($app) {
    $extensions[] = new Pinturus\Form\Extension\ButtonTypeIconExtension();
    return $extensions;
});

// SwiftMailer
// See http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app['swiftmailer.options'] = array(
	'host' => 'smtp.gmail.com',
	'port' => 465,
    'username' => 'amatukami66@gmail.com',
    'password' => 'rclens66',
    'encryption' => 'ssl'
);

// Global
$app['web_directory'] = realpath(__DIR__."/../web");
// die(var_dump($app["request"]));
return $app;