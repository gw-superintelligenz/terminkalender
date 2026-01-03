/**
 * Admin JavaScript - General Functions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Cancel appointment functionality
    const cancelButtons = document.querySelectorAll('.cancel-appointment');
    const cancelModal = document.getElementById('cancel-modal');

    if (cancelButtons.length > 0 && cancelModal) {
        let appointmentToCancel = null;

        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                appointmentToCancel = this.dataset.id;
                const date = this.dataset.date;
                const time = this.dataset.time;

                document.getElementById('cancel-confirm-text').textContent =
                    `Möchten Sie den Termin am ${date} um ${time} Uhr wirklich stornieren?`;

                cancelModal.style.display = 'block';
            });
        });

        document.getElementById('cancel-no').addEventListener('click', function() {
            cancelModal.style.display = 'none';
            appointmentToCancel = null;
        });

        document.getElementById('cancel-yes').addEventListener('click', function() {
            if (appointmentToCancel) {
                cancelAppointment(appointmentToCancel);
            }
        });

        window.addEventListener('click', function(event) {
            if (event.target === cancelModal) {
                cancelModal.style.display = 'none';
                appointmentToCancel = null;
            }
        });
    }
});

function cancelAppointment(appointmentId) {
    const formData = new FormData();
    formData.append('appointment_id', appointmentId);

    fetch('../api/cancel_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the row from the table
            const row = document.querySelector(`tr[data-appointment-id="${appointmentId}"]`);
            if (row) {
                row.remove();
            }

            // Close modal
            document.getElementById('cancel-modal').style.display = 'none';

            // Show success message
            showMessage('Termin erfolgreich storniert.', 'success');

            // Reload page after 1 second
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showMessage(data.message || 'Fehler beim Stornieren.', 'error');
        }
    })
    .catch(error => {
        console.error('Error cancelling appointment:', error);
        showMessage('Ein Fehler ist aufgetreten.', 'error');
    });
}

function showMessage(message, type) {
    // Create a temporary alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.style.position = 'fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '10000';
    alert.style.minWidth = '300px';

    document.body.appendChild(alert);

    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// Modal helper functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModalById(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Attach close handlers to all modals
document.querySelectorAll('.close, .close-modal').forEach(element => {
    element.addEventListener('click', function() {
        const modal = this.closest('.modal');
        if (modal) {
            modal.style.display = 'none';
        }
    });
});
