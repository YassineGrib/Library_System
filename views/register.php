<?php
/**
 * Registration page
 */
include __DIR__ . '/layout/header.php';
?>

<div class="auth-form">
    <h2 class="form-title"><?php echo $localization->t('register_title'); ?></h2>

    <form action="<?php echo $baseUrl; ?>register" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
        <div class="mb-3">
            <label for="full_name" class="form-label"><?php echo $localization->t('full_name'); ?></label>
            <input type="text" class="form-control" id="full_name" name="full_name" required>
            <div class="invalid-feedback">
                <?php echo $localization->t('required'); ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label"><?php echo $localization->t('email'); ?></label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="invalid-feedback">
                <?php echo $localization->t('email_invalid'); ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label"><?php echo $localization->t('password'); ?></label>
            <input type="password" class="form-control" id="password" name="password" required minlength="6">
            <div class="invalid-feedback">
                <?php echo $localization->t('password_min'); ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label"><?php echo $localization->t('confirm_password'); ?></label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            <div class="invalid-feedback">
                <?php echo $localization->t('password_match'); ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="receipt" class="form-label"><?php echo $localization->t('upload_receipt'); ?></label>
            <input type="file" class="form-control custom-file-input" id="receipt" name="receipt" accept="image/*,application/pdf" required>
            <div class="form-text"><?php echo $localization->t('receipt_help'); ?></div>
            <div class="invalid-feedback">
                <?php echo $localization->t('required'); ?>
            </div>
            <img id="receipt-preview" class="mt-2 img-thumbnail d-none" style="max-height: 200px;">
        </div>

        <button type="submit" class="btn btn-primary w-100"><?php echo $localization->t('register'); ?></button>
    </form>

    <div class="mt-3 text-center">
        <p><?php echo $localization->t('already_have_account'); ?> <a href="<?php echo $baseUrl; ?>login"><?php echo $localization->t('login'); ?></a></p>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
