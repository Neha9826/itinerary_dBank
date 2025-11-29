</main> <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">CareMyTrip.com</div>
            <strong>Copyright &copy; 2025.</strong> All rights reserved.
        </footer>
    </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="./js/adminlte.js"></script>

    <script>
        $(document).ready(function() {
            // Apply Select2 to elements with class 'select2-dynamic'
            $('.select2-dynamic').select2({
                theme: 'bootstrap-5',
                tags: true, // THIS ALLOWS TYPING NEW VALUES
                width: '100%',
                placeholder: 'Select or Type New...'
            });
        });

      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
</body>
</html>