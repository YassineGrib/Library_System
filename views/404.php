<?php
/**
 * 404 Not Found page
 */
include __DIR__ . '/layout/header.php';
?>

<div class="text-center py-5">
    <h1 class="display-1">404</h1>
    <h2><?php echo $localization->t('error_404'); ?></h2>
    <p class="lead"><?php echo $localization->t('page_not_found'); ?></p>
    <a href="<?php echo $baseUrl; ?>" class="btn btn-primary">
        <i class="fas fa-home me-1"></i> <?php echo $localization->t('go_home'); ?>
    </a>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
