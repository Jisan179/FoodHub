<?php $title = 'Sign in'; ?>
<section class="card auth">
    <h1>Welcome back</h1>
    <p class="muted">Sign in to your FoodHub account.</p>
    <form method="POST" action="<?= $this->url('/login') ?>">
        <?= $this->csrf() ?>
        <div class="field">
            <label for="login">Username or email</label>
            <input id="login" name="login" value="<?= $this->old('login') ?>" autocomplete="username" required autofocus>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-block btn-lg" type="submit">Sign in</button>
    </form>
    <p class="row-between small section">
        <a href="<?= $this->url('/forgot-password') ?>">Forgot your password?</a>
        <a href="<?= $this->url('/register') ?>">Create an account</a>
    </p>
    <p class="trust"><span aria-hidden="true">🔒</span><span>Passwords are stored as hashes and sign-in attempts are limited. <a href="<?= $this->url('/security') ?>">How we protect accounts</a></span></p>
</section>
