document.addEventListener('DOMContentLoaded', function() {
    function showNotificationsSequentially() {
        var notifications = document.querySelectorAll('#unique-notifications-container .unique-notification');
        var index = 0;

        function showNextNotification() {
            if (index < notifications.length) {
                var notification = notifications[index];
                notification.style.display = 'block';

                setTimeout(function() {
                    notification.classList.add('fade-out');
                    setTimeout(function() {
                        notification.style.display = 'none';
                        notification.classList.remove('fade-out');
                        markNotificationAsRead(notification.dataset.id);
                        index++;
                        showNextNotification();
                    }, 500); // Temps pour l'animation de disparition
                }, 2500); // Temps pour afficher la notification
            }
        }

        showNextNotification();
    }

    function markNotificationAsRead(notificationId) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Notification " + notificationId + " marquée comme lue.");
            } else {
                console.error("Erreur lors de la mise à jour de la notification " + notificationId);
            }
        })
        .catch(() => {
            console.error("Erreur lors de la requête pour marquer la notification " + notificationId + " comme lue.");
        });
    }

    if (document.querySelectorAll('#unique-notifications-container .unique-notification').length) {
        showNotificationsSequentially();
    }
});