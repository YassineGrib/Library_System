<?php
/**
 * Header template
 */
$isRtl = $localization->isRtl();
$direction = $localization->getDirection();

// Make sure $auth is defined
if (!isset($auth)) {
    $auth = Auth::getInstance();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $localization->getCurrentLanguage(); ?>" dir="<?php echo $direction; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $localization->t('app_name'); ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo url('assets/img/favicon.ico'); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo url('assets/img/favicon.ico'); ?>" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?php echo url('assets/img/apple-touch-icon.png'); ?>">
    <meta name="theme-color" content="#343a40">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/app.css'); ?>">

    <?php if ($isRtl): ?>
    <!-- RTL Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <?php endif; ?>
</head>
<body dir="<?php echo $direction; ?>">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo url(); ?>">
                <?php echo $localization->t('app_name'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url(); ?>">
                            <i class="fas fa-home me-1"></i> <?php echo $localization->t('home'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('books'); ?>">
                            <i class="fas fa-book me-1"></i> <?php echo $localization->t('books'); ?>
                        </a>
                    </li>
                    <?php if ($auth->isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-shield me-1"></i> <?php echo $localization->t('admin'); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo url('admin'); ?>">
                                    <i class="fas fa-tachometer-alt me-1"></i> <?php echo $localization->t('dashboard'); ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo url('admin/users'); ?>">
                                    <i class="fas fa-users-cog me-1"></i> <?php echo $localization->t('manage_users'); ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo url('admin/books'); ?>">
                                    <i class="fas fa-book-open me-1"></i> <?php echo $localization->t('manage_books'); ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <!-- Language Switcher -->
                    <li class="nav-item dropdown language-switcher">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i> <?php echo $localization->t('language'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown" id="language-switcher">
                            <li>
                                <a class="dropdown-item" href="?lang=en" data-lang="en">
                                    <img src="https://flagcdn.com/w20/us.png" alt="English"> English
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="?lang=fr" data-lang="fr">
                                    <img src="https://flagcdn.com/w20/fr.png" alt="Français"> Français
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="?lang=ar" data-lang="ar">
                                    <img src="https://flagcdn.com/w20/sa.png" alt="العربية"> العربية
                                </a>
                            </li>
                        </ul>
                    </li>

                    <?php if ($auth->isLoggedIn()): ?>
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo $auth->getUser()['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo url('profile'); ?>">
                                    <i class="fas fa-id-card me-1"></i> <?php echo $localization->t('profile'); ?>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo url('logout'); ?>">
                                    <i class="fas fa-sign-out-alt me-1"></i> <?php echo $localization->t('logout'); ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <!-- Login/Register -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('login'); ?>">
                            <i class="fas fa-sign-in-alt me-1"></i> <?php echo $localization->t('login'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('register'); ?>">
                            <i class="fas fa-user-plus me-1"></i> <?php echo $localization->t('register'); ?>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content py-4">
        <div class="container">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
