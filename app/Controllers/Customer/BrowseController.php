<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\FavoriteRepository;
use App\Models\FoodRepository;
use App\Models\OrderRepository;
use App\Models\RestaurantRepository;
use App\Models\ReviewRepository;

final class BrowseController extends Controller
{
    public function dashboard(): Response
    {
        $id = $this->userId();

        return $this->view('customer/dashboard', [
            'stats' => $this->make(OrderRepository::class)->customerStats($id),
            'favoriteCount' => $this->make(FavoriteRepository::class)->count($id),
            'activeOrders' => $this->make(OrderRepository::class)->list(['customer_id' => $id, 'statuses' => ['pending', 'preparing', 'ready', 'out_for_delivery']], 5),
            'pendingReviews' => count($this->make(ReviewRepository::class)->pendingFor($id)),
            'restaurants' => array_slice($this->make(RestaurantRepository::class)->approved($id), 0, 3),
        ]);
    }

    public function restaurants(): Response
    {
        $search = mb_substr($this->request->string('search'), 0, 100);
        $cuisine = $this->request->string('cuisine');
        $repository = $this->make(RestaurantRepository::class);

        return $this->view('customer/restaurants', [
            'search' => $search,
            'cuisine' => $cuisine,
            'cuisines' => $repository->cuisines(),
            'restaurants' => $repository->approved($this->userId(), $search, $cuisine),
            'foodResults' => $search === '' ? [] : $this->make(FoodRepository::class)->search($search),
        ]);
    }

    public function menu(int $id): Response
    {
        $restaurant = $this->make(RestaurantRepository::class)->approvedOrFail($id, $this->userId());
        $category = $this->request->string('category');
        $food = $this->make(FoodRepository::class);

        return $this->view('customer/menu', [
            'restaurant' => $restaurant,
            'category' => $category,
            'categories' => $food->categories($id),
            'items' => $food->menu($id, $category),
            'conflict' => $this->app->session->pull('cart_conflict'),
        ]);
    }

    public function favorites(): Response
    {
        $restaurants = array_values(array_filter(
            $this->make(RestaurantRepository::class)->approved($this->userId(), '', '', 500),
            static fn (array $r) => (bool) $r['is_favorite']
        ));

        return $this->view('customer/favorites', ['restaurants' => $restaurants]);
    }

    public function toggleFavorite(int $id): Response
    {
        $this->make(RestaurantRepository::class)->approvedOrFail($id, $this->userId());
        $added = $this->make(FavoriteRepository::class)->toggle($this->userId(), $id);

        return $this->back('/customer/favorites', $added ? 'Added to favorites.' : 'Removed from favorites.');
    }
}
