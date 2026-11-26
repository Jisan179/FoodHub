<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\DomainError;
use App\Core\HttpException;
use App\Core\Response;
use App\Core\ValidationError;
use App\Models\UserRepository;
use App\Services\AccountService;

final class UserController extends Controller
{
    private function users(): UserRepository
    {
        return $this->make(UserRepository::class);
    }

    private function findOrFail(int $id): array
    {
        $user = $this->users()->find($id);
        if (!$user) {
            throw new HttpException(404, 'User not found.');
        }

        return $user;
    }

    public function index(): Response
    {
        $filters = [
            'search' => mb_substr($this->request->string('search'), 0, 100),
            'role' => $this->request->string('role'),
            'status' => $this->request->string('status'),
        ];
        $page = $this->paginator($this->users()->count($filters));

        return $this->view('admin/users', ['users' => $this->users()->paginate($filters, $page), 'page' => $page, 'filters' => $filters]);
    }

    private function input(bool $creating): array
    {
        $rules = [
            'name' => 'required|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|phone',
            'address' => 'max:255',
            'role' => 'required|in:admin,customer,manager,rider',
            'status' => 'required|in:pending,active,inactive,suspended',
            'password' => ($creating ? 'required|' : '') . 'min:8|max:200',
        ];
        if ($creating) {
            $rules['username'] = 'required|username';
        }

        return $this->validate($rules);
    }

    public function create(): Response
    {
        return $this->view('admin/user_form', ['account' => null]);
    }

    public function store(): Response
    {
        $input = $this->input(true);
        if ($this->users()->usernameTaken($input['username'])) {
            throw new ValidationError(['username' => 'That username is already taken.']);
        }
        if ($this->users()->emailTaken($input['email'])) {
            throw new ValidationError(['email' => 'That email is already used.']);
        }
        $password = $input['password'];
        unset($input['password']);
        $this->users()->create($input, $password);

        return $this->redirect('/admin/users', 'User created.');
    }

    public function edit(int $id): Response
    {
        return $this->view('admin/user_form', ['account' => $this->findOrFail($id)]);
    }

    public function update(int $id): Response
    {
        $account = $this->findOrFail($id);
        $input = $this->input(false);
        if ($this->users()->emailTaken($input['email'], $id)) {
            throw new ValidationError(['email' => 'That email is already used.']);
        }
        if ($id === $this->userId() && ($input['role'] !== 'admin' || $input['status'] !== 'active')) {
            throw new DomainError('You cannot remove your own administrator access.');
        }
        $password = $input['password'];
        unset($input['password']);
        $this->users()->update($id, $input);
        if ($password !== '') {
            $this->users()->setPassword($id, $password);
            if ($id === $this->userId()) {
                $this->app->login($this->users()->find($id));
            }
        }

        return $this->redirect('/admin/users', 'User @' . $account['username'] . ' updated.');
    }

    public function approve(int $id): Response
    {
        $account = $this->findOrFail($id);
        if ($account['status'] !== 'pending') {
            throw new DomainError('This account is not waiting for approval.');
        }
        $this->users()->update($id, ['status' => 'active']);

        return $this->back('/admin/users', '@' . $account['username'] . ' approved.');
    }

    public function destroy(int $id): Response
    {
        if ($id === $this->userId()) {
            throw new DomainError('You cannot delete your own account here.');
        }
        $account = $this->findOrFail($id);
        $this->make(AccountService::class)->delete($account);

        return $this->back('/admin/users', '@' . $account['username'] . ' deleted.');
    }
}
