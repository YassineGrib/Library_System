<?php
/**
 * User Profile Page
 */
include __DIR__ . '/layout/header.php';

// Get user data
$user = $auth->getUser();

// Get user's books
$db = Database::getInstance();
$db->query("SELECT b.*, c.name as category_name 
            FROM books b 
            LEFT JOIN categories c ON b.category_id = c.id 
            WHERE b.uploaded_by = :user_id 
            ORDER BY b.uploaded_at DESC");
$db->bind(':user_id', $user['id']);
$books = $db->resultSet();
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $localization->t('profile_info'); ?></h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-placeholder bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-user fa-3x text-secondary"></i>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong><?php echo $localization->t('full_name'); ?>:</strong>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </div>
                
                <div class="mb-3">
                    <strong><?php echo $localization->t('email'); ?>:</strong>
                    <?php echo htmlspecialchars($user['email']); ?>
                </div>
                
                <div class="mb-3">
                    <strong><?php echo $localization->t('status'); ?>:</strong>
                    <?php if ($user['status'] === 'pending'): ?>
                    <span class="badge bg-warning"><?php echo $localization->t('pending'); ?></span>
                    <?php elseif ($user['status'] === 'approved'): ?>
                    <span class="badge bg-success"><?php echo $localization->t('approved'); ?></span>
                    <?php else: ?>
                    <span class="badge bg-danger"><?php echo $localization->t('rejected'); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <strong><?php echo $localization->t('member_since'); ?>:</strong>
                    <?php echo date('F d, Y', strtotime($user['created_at'])); ?>
                </div>
                
                <?php if ($user['receipt_path']): ?>
                <div class="mb-3">
                    <strong><?php echo $localization->t('receipt'); ?>:</strong>
                    <a href="<?php echo $baseUrl; ?>uploads/<?php echo $user['receipt_path']; ?>" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> <?php echo $localization->t('view'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo $localization->t('my_books'); ?></h5>
                
                <?php if ($user['status'] === 'approved'): ?>
                <a href="<?php echo $baseUrl; ?>admin/books" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> <?php echo $localization->t('add_book'); ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($user['status'] !== 'approved'): ?>
                <div class="alert alert-warning">
                    <?php echo $localization->t('account_pending_books'); ?>
                </div>
                <?php elseif (empty($books)): ?>
                <div class="alert alert-info">
                    <?php echo $localization->t('no_books_uploaded'); ?>
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
                                <th><?php echo $localization->t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($book['uploaded_at'])); ?></td>
                                <td>
                                    <a href="<?php echo $baseUrl; ?>books/view/<?php echo $book['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> <?php echo $localization->t('view'); ?>
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>uploads/books/<?php echo $book['file_path']; ?>" class="btn btn-sm btn-success" download>
                                        <i class="fas fa-download"></i> <?php echo $localization->t('download'); ?>
                                    </a>
                                </td>
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

<?php include __DIR__ . '/layout/footer.php'; ?>
