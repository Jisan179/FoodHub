<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Controllers\Controller;
use App\Core\Response;
use App\Core\ValidationError;
use App\Models\OrderRepository;
use App\Models\ReviewRepository;
use App\Services\OrderRules;
use App\Services\OrderService;

final class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = $this->make(OrderRepository::class);
        $filters = ['customer_id' => $this->userId(), 'status' => $this->request->string('status')];
        $page = $this->paginator($orders->count($filters));

        return $this->view('customer/orders', ['orders' => $orders->paginate($filters, $page), 'page' => $page, 'status' => $filters['status']]);
    }

    public function show(int $id): Response
    {
        $order = $this->make(OrderRepository::class)->visibleOrFail($id, $this->user());

        return $this->view('customer/order', [
            'order' => $order,
            'canCancel' => OrderRules::can($order['status'], 'cancelled', 'customer'),
        ]);
    }

    public function cancel(int $id): Response
    {
        $this->make(OrderService::class)->changeStatus($id, 'cancelled', $this->user());

        return $this->redirect("/customer/orders/$id", "Order #$id has been cancelled.");
    }

    public function reviews(): Response
    {
        $repository = $this->make(ReviewRepository::class);

        return $this->view('customer/reviews', [
            'pending' => $repository->pendingFor($this->userId()),
            'reviews' => $repository->forCustomer($this->userId()),
        ]);
    }

    private function reviewInput(): array
    {
        $input = $this->validate(['rating' => 'required|in:1,2,3,4,5', 'comment' => 'max:1000']);

        return [(int) $input['rating'], $input['comment']];
    }

    public function storeReview(): Response
    {
        $orderId = $this->request->int('order_id');
        $itemId = $this->request->int('item_id');
        if ($orderId < 1 || $itemId < 1) {
            throw new ValidationError(['review' => 'Choose an item to review.']);
        }
        [$rating, $comment] = $this->reviewInput();
        $this->make(ReviewRepository::class)->create($this->userId(), $orderId, $itemId, $rating, $comment);

        return $this->redirect('/customer/reviews', 'Thank you for your review.');
    }

    public function updateReview(int $id): Response
    {
        [$rating, $comment] = $this->reviewInput();
        $this->make(ReviewRepository::class)->update($id, $this->userId(), $rating, $comment);

        return $this->redirect('/customer/reviews', 'Review updated.');
    }

    public function destroyReview(int $id): Response
    {
        $this->make(ReviewRepository::class)->delete($id, $this->userId());

        return $this->redirect('/customer/reviews', 'Review deleted.');
    }
}
