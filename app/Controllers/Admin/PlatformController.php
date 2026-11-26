<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\OrderRepository;
use App\Models\RestaurantRepository;
use App\Models\UserRepository;
use App\Services\OrderRules;
use App\Services\OrderService;

final class PlatformController extends Controller
{
    public function dashboard(): Response
    {
        $users = $this->make(UserRepository::class);
        $orders = $this->make(OrderRepository::class);
        $restaurants = $this->make(RestaurantRepository::class);

        return $this->view('admin/dashboard', [
            'userCounts' => $users->countByRole(),
            'pendingUsers' => $users->pending(),
            'pendingRestaurants' => $restaurants->count('pending'),
            'orderCount' => $orders->count(),
            'revenue' => $orders->revenue(),
            'recentOrders' => $orders->list([], 5),
        ]);
    }

    public function restaurants(): Response
    {
        $repository = $this->make(RestaurantRepository::class);
        $status = $this->request->string('status');
        $page = $this->paginator($repository->count($status));

        return $this->view('admin/restaurants', ['restaurants' => $repository->paginate($status, $page), 'page' => $page, 'status' => $status]);
    }

    public function restaurantStatus(int $id): Response
    {
        $input = $this->validate(['status' => 'required|in:pending,approved,rejected,suspended', 'reason' => 'max:255']);
        $this->make(RestaurantRepository::class)->setStatus($id, $input['status'], $input['reason']);

        return $this->back('/admin/restaurants', 'Restaurant status updated.');
    }

    public function orders(): Response
    {
        $orders = $this->make(OrderRepository::class);
        $filters = ['status' => $this->request->string('status')];
        $page = $this->paginator($orders->count($filters));

        return $this->view('admin/orders', ['orders' => $orders->paginate($filters, $page), 'page' => $page, 'status' => $filters['status']]);
    }

    public function order(int $id): Response
    {
        $order = $this->make(OrderRepository::class)->visibleOrFail($id, $this->user());

        return $this->view('admin/order', [
            'order' => $order,
            'options' => OrderRules::options($order['status'], 'admin'),
            'riders' => $this->make(UserRepository::class)->activeRiders(),
        ]);
    }

    public function orderStatus(int $id): Response
    {
        $input = $this->validate(['status' => 'required|in:preparing,ready,cancelled']);
        $this->make(OrderService::class)->changeStatus($id, $input['status'], $this->user());

        return $this->redirect("/admin/orders/$id", 'Order status updated.');
    }

    public function assignRider(int $id): Response
    {
        $riderId = $this->request->int('rider_id');
        $this->make(OrderService::class)->assignRider($id, $riderId > 0 ? $riderId : null, $this->user());

        return $this->redirect("/admin/orders/$id", 'Rider assignment updated.');
    }
}
