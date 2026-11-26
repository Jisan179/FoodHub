<?php

declare(strict_types=1);

namespace App\Controllers\Manager;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\FoodRepository;
use App\Models\OrderRepository;
use App\Models\RestaurantRepository;

final class RestaurantController extends Controller
{
    private function restaurants(): RestaurantRepository
    {
        return $this->make(RestaurantRepository::class);
    }

    public function dashboard(): Response
    {
        $orders = $this->make(OrderRepository::class);
        $id = $this->userId();
        $trend = $orders->trendByDay($id);

        return $this->view('manager/dashboard', [
            'restaurants' => $this->restaurants()->forOwner($id),
            'orderCount' => $orders->count(['owner_id' => $id]),
            'openCount' => $orders->count(['owner_id' => $id, 'statuses' => ['pending', 'preparing', 'ready']]),
            'revenue' => $orders->revenue($id),
            'trend' => $trend,
            'maxValue' => max(1, ...array_values(array_column($trend, 'value'))),
            'trendingToday' => $orders->trendingItems($id, date('Y-m-d 00:00:00')),
            'topWeek' => $orders->trendingItems($id, array_key_first($trend) . ' 00:00:00'),
        ]);
    }

    private function input(): array
    {
        return $this->validate([
            'name' => 'required|max:150',
            'cuisine_type' => 'required|max:100',
            'address' => 'required|max:255',
            'phone' => 'required|phone',
            'description' => 'max:1000',
        ]);
    }

    public function create(): Response
    {
        return $this->view('manager/restaurant_form', ['restaurant' => null]);
    }

    public function store(): Response
    {
        $data = $this->input();
        if ($file = $this->request->file('image')) {
            $data['image_path'] = $this->images()->store($file, 'restaurants');
        }
        $data['owner_id'] = $this->userId();
        $this->restaurants()->create($data);

        return $this->redirect('/manager', 'Restaurant submitted. It will appear after administrator approval.');
    }

    public function edit(int $id): Response
    {
        return $this->view('manager/restaurant_form', ['restaurant' => $this->restaurants()->ownedOrFail($id, $this->userId())]);
    }

    public function update(int $id): Response
    {
        $restaurant = $this->restaurants()->ownedOrFail($id, $this->userId());
        $data = $this->input();
        if ($file = $this->request->file('image')) {
            $data['image_path'] = $this->images()->store($file, 'restaurants');
            $this->images()->delete($restaurant['image_path']);
        }
        // A rejected restaurant goes back to the review queue after it is edited
        if ($restaurant['status'] === 'rejected') {
            $data['status'] = 'pending';
            $data['rejection_reason'] = null;
        }
        $this->restaurants()->update($id, $data);

        return $this->redirect("/manager/restaurants/$id/edit", 'Restaurant updated.');
    }

    public function toggle(int $id): Response
    {
        $restaurant = $this->restaurants()->ownedOrFail($id, $this->userId());
        $open = $restaurant['is_open'] ? 0 : 1;
        $this->restaurants()->update($id, ['is_open' => $open]);

        return $this->back('/manager', $restaurant['name'] . ($open ? ' is now open for orders.' : ' is now closed.'));
    }

    public function destroy(int $id): Response
    {
        $restaurant = $this->restaurants()->ownedOrFail($id, $this->userId());
        $images = array_column($this->make(FoodRepository::class)->forManager($id), 'image_path');
        $this->restaurants()->delete($id);
        foreach (array_merge($images, [$restaurant['image_path']]) as $path) {
            $this->images()->delete($path);
        }

        return $this->redirect('/manager', 'Restaurant deleted.');
    }
}
