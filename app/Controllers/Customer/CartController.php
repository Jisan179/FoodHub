<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Controllers\Controller;
use App\Core\Response;
use App\Services\CartConflict;
use App\Services\CartService;
use App\Services\CheckoutService;

final class CartController extends Controller
{
    private function cart(): CartService
    {
        return $this->make(CartService::class);
    }

    public function index(): Response
    {
        return $this->view('customer/cart', ['cart' => $this->cart()->summary($this->userId())]);
    }

    public function add(): Response
    {
        $itemId = $this->request->int('item_id');
        $quantity = $this->request->int('quantity', 1);
        try {
            $name = $this->cart()->add($this->userId(), $itemId, $quantity, $this->request->string('replace') === '1');
        } catch (CartConflict $conflict) {
            // Ask the customer to confirm before the current cart is replaced
            $this->app->session->put('cart_conflict', ['item_id' => $itemId, 'quantity' => $quantity, 'message' => $conflict->getMessage()]);

            return $this->back('/customer/restaurants');
        }

        return $this->back('/customer/cart', "Added $name to your cart.");
    }

    public function update(int $id): Response
    {
        $this->cart()->setQuantity($this->userId(), $id, $this->request->int('quantity'));

        return $this->redirect('/customer/cart', 'Cart updated.');
    }

    public function remove(int $id): Response
    {
        $this->cart()->remove($this->userId(), $id);

        return $this->redirect('/customer/cart', 'Item removed.');
    }

    public function checkout(): Response
    {
        $cart = $this->cart()->summary($this->userId());
        if (!$cart['items']) {
            $this->app->session->flash('error', 'Your cart is empty.');

            return $this->redirect('/customer/cart');
        }

        return $this->view('customer/checkout', ['cart' => $cart]);
    }

    public function placeOrder(): Response
    {
        $input = $this->validate([
            'delivery_address' => 'required|max:255',
            'contact_phone' => 'required|phone',
            'payment_method' => 'required|in:cod,wallet',
        ]);
        $orderId = $this->make(CheckoutService::class)->place($this->userId(), $input['delivery_address'], $input['contact_phone'], $input['payment_method']);

        return $this->redirect("/customer/orders/$orderId", "Order #$orderId has been placed.");
    }
}
