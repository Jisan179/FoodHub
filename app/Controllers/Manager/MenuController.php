<?php

declare(strict_types=1);

namespace App\Controllers\Manager;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\FoodRepository;
use App\Models\RestaurantRepository;

final class MenuController extends Controller
{
    private function food(): FoodRepository
    {
        return $this->make(FoodRepository::class);
    }

    private function restaurant(int $id): array
    {
        return $this->make(RestaurantRepository::class)->ownedOrFail($id, $this->userId());
    }

    private function input(): array
    {
        $data = $this->validate([
            'name' => 'required|max:150',
            'category' => 'required|max:50',
            'price' => 'required|price',
            'description' => 'max:1000',
        ]);
        $data['is_available'] = $this->request->string('is_available') === '1' ? 1 : 0;

        return $data;
    }

    public function index(int $id): Response
    {
        return $this->view('manager/menu', ['restaurant' => $this->restaurant($id), 'items' => $this->food()->forManager($id)]);
    }

    public function store(int $id): Response
    {
        $this->restaurant($id);
        $data = $this->input();
        if ($file = $this->request->file('image')) {
            $data['image_path'] = $this->images()->store($file, 'food');
        }
        $data['restaurant_id'] = $id;
        $this->food()->create($data);

        return $this->redirect("/manager/restaurants/$id/menu", 'Menu item added.');
    }

    public function edit(int $id): Response
    {
        $item = $this->food()->ownedOrFail($id, $this->userId());

        return $this->view('manager/item_form', ['item' => $item, 'restaurant' => $this->restaurant((int) $item['restaurant_id'])]);
    }

    public function update(int $id): Response
    {
        $item = $this->food()->ownedOrFail($id, $this->userId());
        $data = $this->input();
        if ($file = $this->request->file('image')) {
            $data['image_path'] = $this->images()->store($file, 'food');
            $this->images()->delete($item['image_path']);
        }
        $this->food()->update($id, $data);

        return $this->redirect('/manager/restaurants/' . $item['restaurant_id'] . '/menu', 'Menu item updated.');
    }

    public function destroy(int $id): Response
    {
        $item = $this->food()->ownedOrFail($id, $this->userId());
        $this->food()->softDelete($id);

        return $this->redirect('/manager/restaurants/' . $item['restaurant_id'] . '/menu', 'Menu item deleted.');
    }
}
