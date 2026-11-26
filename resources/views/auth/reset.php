<?php $title = 'Choose a new password'; ?>
<section class="card auth">
    <h1>Choose a new password</h1>
    <form method="POST" action="<?= $this->url('/reset-password/' . $token) ?>">
        <?= $this->csrf() ?>
        <div class="field"><label for="password">New password</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required autofocus><?= $this->error('password') ?></div>
        <div class="field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required><?= $this->error('password_confirmation') ?></div>
        <button class="btn btn-block" type="submit">Change password</button>
    </form>
    <p class="small section"><a href="<?= $this->url('/login') ?>">Back to sign in</a></p>
</section>
