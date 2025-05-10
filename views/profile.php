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

                <?php if (isset($user['subscription_end'])): ?>
                <div class="mb-3">
                    <strong><?php echo $localization->t('subscription_end'); ?>:</strong>
                    <?php echo date('F d, Y', strtotime($user['subscription_end'])); ?>

                    <?php if ($auth->hasActiveSubscription()): ?>
                    <span class="badge bg-success"><?php echo $localization->t('subscription_active'); ?></span>
                    <?php else: ?>
                    <span class="badge bg-danger"><?php echo $localization->t('subscription_expired'); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($user['upload_limit'])): ?>
                <div class="mb-3">
                    <strong><?php echo $localization->t('upload_limit'); ?>:</strong>
                    <?php echo $user['upload_limit']; ?>
                </div>

                <div class="mb-3">
                    <strong><?php echo $localization->t('remaining_uploads'); ?>:</strong>
                    <?php echo $auth->getRemainingUploads(); ?>
                </div>
                <?php endif; ?>

                <?php if ($user['receipt_path']): ?>
                <div class="mb-3">
                    <strong><?php echo $localization->t('receipt'); ?>:</strong>
                    <a href="<?php echo url('uploads/' . $user['receipt_path']); ?>" target="_blank" class="btn btn-sm btn-info">
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

                <?php if ($auth->canUploadBooks()): ?>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadBookModal">
                    <i class="fas fa-plus"></i> <?php echo $localization->t('add_book'); ?>
                </button>
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
                                    <a href="<?php echo url('books/view/' . $book['id']); ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> <?php echo $localization->t('view'); ?>
                                    </a>
                                    <a href="<?php echo url('uploads/books/' . $book['file_path']); ?>" class="btn btn-sm btn-success" download>
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

<!-- Upload Book Modal -->
<?php if ($auth->canUploadBooks()): ?>
<div class="modal fade" id="uploadBookModal" tabindex="-1" aria-labelledby="uploadBookModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadBookModalLabel"><i class="fas fa-upload me-1"></i> <?php echo $localization->t('upload_book'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo url('books/upload'); ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label"><?php echo $localization->t('title'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        <div class="invalid-feedback">
                            <?php echo $localization->t('required'); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="author" class="form-label"><?php echo $localization->t('author'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="author" name="author" required>
                        <div class="invalid-feedback">
                            <?php echo $localization->t('required'); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label"><?php echo $localization->t('category'); ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value=""><?php echo $localization->t('select_category'); ?></option>
                            <?php
                            $db = Database::getInstance();
                            $db->query("SELECT * FROM categories ORDER BY name");
                            $categories = $db->resultSet();

                            foreach ($categories as $category):
                            ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            <?php echo $localization->t('required'); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="book_file" class="form-label"><?php echo $localization->t('book_file'); ?> <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="book_file" name="book_file" accept=".pdf" required>
                        <div class="invalid-feedback">
                            <?php echo $localization->t('required'); ?>
                        </div>
                        <small class="form-text text-muted">
                            <?php echo $localization->t('file_type'); ?>: PDF
                        </small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> <?php echo $localization->t('upload'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>
