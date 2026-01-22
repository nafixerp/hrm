        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('.datatable').DataTable({
                    "pageLength": <?php echo RECORDS_PER_PAGE; ?>,
                    "ordering": true,
                    "searching": true,
                    "responsive": true
                });
            }

            // Initialize Select2
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5'
                });
            }

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Confirm delete actions
            $('.btn-delete').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });

            // Print button
            $('.btn-print').on('click', function() {
                window.print();
            });
        });

        // Format currency
        function formatCurrency(amount) {
            return '<?php echo CURRENCY_SYMBOL; ?> ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return day + '-' + month + '-' + year;
        }

        // Show loading indicator
        function showLoading(message = 'Loading...') {
            if (!$('#loadingModal').length) {
                $('body').append(`
                    <div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body text-center p-4">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mb-0">${message}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }
            $('#loadingModal').modal('show');
        }

        // Hide loading indicator
        function hideLoading() {
            $('#loadingModal').modal('hide');
        }

        // Show success message
        function showSuccess(message) {
            showAlert('success', message);
        }

        // Show error message
        function showError(message) {
            showAlert('danger', message);
        }

        // Show alert
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('.content-area').prepend(alertHtml);
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        }
    </script>

    <?php if (isset($customJS)): ?>
        <script><?php echo $customJS; ?></script>
    <?php endif; ?>
</body>
</html>
