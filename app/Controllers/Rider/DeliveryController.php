<?php

declare(strict_types=1);

namespace App\Controllers\Rider;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\DeliveryRepository;
use App\Services\DeliveryService;

final class DeliveryController extends Controller
{
    private function deliveries(): DeliveryRepository
    {
        return $this->make(DeliveryRepository::class);
    }

    private function service(): DeliveryService
    {
        return $this->make(DeliveryService::class);
    }

    public function dashboard(): Response
    {
        $id = $this->userId();

        return $this->view('rider/dashboard', [
            'available' => $this->deliveries()->available(),
            'active' => $this->deliveries()->activeFor($id),
            'delivered' => $this->deliveries()->countDelivered($id),
            'earnings' => $this->deliveries()->earnings($id),
        ]);
    }

    public function history(): Response
    {
        $id = $this->userId();
        $page = $this->paginator($this->deliveries()->countDelivered($id));

        return $this->view('rider/history', [
            'deliveries' => $this->deliveries()->history($id, $page),
            'page' => $page,
            'earnings' => $this->deliveries()->earnings($id),
        ]);
    }

    public function accept(int $id): Response
    {
        $this->service()->accept($id, $this->userId());

        return $this->redirect('/rider', 'Delivery accepted.');
    }

    public function pickup(int $id): Response
    {
        $this->service()->pickUp($id, $this->userId());

        return $this->redirect('/rider', 'Pickup confirmed.');
    }

    public function complete(int $id): Response
    {
        $this->service()->complete($id, $this->userId());

        return $this->redirect('/rider', 'Delivery completed.');
    }

    public function release(int $id): Response
    {
        $input = $this->validate(['reason' => 'required|max:200']);
        $this->service()->release($id, $this->userId(), $input['reason']);

        return $this->redirect('/rider', 'Delivery released. Another rider can accept it.');
    }
}
