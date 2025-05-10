<?php
/**
 * Admin Books Management
 */
include __DIR__ . '/../views/layout/header.php';

// Get categories
$db = Database::getInstance();
$db->query("SELECT * FROM categories ORDER BY name");
$categories = $db->resultSet();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $author = filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING);
    $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);

    // Handle file upload
    $filePath = null;
    if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
        $config = require_once __DIR__ . '/../config/xampp.php';
        $uploadDir = $config['security']['upload_dir'] . 'books/';

        // Generate unique filename
        $filename = uniqid() . '_' . basename($_FILES['book_file']['name']);
        $uploadFile = $uploadDir . $filename;

        // Check file type
        $allowedTypes = ['application/pdf'];
        if (!in_array($_FILES['book_file']['type'], $allowedTypes)) {
            $_SESSION['error'] = $localization->t('file_type');
            header('Location: ' . $baseUrl . 'admin/books');
            exit;
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['book_file']['tmp_name'], $uploadFile)) {
            $filePath = $filename;
        } else {
            $_SESSION['error'] = 'Failed to upload file';
            header('Location: ' . $baseUrl . 'admin/books');
            exit;
        }
    } else {
        $_SESSION['error'] = 'No file uploaded';
        header('Location: ' . $baseUrl . 'admin/books');
        exit;
    }

    // Insert book
    $db->query("INSERT INTO books (title, author, file_path, category_id, uploaded_by)
                VALUES (:title, :author, :file_path, :category_id, :uploaded_by)");
    $db->bind(':title', $title);
    $db->bind(':author', $author);
    $db->bind(':file_path', $filePath);
    $db->bind(':category_id', $categoryId);
    $db->bind(':uploaded_by', $auth->getUser()['id']);

    if ($db->execute()) {
        $_SESSION['success'] = $localization->t('book_added');
    } else {
        $_SESSION['error'] = 'Failed to add book';
    }

    header('Location: ' . $baseUrl . 'admin/books');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $bookId = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);

    // Get book file path
    $db->query("SELECT file_path FROM books WHERE id = :id");
    $db->bind(':id', $bookId);
    $book = $db->single();

    if ($book) {
        // Delete file
        $config = require_once __DIR__ . '/../config/xampp.php';
        $filePath = $config['security']['upload_dir'] . 'books/' . $book['file_path'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        $db->query("DELETE FROM books WHERE id = :id");
        $db->bind(':id', $bookId);

        if ($db->execute()) {
            $_SESSION['success'] = $localization->t('book_deleted');
        } else {
            $_SESSION['error'] = 'Failed to delete book';
        }
    }

    header('Location: ' . $baseUrl . 'admin/books');
    exit;
}

// Get books
$db->query("SELECT b.*, c.name as category_name, u.full_name as uploader_name
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            LEFT JOIN users u ON b.uploaded_by = u.id
            ORDER BY b.uploaded_at DESC");
$books = $db->resultSet();
?>

<h1 class="mb-4"><i class="fas fa-book-open me-2"></i><?php echo $localization->t('manage_books'); ?></h1>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-plus-circle me-1"></i> <?php echo $localization->t('add_book'); ?></h5>
    </div>
    <div class="card-body">
        <form action="<?php echo url('admin/books'); ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label"><?php echo $localization->t('title'); ?></label>
                    <input type="text" class="form-control" id="title" name="title" required>
                    <div class="invalid-feedback">
                        <?php echo $localization->t('required'); ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="author" class="form-label"><?php echo $localization->t('author'); ?></label>
                    <input type="text" class="form-control" id="author" name="author" required>
                    <div class="invalid-feedback">
                        <?php echo $localization->t('required'); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label"><?php echo $localization->t('category'); ?></label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value=""><?php echo $localization->t('select_category'); ?></option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        <?php echo $localization->t('required'); ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="book_file" class="form-label"><?php echo $localization->t('book_file'); ?></label>
                    <input type="file" class="form-control" id="book_file" name="book_file" accept="application/pdf" required>
                    <div class="form-text"><?php echo $localization->t('pdf_only'); ?></div>
                    <div class="invalid-feedback">
                        <?php echo $localization->t('required'); ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> <?php echo $localization->t('add_book'); ?>
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-1"></i> <?php echo $localization->t('books_list'); ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($books)): ?>
        <div class="alert alert-info">
            <?php echo $localization->t('no_books_found'); ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo $localization->t('id'); ?></th>
                        <th><?php echo $localization->t('title'); ?></th>
                        <th><?php echo $localization->t('author'); ?></th>
                        <th><?php echo $localization->t('category'); ?></th>
                        <th><?php echo $localization->t('uploaded_by'); ?></th>
                        <th><?php echo $localization->t('uploaded_at'); ?></th>
                        <th><?php echo $localization->t('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo $book['id']; ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($book['uploader_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($book['uploaded_at'])); ?></td>
                        <td>
                            <a href="<?php echo url('uploads/books/' . $book['file_path']); ?>" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-eye"></i> <?php echo $localization->t('view'); ?>
                            </a>
                            <a href="<?php echo url('admin/books?delete=' . $book['id']); ?>" class="btn btn-sm btn-danger btn-delete" onclick="return confirm('<?php echo $localization->t('confirm_delete'); ?>')">
                                <i class="fas fa-trash"></i> <?php echo $localization->t('delete'); ?>
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

<?php include __DIR__ . '/../views/layout/footer.php'; ?>
