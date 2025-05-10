<?php
/**
 * Admin Dashboard
 */
include __DIR__ . '/../views/layout/header.php';

// Get statistics
$db = Database::getInstance();

// Total users
$db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$totalUsers = $db->single()['count'];

// Pending users
$db->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
$pendingUsers = $db->single()['count'];

// Total books
$db->query("SELECT COUNT(*) as count FROM books");
$totalBooks = $db->single()['count'];

// Recent users
$db->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
$recentUsers = $db->resultSet();

// Recent books
$db->query("SELECT b.*, c.name as category_name FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            ORDER BY b.uploaded_at DESC LIMIT 5");
$recentBooks = $db->resultSet();
?>

<h1 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i><?php echo $localization->t('admin_dashboard'); ?></h1>

<div class="row dashboard-stats mb-4">
    <div class="col-md-4">
        <div class="card card-users">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="card-title"><?php echo $localization->t('total_users'); ?></div>
                        <div class="card-text"><?php echo $totalUsers; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-books">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="card-title"><?php echo $localization->t('total_books'); ?></div>
                        <div class="card-text"><?php echo $totalBooks; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-pending">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="card-title"><?php echo $localization->t('pending_users'); ?></div>
                        <div class="card-text"><?php echo $pendingUsers; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-clock card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo $localization->t('recent_users'); ?></h5>
                <a href="<?php echo url('admin/users'); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i> <?php echo $localization->t('view_all'); ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentUsers)): ?>
                <div class="alert alert-info">
                    <?php echo $localization->t('no_users_found'); ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo $localization->t('full_name'); ?></th>
                                <th><?php echo $localization->t('email'); ?></th>
                                <th><?php echo $localization->t('status'); ?></th>
                                <th><?php echo $localization->t('created_at'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['status'] === 'pending'): ?>
                                    <span class="badge bg-warning"><?php echo $localization->t('pending'); ?></span>
                                    <?php elseif ($user['status'] === 'approved'): ?>
                                    <span class="badge bg-success"><?php echo $localization->t('approved'); ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-danger"><?php echo $localization->t('rejected'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo $localization->t('recent_books'); ?></h5>
                <a href="<?php echo url('admin/books'); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i> <?php echo $localization->t('view_all'); ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentBooks)): ?>
                <div class="alert alert-info">
                    <?php echo $localization->t('no_books_found'); ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo $localization->t('title'); ?></th>
                                <th><?php echo $localization->t('author'); ?></th>
                                <th><?php echo $localization->t('category'); ?></th>
                                <th><?php echo $localization->t('uploaded_at'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBooks as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($book['uploaded_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/layout/footer.php'; ?>
