<?php $title = 'Reset password'; ?>
<section class="card auth">
    <h1>Reset your password</h1>
    <p class="muted">Enter your account email. We will send a link that works once and expires in 30 minutes.</p>
    <form method="POST" action="<?= $this->url('/forgot-password') ?>">
        <?= $this->csrf() ?>
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" required autofocus></div>
        <button class="btn btn-block" type="submit">Send reset link</button>
    </form>
    <p class="small section"><a href="<?= $this->url('/login') ?>">Back to sign in</a></p>
</section>
