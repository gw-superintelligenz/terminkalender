/**
 * Availability Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const addAvailabilityForm = document.getElementById('add-availability-form');
    const addExceptionForm = document.getElementById('add-exception-form');
    const exceptionType = document.getElementById('exception-type');
    const specialTimes = document.getElementById('special-times');

    // Add availability rule
    if (addAvailabilityForm) {
        addAvailabilityForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'add');

            fetch('../api/save_availability.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('availability-message');
                messageDiv.textContent = data.message;
                messageDiv.className = data.success ? 'alert alert-success' : 'alert alert-error';
                messageDiv.style.display = 'block';

                if (data.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessageInDiv('availability-message', 'Ein Fehler ist aufgetreten.', 'error');
            });
        });
    }

    // Delete availability rule
    document.querySelectorAll('.delete-rule').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Möchten Sie diese Verfügbarkeit wirklich löschen?')) {
                const ruleId = this.dataset.id;
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('rule_id', ruleId);
                formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);

                fetch('../api/save_availability.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = this.closest('tr');
                        row.remove();
                        showMessage('Verfügbarkeit gelöscht.', 'success');
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

    // Exception type toggle
    if (exceptionType && specialTimes) {
        console.log('Exception type handler attached');

        exceptionType.addEventListener('change', function() {
            console.log('Exception type changed to:', this.value);

            if (this.value === 'special' || this.value === 'block_slots') {
                console.log('Showing special times fields');
                specialTimes.style.display = 'block';
                document.getElementById('exception-start').required = true;
                document.getElementById('exception-end').required = true;
            } else {
                console.log('Hiding special times fields');
                specialTimes.style.display = 'none';
                document.getElementById('exception-start').required = false;
                document.getElementById('exception-end').required = false;
            }
        });
    } else {
        console.error('Exception type or special times element not found');
    }

    // Add exception
    if (addExceptionForm) {
        addExceptionForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'add');

            fetch('../api/save_exception.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('exception-message');
                messageDiv.textContent = data.message;
                messageDiv.className = data.success ? 'alert alert-success' : 'alert alert-error';
                messageDiv.style.display = 'block';

                if (data.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessageInDiv('exception-message', 'Ein Fehler ist aufgetreten.', 'error');
            });
        });

        // Load existing exceptions
        loadExceptions();
    }
});

function loadExceptions() {
    const formData = new FormData();
    formData.append('action', 'list');
    formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);

    fetch('../api/save_exception.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.exceptions.length > 0) {
            const exceptionsDiv = document.getElementById('exceptions-list');
            let html = '<h4 style="margin-top: 30px; color: var(--primary-color);">Bestehende Ausnahmen</h4>';
            html += '<div class="table-responsive"><table class="admin-table">';
            html += '<thead><tr><th>Datum</th><th>Typ</th><th>Details</th><th>Aktionen</th></tr></thead><tbody>';

            data.exceptions.forEach(exc => {
                const date = new Date(exc.exception_date);
                const dateStr = date.toLocaleDateString('de-DE');

                html += '<tr>';
                html += `<td>${dateStr}</td>`;

                if (exc.is_blocked == 1 && (!exc.start_time || !exc.end_time)) {
                    // Whole day blocked (no times specified)
                    html += '<td><span class="location-status inactive">Tag gesperrt</span></td>';
                    html += '<td>Ganzer Tag nicht verfügbar</td>';
                } else if (exc.is_blocked == 1 && exc.start_time && exc.end_time) {
                    // Specific time slots blocked
                    html += '<td><span class="location-status inactive">Zeitslots gesperrt</span></td>';
                    html += `<td>${exc.start_time.substring(0, 5)} - ${exc.end_time.substring(0, 5)} gesperrt</td>`;
                } else {
                    // Special availability (is_blocked = 0)
                    html += '<td><span class="location-status active">Spezielle Zeiten</span></td>';
                    html += `<td>${exc.start_time.substring(0, 5)} - ${exc.end_time.substring(0, 5)} (${exc.duration_minutes} Min.)</td>`;
                }

                html += `<td><button class="btn btn-small btn-danger delete-exception" data-id="${exc.id}">Löschen</button></td>`;
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            exceptionsDiv.innerHTML = html;

            // Attach delete handlers
            document.querySelectorAll('.delete-exception').forEach(button => {
                button.addEventListener('click', function() {
                    deleteException(this.dataset.id);
                });
            });
        }
    })
    .catch(error => {
        console.error('Error loading exceptions:', error);
    });
}

function deleteException(exceptionId) {
    if (confirm('Möchten Sie diese Ausnahme wirklich löschen?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('exception_id', exceptionId);
        formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);

        fetch('../api/save_exception.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Ausnahme gelöscht.', 'success');
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
}

function showMessageInDiv(divId, message, type) {
    const div = document.getElementById(divId);
    div.textContent = message;
    div.className = `alert alert-${type}`;
    div.style.display = 'block';
}
