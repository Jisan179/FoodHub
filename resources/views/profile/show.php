<?php $title = 'My profile'; ?>
<div class="page-head"><div><h1>My profile</h1><p>@<?= e($currentUser['username']) ?> · <?= e(status_label($currentUser['role'])) ?></p></div></div>
<div class="split">
    <section class="card">
        <h2>Details</h2>
        <form method="POST" action="<?= $this->url('/profile') ?>">
            <?= $this->csrf() ?>
            <div class="form-grid">
                <div class="field"><label for="name">Full name</label><input id="name" name="name" value="<?= $this->old('name', $currentUser['name']) ?>" maxlength="100" required></div>
                <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="<?= $this->old('email', $currentUser['email']) ?>" required></div>
                <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" value="<?= $this->old('phone', $currentUser['phone']) ?>" required></div>
                <div class="field"><label for="address">Address</label><input id="address" name="address" value="<?= $this->old('address', $currentUser['address']) ?>" maxlength="255" required></div>
                <div class="field full"><label for="profile_password">Current password (only needed to change email)</label><input id="profile_password" name="current_password" type="password" autocomplete="current-password"></div>
            </div>
            <button class="btn" type="submit">Save changes</button>
        </form>
    </section>
    <div class="stack">
        <section class="card">
            <h2>Change password</h2>
            <form method="POST" action="<?= $this->url('/profile/password') ?>">
                <?= $this->csrf() ?>
                <div class="field"><label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required></div>
                <div class="field"><label for="password">New password</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required></div>
                <div class="field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required></div>
                <button class="btn" type="submit">Change password</button>
            </form>
        </section>
        <section class="card">
            <h2>Close account</h2>
            <p class="small muted">Deactivation keeps your records. Deletion is permanent and is only possible without order history.</p>
            <details>
                <summary>Show options</summary>
                <form method="POST" action="<?= $this->url('/profile/deactivate') ?>" data-confirm="Deactivate your account?">
                    <?= $this->csrf() ?>
                    <div class="field"><label for="deactivate_password">Current password</label><input id="deactivate_password" name="current_password" type="password" required></div>
                    <button class="btn btn-secondary" type="submit">Deactivate account</button>
                </form>
                <form class="section" method="POST" action="<?= $this->url('/profile/delete') ?>" data-confirm="Permanently delete your account? This cannot be undone.">
                    <?= $this->csrf() ?>
                    <div class="field"><label for="delete_password">Current password</label><input id="delete_password" name="current_password" type="password" required></div>
                    <button class="btn btn-danger" type="submit">Delete account</button>
                </form>
            </details>
        </section>
    </div>
</div>
