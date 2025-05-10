        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white">
        <div class="container py-3">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo $localization->t('app_name'); ?></h5>
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $localization->t('app_name'); ?>. <?php echo $localization->t('all_rights_reserved'); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="#" class="text-white">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="text-white">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="text-white">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="<?php echo url('assets/js/main.js'); ?>"></script>
</body>
</html>
