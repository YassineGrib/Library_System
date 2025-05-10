<?php
/**
 * Book View Page
 */
include __DIR__ . '/layout/header.php';

// Get book ID
$bookId = isset($id) ? (int)$id : 0;

// Get book details
$db = Database::getInstance();
$db->query("SELECT b.*, c.name as category_name, u.full_name as uploader_name
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            LEFT JOIN users u ON b.uploaded_by = u.id
            WHERE b.id = :id");
$db->bind(':id', $bookId);
$book = $db->single();

// Check if book exists
if (!$book) {
    $_SESSION['error'] = $localization->t('book_not_found');
    redirect('books');
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="mb-0"><?php echo htmlspecialchars($book['title']); ?></h2>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center bg-light p-5 mb-3">
                            <i class="fas fa-book fa-5x text-secondary"></i>
                        </div>

                        <?php if ($auth->isLoggedIn()): ?>
                        <a href="<?php echo url('uploads/books/' . $book['file_path']); ?>" class="btn btn-success w-100 mb-2" download>
                            <i class="fas fa-download me-1"></i> <?php echo $localization->t('download'); ?>
                        </a>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <?php echo $localization->t('login_to_download'); ?>
                            <a href="<?php echo url('login'); ?>" class="alert-link"><?php echo $localization->t('login'); ?></a>
                        </div>
                        <?php endif; ?>

                        <a href="<?php echo url('books'); ?>" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-1"></i> <?php echo $localization->t('back_to_books'); ?>
                        </a>
                    </div>

                    <div class="col-md-8">
                        <h4><?php echo $localization->t('book_details'); ?></h4>
                        <hr>

                        <div class="mb-3">
                            <strong><?php echo $localization->t('title'); ?>:</strong>
                            <?php echo htmlspecialchars($book['title']); ?>
                        </div>

                        <div class="mb-3">
                            <strong><?php echo $localization->t('author'); ?>:</strong>
                            <?php echo htmlspecialchars($book['author']); ?>
                        </div>

                        <div class="mb-3">
                            <strong><?php echo $localization->t('category'); ?>:</strong>
                            <?php echo htmlspecialchars($book['category_name']); ?>
                        </div>

                        <div class="mb-3">
                            <strong><?php echo $localization->t('uploaded_by'); ?>:</strong>
                            <?php echo htmlspecialchars($book['uploader_name']); ?>
                        </div>

                        <div class="mb-3">
                            <strong><?php echo $localization->t('uploaded_at'); ?>:</strong>
                            <?php echo date('F d, Y', strtotime($book['uploaded_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
