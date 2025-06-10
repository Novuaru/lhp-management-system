</div> <!-- Close container from header -->
    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© <?php echo date('Y'); ?> Sistem Manajemen LHP. All rights reserved.</span>
        </div>
    </footer>

    <!-- Notification Scripts -->
    <script>
    function checkNotifications() {
        $.ajax({
            url: '/notifications/check.php',
            method: 'GET',
            success: function(response) {
                const data = JSON.parse(response);
                $('#notificationCount').text(data.unread);
                
                let notificationHtml = '';
                if (data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        notificationHtml += `
                            <a class="dropdown-item ${!notification.is_read ? 'bg-light' : ''}" href="#">
                                ${notification.message}
                                <small class="text-muted d-block">
                                    ${notification.created_at}
                                </small>
                            </a>
                        `;
                    });
                } else {
                    notificationHtml = '<div class="dropdown-item">Tidak ada notifikasi baru</div>';
                }
                
                $('#notificationList').html(notificationHtml);
            }
        });
    }

    // Check notifications every 5 minutes
    $(document).ready(function() {
        checkNotifications();
        setInterval(checkNotifications, 300000);
    });
    </script>

    <!-- Service Worker Registration -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/notifications/service-worker.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed: ', err);
            });
        });
    }
    </script>
</body>
</html>
