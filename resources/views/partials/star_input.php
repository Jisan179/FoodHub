<?php
// Clickable star rating built from radio buttons. $id must be unique on the page.
// Stars are listed from 5 to 1 and displayed in reverse, so CSS can light up every star below the chosen one.
$words = [5 => 'Excellent', 4 => 'Good', 3 => 'Okay', 2 => 'Poor', 1 => 'Bad'];
?>
<fieldset class="star-field">
    <legend class="label">Your rating</legend>
    <div class="star-input">
        <?php foreach ($words as $value => $word): ?>
            <input type="radio" id="<?= e($id . '-' . $value) ?>" name="rating" value="<?= $value ?>"<?= (int) ($selected ?? 0) === $value ? ' checked' : '' ?> required>
            <label for="<?= e($id . '-' . $value) ?>" title="<?= e($word) ?>"><span class="sr-only"><?= $value ?> star<?= $value > 1 ? 's' : '' ?>, <?= e($word) ?></span>★</label>
        <?php endforeach; ?>
    </div>
</fieldset>
