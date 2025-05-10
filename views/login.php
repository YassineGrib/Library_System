<?php
/**
 * Login page
 */
include __DIR__ . '/layout/header.php';
?>

<div class="auth-form">
    <h2 class="form-title"><?php echo $localization->t('login_title'); ?></h2>

    <form action="<?php echo $baseUrl; ?>login" method="post" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label"><?php echo $localization->t('email'); ?></label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="invalid-feedback">
                <?php echo $localization->t('email_invalid'); ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label"><?php echo $localization->t('password'); ?></label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="invalid-feedback">
                <?php echo $localization->t('required'); ?>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember"><?php echo $localization->t('remember_me'); ?></label>
        </div>

        <button type="submit" class="btn btn-primary w-100"><?php echo $localization->t('login'); ?></button>
    </form>

    <div class="mt-3 text-center">
        <p><?php echo $localization->t('dont_have_account'); ?> <a href="<?php echo $baseUrl; ?>register"><?php echo $localization->t('register'); ?></a></p>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
