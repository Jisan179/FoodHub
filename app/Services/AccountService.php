<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DomainError;
use App\Models\UserRepository;

final class AccountService extends Service
{
    private function users(): UserRepository
    {
        return $this->app->make(UserRepository::class);
    }

    /**
     * Public sign-up always creates an active customer, whatever the request contains.
     * Manager and rider accounts are created only by an administrator.
     */
    public function registerCustomer(array $data, string $password): array
    {
        $data['role'] = 'customer';
        $data['status'] = 'active';
        $id = $this->users()->create($data, $password);

        return $this->users()->find($id);
    }

    private function assertNotLastAdmin(array $user): void
    {
        if ($user['role'] === 'admin' && $user['status'] === 'active' && $this->users()->activeAdminCount() <= 1) {
            throw new DomainError('At least one active administrator is required.');
        }
    }

    public function delete(array $user): void
    {
        $this->assertNotLastAdmin($user);
        if ($this->users()->hasOrderHistory((int) $user['user_id'])) {
            throw new DomainError('This account has order history, so it cannot be deleted. Deactivate it instead.');
        }
        $this->users()->delete((int) $user['user_id']);
    }

    public function deactivate(array $user): void
    {
        $this->assertNotLastAdmin($user);
        $this->users()->update((int) $user['user_id'], ['status' => 'inactive']);
    }
}
