<?php

declare(strict_types=1);

use App\Controllers\Admin\PlatformController;
use App\Controllers\Admin\UserController;
use App\Controllers\AuthController;
use App\Controllers\Customer\BrowseController;
use App\Controllers\Customer\CartController;
use App\Controllers\Customer\OrderController as CustomerOrders;
use App\Controllers\HomeController;
use App\Controllers\Manager\MenuController;
use App\Controllers\Manager\OrderController as ManagerOrders;
use App\Controllers\Manager\RestaurantController;
use App\Controllers\PageController;
use App\Controllers\ProfileController;
use App\Controllers\Rider\DeliveryController;
use App\Core\Router;

$router = new Router();

$router->get('/', [HomeController::class, 'index'], 'public');

// Information pages, open to everyone
foreach (['about', 'help', 'privacy', 'terms', 'security'] as $page) {
    $router->get("/$page", [PageController::class, $page], 'public');
}

// Guests
$router->get('/login', [AuthController::class, 'showLogin'], 'guest');
$router->post('/login', [AuthController::class, 'login'], 'guest');
$router->get('/register', [AuthController::class, 'showRegister'], 'guest');
$router->post('/register', [AuthController::class, 'register'], 'guest');
$router->get('/forgot-password', [AuthController::class, 'showForgot'], 'guest');
$router->post('/forgot-password', [AuthController::class, 'sendReset'], 'guest');
$router->get('/reset-password/{token}', [AuthController::class, 'showReset'], 'guest');
$router->post('/reset-password/{token}', [AuthController::class, 'reset'], 'guest');

// Any signed-in user
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/profile', [ProfileController::class, 'show']);
$router->post('/profile', [ProfileController::class, 'update']);
$router->post('/profile/password', [ProfileController::class, 'password']);
$router->post('/profile/deactivate', [ProfileController::class, 'deactivate']);
$router->post('/profile/delete', [ProfileController::class, 'destroy']);

// Customer
$router->get('/customer', [BrowseController::class, 'dashboard'], 'customer');
$router->get('/customer/restaurants', [BrowseController::class, 'restaurants'], 'customer');
$router->get('/customer/restaurants/{id}', [BrowseController::class, 'menu'], 'customer');
$router->post('/customer/restaurants/{id}/favorite', [BrowseController::class, 'toggleFavorite'], 'customer');
$router->get('/customer/favorites', [BrowseController::class, 'favorites'], 'customer');
$router->get('/customer/cart', [CartController::class, 'index'], 'customer');
$router->post('/customer/cart', [CartController::class, 'add'], 'customer');
$router->post('/customer/cart/{id}', [CartController::class, 'update'], 'customer');
$router->post('/customer/cart/{id}/remove', [CartController::class, 'remove'], 'customer');
$router->get('/customer/checkout', [CartController::class, 'checkout'], 'customer');
$router->post('/customer/checkout', [CartController::class, 'placeOrder'], 'customer');
$router->get('/customer/orders', [CustomerOrders::class, 'index'], 'customer');
$router->get('/customer/orders/{id}', [CustomerOrders::class, 'show'], 'customer');
$router->post('/customer/orders/{id}/cancel', [CustomerOrders::class, 'cancel'], 'customer');
$router->get('/customer/reviews', [CustomerOrders::class, 'reviews'], 'customer');
$router->post('/customer/reviews', [CustomerOrders::class, 'storeReview'], 'customer');
$router->post('/customer/reviews/{id}', [CustomerOrders::class, 'updateReview'], 'customer');
$router->post('/customer/reviews/{id}/delete', [CustomerOrders::class, 'destroyReview'], 'customer');

// Restaurant manager
$router->get('/manager', [RestaurantController::class, 'dashboard'], 'manager');
$router->get('/manager/restaurants/create', [RestaurantController::class, 'create'], 'manager');
$router->post('/manager/restaurants', [RestaurantController::class, 'store'], 'manager');
$router->get('/manager/restaurants/{id}/edit', [RestaurantController::class, 'edit'], 'manager');
$router->post('/manager/restaurants/{id}', [RestaurantController::class, 'update'], 'manager');
$router->post('/manager/restaurants/{id}/toggle', [RestaurantController::class, 'toggle'], 'manager');
$router->post('/manager/restaurants/{id}/delete', [RestaurantController::class, 'destroy'], 'manager');
$router->get('/manager/restaurants/{id}/menu', [MenuController::class, 'index'], 'manager');
$router->post('/manager/restaurants/{id}/menu', [MenuController::class, 'store'], 'manager');
$router->get('/manager/items/{id}/edit', [MenuController::class, 'edit'], 'manager');
$router->post('/manager/items/{id}', [MenuController::class, 'update'], 'manager');
$router->post('/manager/items/{id}/delete', [MenuController::class, 'destroy'], 'manager');
$router->get('/manager/orders', [ManagerOrders::class, 'index'], 'manager');
$router->get('/manager/orders/{id}', [ManagerOrders::class, 'show'], 'manager');
$router->post('/manager/orders/{id}/status', [ManagerOrders::class, 'status'], 'manager');

// Rider
$router->get('/rider', [DeliveryController::class, 'dashboard'], 'rider');
$router->get('/rider/history', [DeliveryController::class, 'history'], 'rider');
$router->post('/rider/deliveries/{id}/accept', [DeliveryController::class, 'accept'], 'rider');
$router->post('/rider/deliveries/{id}/pickup', [DeliveryController::class, 'pickup'], 'rider');
$router->post('/rider/deliveries/{id}/complete', [DeliveryController::class, 'complete'], 'rider');
$router->post('/rider/deliveries/{id}/release', [DeliveryController::class, 'release'], 'rider');

// Administrator
$router->get('/admin', [PlatformController::class, 'dashboard'], 'admin');
$router->get('/admin/users', [UserController::class, 'index'], 'admin');
$router->get('/admin/users/create', [UserController::class, 'create'], 'admin');
$router->post('/admin/users', [UserController::class, 'store'], 'admin');
$router->get('/admin/users/{id}/edit', [UserController::class, 'edit'], 'admin');
$router->post('/admin/users/{id}', [UserController::class, 'update'], 'admin');
$router->post('/admin/users/{id}/approve', [UserController::class, 'approve'], 'admin');
$router->post('/admin/users/{id}/delete', [UserController::class, 'destroy'], 'admin');
$router->get('/admin/restaurants', [PlatformController::class, 'restaurants'], 'admin');
$router->post('/admin/restaurants/{id}/status', [PlatformController::class, 'restaurantStatus'], 'admin');
$router->get('/admin/orders', [PlatformController::class, 'orders'], 'admin');
$router->get('/admin/orders/{id}', [PlatformController::class, 'order'], 'admin');
$router->post('/admin/orders/{id}/status', [PlatformController::class, 'orderStatus'], 'admin');
$router->post('/admin/orders/{id}/rider', [PlatformController::class, 'assignRider'], 'admin');

return $router;
