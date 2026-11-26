<?php $title = 'Create account'; $selected = $this->rawOld('role', 'customer'); ?>
<section class="card auth auth-wide">
    <h1>Create your account</h1>
    <p class="muted">Restaurant Manager and Rider accounts need administrator approval.</p>
    <form method="POST" action="<?= $this->url('/register') ?>">
        <?= $this->csrf() ?>
        <div class="form-grid">
            <div class="field"><label for="name">Full name</label><input id="name" name="name" value="<?= $this->old('name') ?>" autocomplete="name" maxlength="100" required><?= $this->error('name') ?></div>
            <div class="field"><label for="username">Username</label><input id="username" name="username" value="<?= $this->old('username') ?>" autocomplete="username" maxlength="30" required><?= $this->error('username') ?></div>
            <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="<?= $this->old('email') ?>" autocomplete="email" required><?= $this->error('email') ?></div>
            <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" value="<?= $this->old('phone') ?>" autocomplete="tel" required><?= $this->error('phone') ?></div>
            <div class="field full"><label for="address">Address</label><input id="address" name="address" value="<?= $this->old('address') ?>" autocomplete="street-address" maxlength="255" required><?= $this->error('address') ?></div>
            <div class="field full">
                <label for="role">I am a</label>
                <select id="role" name="role">
                    <?php foreach (['customer', 'manager', 'rider'] as $role): ?>
                        <option value="<?= $role ?>"<?= $selected === $role ? ' selected' : '' ?>><?= e(status_label($role)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="password">Password (8 or more characters)</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required><?= $this->error('password') ?></div>
            <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required><?= $this->error('password_confirmation') ?></div>
        </div>
        <button class="btn btn-block" type="submit">Create account</button>
    </form>
    <p class="small muted section">By creating an account you agree to the <a href="<?= $this->url('/terms') ?>">Terms of use</a> and <a href="<?= $this->url('/privacy') ?>">Privacy policy</a>.</p>
    <p class="small">Already registered? <a href="<?= $this->url('/login') ?>">Sign in</a></p>
</section>
