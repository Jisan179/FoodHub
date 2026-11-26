<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\DomainError;
use App\Core\RateLimiter;
use App\Core\Response;
use App\Core\ValidationError;
use App\Models\UserRepository;
use App\Services\AccountService;
use App\Services\PasswordResetService;

final class AuthController extends Controller
{
    private const WINDOW = 900; // 15 minutes

    private function limiter(): RateLimiter
    {
        return new RateLimiter($this->app->db());
    }

    public function showLogin(): Response
    {
        return $this->view('auth/login');
    }

    public function login(): Response
    {
        $input = $this->validate(['login' => 'required|max:150', 'password' => 'required|max:200'], ['login' => 'Username or email']);
        $limiter = $this->limiter();
        $accountKey = 'login:' . mb_strtolower($input['login']) . '|' . $this->request->ip();
        $addressKey = 'login-ip:' . $this->request->ip();
        if ($limiter->tooMany($accountKey, 5, self::WINDOW) || $limiter->tooMany($addressKey, 30, self::WINDOW)) {
            throw new DomainError('Too many sign-in attempts. Please wait 15 minutes and try again.');
        }

        $user = $this->make(UserRepository::class)->findByLogin($input['login']);
        if (!$user || !password_verify($input['password'], $user['password_hash'])) {
            $limiter->hit($accountKey);
            $limiter->hit($addressKey);
            throw new ValidationError(['login' => 'Invalid username/email or password.']);
        }
        // The account state is revealed only after the password has been proven
        if ($user['status'] !== 'active') {
            throw new DomainError($user['status'] === 'pending'
                ? 'Your account is waiting for administrator approval.'
                : 'Your account is ' . $user['status'] . '. Please contact an administrator.');
        }

        $limiter->clear($accountKey);
        $this->app->login($user);

        return $this->redirect(App::homeFor($user['role']));
    }

    public function logout(): Response
    {
        $this->app->logout();

        return $this->redirect('/login', 'You have been signed out.');
    }

    public function showRegister(): Response
    {
        return $this->view('auth/register');
    }

    public function register(): Response
    {
        $input = $this->validate([
            'name' => 'required|max:100',
            'username' => 'required|username',
            'email' => 'required|email|max:150',
            'phone' => 'required|phone',
            'address' => 'required|max:255',
            'password' => 'required|min:8|max:200',
            'password_confirmation' => 'required|same:password',
        ]);
        $users = $this->make(UserRepository::class);
        if ($users->usernameTaken($input['username'])) {
            throw new ValidationError(['username' => 'That username is already taken.']);
        }
        if ($users->emailTaken($input['email'])) {
            throw new ValidationError(['email' => 'An account with that email already exists.']);
        }
        $password = $input['password'];
        unset($input['password'], $input['password_confirmation']);
        $this->make(AccountService::class)->registerCustomer($input, $password);

        return $this->redirect('/login', 'Account created. You can sign in now.');
    }

    public function showForgot(): Response
    {
        return $this->view('auth/forgot');
    }

    public function sendReset(): Response
    {
        $input = $this->validate(['email' => 'required|email|max:150']);
        $limiter = $this->limiter();
        $key = 'reset:' . $this->request->ip();
        if ($limiter->tooMany($key, 5, 3600)) {
            throw new DomainError('Too many reset requests. Please try again later.');
        }
        $limiter->hit($key);
        $this->make(PasswordResetService::class)->request($input['email']);

        return $this->redirect('/login', 'If that email belongs to an account, a reset link has been sent.');
    }

    public function showReset(string $token): Response
    {
        if (!$this->make(PasswordResetService::class)->find($token)) {
            $this->app->session->flash('error', 'This reset link is invalid or has expired. Request a new one.');

            return $this->redirect('/forgot-password');
        }

        return $this->view('auth/reset', ['token' => $token]);
    }

    public function reset(string $token): Response
    {
        $input = $this->validate(['password' => 'required|min:8|max:200', 'password_confirmation' => 'required|same:password']);
        $this->make(PasswordResetService::class)->reset($token, $input['password']);

        return $this->redirect('/login', 'Your password has been changed. Please sign in.');
    }
}
