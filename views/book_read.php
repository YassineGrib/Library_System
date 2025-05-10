<?php
/**
 * Book Read Page
 * This page displays a PDF reader for reading books online
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

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    $_SESSION['error'] = $localization->t('login_to_access');
    redirect('login');
}

// Record this download/view
$db->query("INSERT INTO downloads (book_id, user_id) VALUES (:book_id, :user_id)");
$db->bind(':book_id', $bookId);
$db->bind(':user_id', $auth->getUser()['id']);
$db->execute();

// Get the file path
$filePath = url('public/uploads/books/' . $book['file_path']);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><?php echo htmlspecialchars($book['title']); ?></h2>
                    <div>
                        <a href="<?php echo url('books/view/' . $book['id']); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> <?php echo $localization->t('back_to_details'); ?>
                        </a>
                        <a href="<?php echo url('public/uploads/books/' . $book['file_path']); ?>" class="btn btn-success btn-sm" download>
                            <i class="fas fa-download me-1"></i> <?php echo $localization->t('download'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="pdf-container" style="height: 80vh;">
                <!-- PDF.js viewer -->
                <div id="pdf-viewer" style="width: 100%; height: 100%; border: none;">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading PDF...</p>
                    </div>
                </div>

                <!-- Fallback iframe if PDF.js fails -->
                <iframe id="pdf-iframe" src="<?php echo $filePath; ?>" style="width: 100%; height: 100%; border: none; display: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Try to load PDF with PDF.js first
        const pdfViewer = document.getElementById('pdf-viewer');
        const pdfIframe = document.getElementById('pdf-iframe');
        const pdfUrl = "<?php echo $filePath; ?>";

        // Function to show error message
        function showErrorMessage() {
            pdfViewer.innerHTML = `
                <div class="alert alert-danger">
                    <h4><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $localization->t('error'); ?></h4>
                    <p><?php echo $localization->t('pdf_load_error'); ?></p>
                    <div class="d-flex gap-2 mt-3">
                        <a href="<?php echo $filePath; ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> <?php echo $localization->t('open_in_new_tab'); ?>
                        </a>
                        <a href="<?php echo $filePath; ?>" class="btn btn-success" download>
                            <i class="fas fa-download me-1"></i> <?php echo $localization->t('download'); ?>
                        </a>
                    </div>
                </div>
            `;
        }

        // Try to load PDF directly in iframe as fallback
        pdfIframe.onerror = function() {
            pdfIframe.style.display = 'none';
            showErrorMessage();
        };

        // If iframe loads successfully, show it
        pdfIframe.onload = function() {
            // Check if iframe loaded correctly by checking its content
            try {
                // If we can access iframe content, it loaded successfully
                if (pdfIframe.contentDocument) {
                    pdfViewer.style.display = 'none';
                    pdfIframe.style.display = 'block';
                } else {
                    showErrorMessage();
                }
            } catch (e) {
                // If we can't access iframe content due to CORS, show it anyway
                pdfViewer.style.display = 'none';
                pdfIframe.style.display = 'block';
            }
        };

        // Try to load PDF with object tag as another option
        setTimeout(function() {
            if (pdfIframe.style.display === 'none') {
                pdfViewer.innerHTML = `
                    <object data="${pdfUrl}" type="application/pdf" width="100%" height="100%">
                        <div class="alert alert-danger">
                            <h4><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $localization->t('error'); ?></h4>
                            <p><?php echo $localization->t('pdf_load_error'); ?></p>
                            <div class="d-flex gap-2 mt-3">
                                <a href="<?php echo $filePath; ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-external-link-alt me-1"></i> <?php echo $localization->t('open_in_new_tab'); ?>
                                </a>
                                <a href="<?php echo $filePath; ?>" class="btn btn-success" download>
                                    <i class="fas fa-download me-1"></i> <?php echo $localization->t('download'); ?>
                                </a>
                            </div>
                        </div>
                    </object>
                `;
            }
        }, 2000);
    });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
