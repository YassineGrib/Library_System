<?php
/**
 * Admin Users Management
 */
include __DIR__ . '/../views/layout/header.php';

// Get filter
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$db = Database::getInstance();
$sql = "SELECT * FROM users WHERE role = 'user'";

if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
    $sql .= " AND status = :status";
}

$sql .= " ORDER BY created_at DESC";

$db->query($sql);

if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
    $db->bind(':status', $status);
}

$users = $db->resultSet();

// Count users by status
$db->query("SELECT status, COUNT(*) as count FROM users WHERE role = 'user' GROUP BY status");
$statusCounts = [];
foreach ($db->resultSet() as $row) {
    $statusCounts[$row['status']] = $row['count'];
}

$pendingCount = $statusCounts['pending'] ?? 0;
$approvedCount = $statusCounts['approved'] ?? 0;
$rejectedCount = $statusCounts['rejected'] ?? 0;
$totalCount = $pendingCount + $approvedCount + $rejectedCount;
?>

<h1 class="mb-4"><i class="fas fa-users-cog me-2"></i><?php echo $localization->t('manage_users'); ?></h1>

<div class="card mb-4">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'all' ? 'active' : ''; ?>" href="<?php echo url('admin/users'); ?>">
                    <i class="fas fa-users me-1"></i> <?php echo $localization->t('all'); ?> <span class="badge bg-secondary"><?php echo $totalCount; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'pending' ? 'active' : ''; ?>" href="<?php echo url('admin/users?status=pending'); ?>">
                    <i class="fas fa-user-clock me-1"></i> <?php echo $localization->t('pending'); ?> <span class="badge bg-warning"><?php echo $pendingCount; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'approved' ? 'active' : ''; ?>" href="<?php echo url('admin/users?status=approved'); ?>">
                    <i class="fas fa-user-check me-1"></i> <?php echo $localization->t('approved'); ?> <span class="badge bg-success"><?php echo $approvedCount; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'rejected' ? 'active' : ''; ?>" href="<?php echo url('admin/users?status=rejected'); ?>">
                    <i class="fas fa-user-times me-1"></i> <?php echo $localization->t('rejected'); ?> <span class="badge bg-danger"><?php echo $rejectedCount; ?></span>
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <?php if (empty($users)): ?>
        <div class="alert alert-info">
            <?php echo $localization->t('no_users_found'); ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo $localization->t('id'); ?></th>
                        <th><?php echo $localization->t('full_name'); ?></th>
                        <th><?php echo $localization->t('email'); ?></th>
                        <th><?php echo $localization->t('status'); ?></th>
                        <th><?php echo $localization->t('receipt'); ?></th>
                        <th><?php echo $localization->t('created_at'); ?></th>
                        <th><?php echo $localization->t('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
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
                        <td>
                            <?php if ($user['receipt_path']): ?>
                            <a href="<?php echo url('uploads/' . $user['receipt_path']); ?>" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> <?php echo $localization->t('view'); ?>
                            </a>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?php echo $localization->t('not_uploaded'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['status'] === 'pending'): ?>
                            <form action="<?php echo url('admin/users/update-status'); ?>" method="post" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i> <?php echo $localization->t('approve'); ?>
                                </button>
                            </form>

                            <form action="<?php echo url('admin/users/update-status'); ?>" method="post" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-times"></i> <?php echo $localization->t('reject'); ?>
                                </button>
                            </form>
                            <?php elseif ($user['status'] === 'approved'): ?>
                            <form action="<?php echo url('admin/users/update-status'); ?>" method="post" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-times"></i> <?php echo $localization->t('reject'); ?>
                                </button>
                            </form>
                            <?php else: ?>
                            <form action="<?php echo url('admin/users/update-status'); ?>" method="post" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i> <?php echo $localization->t('approve'); ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../views/layout/footer.php'; ?>
