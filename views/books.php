<?php
/**
 * Books listing page
 */
include __DIR__ . '/layout/header.php';

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Get categories
$db = Database::getInstance();
$db->query("SELECT * FROM categories ORDER BY name");
$categories = $db->resultSet();

// Build query
$sql = "SELECT b.*, c.name as category_name FROM books b
        LEFT JOIN categories c ON b.category_id = c.id";

$params = [];
$whereClause = [];

if (!empty($search)) {
    $whereClause[] = "(b.title LIKE :search OR b.author LIKE :search)";
    $params[':search'] = "%{$search}%";
}

if ($categoryId > 0) {
    $whereClause[] = "b.category_id = :category_id";
    $params[':category_id'] = $categoryId;
}

if (!empty($whereClause)) {
    $sql .= " WHERE " . implode(' AND ', $whereClause);
}

$sql .= " ORDER BY b.uploaded_at DESC";

// Execute query
$db->query($sql);

// Bind parameters
foreach ($params as $param => $value) {
    $db->bind($param, $value);
}

$books = $db->resultSet();
?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <?php echo $localization->t('categories'); ?>
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo url('books'); ?>" class="list-group-item list-group-item-action <?php echo $categoryId === 0 ? 'active' : ''; ?>">
                    <?php echo $localization->t('all_categories'); ?>
                </a>
                <?php foreach ($categories as $category): ?>
                <a href="<?php echo url('books?category=' . $category['id']); ?>" class="list-group-item list-group-item-action <?php echo $categoryId === (int)$category['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo $localization->t('books'); ?></h5>

                <form action="<?php echo url('books'); ?>" method="get" class="d-flex" id="search-form">
                    <?php if ($categoryId > 0): ?>
                    <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                    <?php endif; ?>

                    <input type="text" class="form-control me-2" placeholder="<?php echo $localization->t('search_placeholder'); ?>" name="search" id="search-input" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <div class="card-body">
                <?php if (empty($books)): ?>
                <div class="alert alert-info">
                    <?php echo $localization->t('no_books_found'); ?>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($books as $book): ?>
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card book-card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-book fa-4x text-secondary"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($book['author']); ?>
                                    </small>
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-folder me-1"></i> <?php echo htmlspecialchars($book['category_name']); ?>
                                    </small>
                                </p>
                                <a href="<?php echo url('books/view/' . $book['id']); ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i> <?php echo $localization->t('view'); ?>
                                </a>
                                <?php if ($auth->isLoggedIn()): ?>
                                <a href="<?php echo url('uploads/books/' . $book['file_path']); ?>" class="btn btn-sm btn-success" download>
                                    <i class="fas fa-download me-1"></i> <?php echo $localization->t('download'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer text-muted">
                                <small>
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M d, Y', strtotime($book['uploaded_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
