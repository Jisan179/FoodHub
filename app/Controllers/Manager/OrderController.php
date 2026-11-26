<?php

declare(strict_types=1);

namespace App\Controllers\Manager;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\OrderRepository;
use App\Services\OrderRules;
use App\Services\OrderService;

final class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = $this->make(OrderRepository::class);
        $open = $orders->list(['owner_id' => $this->userId(), 'statuses' => ['pending', 'preparing', 'ready', 'out_for_delivery']], 100);
        foreach ($open as &$order) {
            $order['items'] = $orders->items((int) $order['order_id']);
        }
        unset($order);

        $history = ['owner_id' => $this->userId(), 'statuses' => ['delivered', 'cancelled']];
        $page = $this->paginator($orders->count($history));

        return $this->view('manager/orders', ['open' => $open, 'history' => $orders->paginate($history, $page), 'page' => $page]);
    }

    public function show(int $id): Response
    {
        $order = $this->make(OrderRepository::class)->visibleOrFail($id, $this->user());

        return $this->view('manager/order', ['order' => $order, 'options' => OrderRules::options($order['status'], 'manager')]);
    }

    public function status(int $id): Response
    {
        $input = $this->validate(['status' => 'required|in:preparing,ready,cancelled']);
        $this->make(OrderService::class)->changeStatus($id, $input['status'], $this->user());

        return $this->back('/manager/orders', "Order #$id updated.");
    }
}
