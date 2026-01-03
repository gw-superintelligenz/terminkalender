/**
 * Locations Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const addLocationForm = document.getElementById('add-location-form');
    const editLocationForm = document.getElementById('edit-location-form');
    const editModal = document.getElementById('edit-modal');

    // Add new location
    if (addLocationForm) {
        addLocationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'add');

            fetch('../api/save_location.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('location-message');
                messageDiv.textContent = data.message;
                messageDiv.className = data.success ? 'alert alert-success' : 'alert alert-error';
                messageDiv.style.display = 'block';

                if (data.success) {
                    this.reset();
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessageInDiv('location-message', 'Ein Fehler ist aufgetreten.', 'error');
            });
        });
    }

    // Edit location
    document.querySelectorAll('.edit-location').forEach(button => {
        button.addEventListener('click', function() {
            const locationId = this.dataset.id;
            const locationName = this.dataset.name;
            const locationAddress = this.dataset.address;

            document.getElementById('edit-location-id').value = locationId;
            document.getElementById('edit-name').value = locationName;
            document.getElementById('edit-address').value = locationAddress;

            editModal.style.display = 'block';
        });
    });

    // Submit edit form
    if (editLocationForm) {
        editLocationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'edit');

            fetch('../api/save_location.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    editModal.style.display = 'none';
                    showMessage('Standort aktualisiert.', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Ein Fehler ist aufgetreten.', 'error');
            });
        });
    }

    // Toggle location active status
    document.querySelectorAll('.toggle-location').forEach(button => {
        button.addEventListener('click', function() {
            const locationId = this.dataset.id;
            const isActive = this.dataset.active;

            const actionText = isActive === '1' ? 'deaktivieren' : 'aktivieren';

            if (confirm(`Möchten Sie diesen Standort wirklich ${actionText}?`)) {
                const formData = new FormData();
                formData.append('action', 'toggle');
                formData.append('location_id', locationId);
                formData.append('is_active', isActive);
                formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);

                fetch('../api/save_location.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Ein Fehler ist aufgetreten.', 'error');
                });
            }
        });
    });
});

function showMessageInDiv(divId, message, type) {
    const div = document.getElementById(divId);
    div.textContent = message;
    div.className = `alert alert-${type}`;
    div.style.display = 'block';
}
