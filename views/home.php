<?php
/**
 * Home page
 */
include __DIR__ . '/layout/header.php';

// Get latest books
$db = Database::getInstance();
$db->query("SELECT b.*, c.name as category_name FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            ORDER BY b.uploaded_at DESC LIMIT 6");
$latestBooks = $db->resultSet();
?>

<div class="jumbotron bg-light p-5 rounded">
    <h1 class="display-4"><?php echo $localization->t('welcome'); ?></h1>
    <p class="lead"><?php echo $localization->t('home_description'); ?></p>

    <form action="<?php echo url('books'); ?>" method="get" class="mt-4" id="search-form">
        <div class="input-group mb-3">
            <input type="text" class="form-control form-control-lg" placeholder="<?php echo $localization->t('search_placeholder'); ?>" name="search" id="search-input">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i> <?php echo $localization->t('search'); ?>
            </button>
        </div>
    </form>
</div>

<div class="my-5">
    <h2 class="mb-4"><?php echo $localization->t('latest_books'); ?></h2>

    <div class="row">
        <?php if (empty($latestBooks)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <?php echo $localization->t('no_books_found'); ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($latestBooks as $book): ?>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card book-card h-100 shadow-sm border-0 transition-hover">
                        <div class="position-relative">
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light py-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <div class="book-cover-container">
                                    <div class="book-cover">
                                        <i class="fas fa-book fa-4x" style="color: #6c757d;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="category-badge position-absolute top-0 end-0 m-2">
                                <span class="badge bg-primary rounded-pill">
                                    <i class="fas fa-folder me-1"></i> <?php echo htmlspecialchars($book['category_name']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text text-muted mb-3">
                                <i class="fas fa-user-edit me-1"></i> <?php echo htmlspecialchars($book['author']); ?>
                            </p>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="<?php echo url('books/view/' . $book['id']); ?>" class="btn btn-sm btn-primary rounded-pill">
                                    <i class="fas fa-eye me-1"></i> <?php echo $localization->t('view'); ?>
                                </a>
                                <?php if ($auth->isLoggedIn()): ?>
                                    <a href="<?php echo url('books/read/' . $book['id']); ?>" class="btn btn-sm btn-info rounded-pill">
                                        <i class="fas fa-book-reader me-1"></i> <?php echo $localization->t('read_online'); ?>
                                    </a>
                                    <a href="<?php echo url('public/uploads/books/' . $book['file_path']); ?>" class="btn btn-sm btn-success rounded-pill" download>
                                        <i class="fas fa-download me-1"></i> <?php echo $localization->t('download'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 text-muted">
                            <small>
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo date('M d, Y', strtotime($book['uploaded_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="text-center mt-4">
        <a href="<?php echo url('books'); ?>" class="btn btn-outline-primary">
            <?php echo $localization->t('view_all_books'); ?>
        </a>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
