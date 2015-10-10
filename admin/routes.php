<?php
use PHPRouter\RouteCollection;
use PHPRouter\Router;
use PHPRouter\Route;

$collection = new RouteCollection();

//check if logged in - if not just redirect
$settings = include __DIR__ . '/../data/settings.php';
if (!$_SESSION['logged_in']) {
    if(get_segment(1) !== 'auth') {
        redirect_to('auth');
    }
}

###AUTH###
$collection->attach(new Route('/auth', array(
    '_controller' => 'AuthController::index',
    'methods' => 'GET'
)));

$collection->attach(new Route('/auth/login', array(
    '_controller' => 'AuthController::login',
    'methods' => 'POST'
)));

$collection->attach(new Route('/auth/logout', array(
    '_controller' => 'AuthController::logout',
    'methods' => 'GET'
)));

###DASHBOARD###
$collection->attach(new Route('/', array(
    '_controller' => 'DashboardController::index',
    'methods' => 'GET'
)));

###CATEGORIES###
$collection->attach(new Route('/categories', array(
    '_controller' => 'CategoriesController::index',
    'methods' => 'GET'
)));

$collection->attach(new Route('/categories/save', array(
    '_controller' => 'CategoriesController::save',
    'methods' => 'POST'
)));

###VARIANTS###
$collection->attach(new Route('/variants', array(
    '_controller' => 'VariantsController::index',
    'methods' => 'GET'
)));

$collection->attach(new Route('/variants/save', array(
    '_controller' => 'VariantsController::save',
    'methods' => 'POST'
)));

###PRODUCTS###
$collection->attach(new Route('/products', array(
    '_controller' => 'ProductsController::index',
    'methods' => 'GET'
)));

$collection->attach(new Route('/products/add', array(
    '_controller' => 'ProductsController::getAdd',
    'methods' => 'GET'
)));

$collection->attach(new Route('/products/add', array(
    '_controller' => 'ProductsController::postAdd',
    'methods' => 'POST'
)));

$collection->attach(new Route('/products/edit/:slug', array(
    '_controller' => 'ProductsController::getEdit',
    'methods' => 'GET'
)));

$collection->attach(new Route('/products/edit', array(
    '_controller' => 'ProductsController::postEdit',
    'methods' => 'POST'
)));

$collection->attach(new Route('/products/remove/:slug', array(
    '_controller' => 'ProductsController::getRemove',
    'methods' => 'GET'
)));

$collection->attach(new Route('/products/clean', array(
    '_controller' => 'ProductsController::getCleanProducts',
    'methods' => 'GET'
)));

$collection->attach(new Route('/products/upload/', array(
    '_controller' => 'ProductsController::postUpload',
    'methods' => 'POST'
)));

$collection->attach(new Route('/products/upload/', array(
    '_controller' => 'ProductsController::getUpload',
    'methods' => 'GET'
)));

###ORDERS###
$collection->attach(new Route('/orders', array(
    '_controller' => 'OrdersController::index',
    'methods' => 'GET'
)));
$collection->attach(new Route('/orders/view', array(
    '_controller' => 'OrdersController::getView',
    'methods' => 'GET'
)));
$collection->attach(new Route('/orders/print', array(
    '_controller' => 'OrdersController::postPrint',
    'methods' => 'POST'
)));
$collection->attach(new Route('/orders/save', array(
    '_controller' => 'OrdersController::postNote',
    'methods' => 'POST'
)));
$collection->attach(new Route('/orders/delete', array(
    '_controller' => 'OrdersController::getDelete',
    'methods' => 'GET'
)));

###PRICING###
$collection->attach(new Route('/pricing', array(
    '_controller' => 'PricingController::index',
    'methods' => 'GET'
)));

$collection->attach(new Route('/pricing/save', array(
    '_controller' => 'PricingController::save',
    'methods' => 'POST'
)));

###SETTINGS###
$collection->attach(new Route('/settings', array(
    '_controller' => 'SettingsController::index',
    'methods' => 'GET'
)));

$collection->attach(new Route('/settings/save', array(
    '_controller' => 'SettingsController::save',
    'methods' => 'POST'
)));

$router = new Router($collection);
$router->setBasePath(ADMIN_URL);
$route = $router->matchCurrentRequest();

