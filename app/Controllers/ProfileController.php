<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\ValidationError;
use App\Models\UserRepository;
use App\Services\AccountService;

final class ProfileController extends Controller
{
    private function users(): UserRepository
    {
        return $this->make(UserRepository::class);
    }

    private function confirmPassword(string $password): array
    {
        $user = $this->users()->find($this->userId());
        if (!password_verify($password, $user['password_hash'])) {
            throw new ValidationError(['current_password' => 'Your current password is incorrect.']);
        }

        return $user;
    }

    public function show(): Response
    {
        return $this->view('profile/show');
    }

    public function update(): Response
    {
        $input = $this->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|phone',
            'address' => 'required|max:255',
        ]);
        if ($this->users()->emailTaken($input['email'], $this->userId())) {
            throw new ValidationError(['email' => 'That email is used by another account.']);
        }
        // Changing the email changes where reset links go, so it needs the password
        if (strcasecmp($input['email'], $this->user()['email']) !== 0) {
            $this->confirmPassword($this->request->secret('current_password'));
        }
        $this->users()->update($this->userId(), $input);

        return $this->redirect('/profile', 'Profile updated.');
    }

    public function password(): Response
    {
        $input = $this->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|max:200',
            'password_confirmation' => 'required|same:password',
        ], ['password' => 'New password']);
        $this->confirmPassword($input['current_password']);
        $this->users()->setPassword($this->userId(), $input['password']);
        // Keep this session signed in; every other session is signed out
        $this->app->login($this->users()->find($this->userId()));

        return $this->redirect('/profile', 'Password changed.');
    }

    public function deactivate(): Response
    {
        $user = $this->confirmPassword($this->request->secret('current_password'));
        $this->make(AccountService::class)->deactivate($user);
        $this->app->logout();

        return $this->redirect('/login', 'Your account has been deactivated.');
    }

    public function destroy(): Response
    {
        $user = $this->confirmPassword($this->request->secret('current_password'));
        $this->make(AccountService::class)->delete($user);
        $this->app->logout();

        return $this->redirect('/login', 'Your account has been deleted.');
    }
}
