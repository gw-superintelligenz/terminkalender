/**
 * Patient Calendar JavaScript
 */

let currentWeekStart = null;
let selectedLocation = null;
let selectedSlot = null;
let bookedAppointment = null;
let pendingRequests = [];
let isLoading = false;

document.addEventListener('DOMContentLoaded', function() {
    const locationSelect = document.getElementById('location-select');
    const calendarContainer = document.getElementById('calendar-container');
    const noLocationMessage = document.getElementById('no-location-message');
    const modal = document.getElementById('booking-modal');
    const closeBtn = modal.querySelector('.close');
    const bookingForm = document.getElementById('booking-form');
    const cancelBtn = document.getElementById('cancel-booking');
    const closeSuccessBtn = document.getElementById('close-success');
    const closeErrorBtn = document.getElementById('close-error');

    // Initialize to current week
    currentWeekStart = getMonday(new Date());

    // Location selection
    locationSelect.addEventListener('change', function() {
        selectedLocation = this.value;

        if (selectedLocation) {
            calendarContainer.style.display = 'block';
            noLocationMessage.style.display = 'none';
            loadCalendar();
        } else {
            calendarContainer.style.display = 'none';
            noLocationMessage.style.display = 'block';
        }
    });

    // Week navigation
    document.getElementById('prev-week').addEventListener('click', function() {
        // Don't navigate if already loading
        if (isLoading) return;

        // Don't allow going before current week
        const today = getMonday(new Date());

        const prevWeek = new Date(currentWeekStart);
        prevWeek.setDate(prevWeek.getDate() - 7);

        // Compare dates by converting to time values (milliseconds since epoch)
        // This ensures accurate comparison without time component issues
        if (prevWeek.getTime() >= today.getTime()) {
            currentWeekStart = prevWeek;
            loadCalendar();
        }
    });

    document.getElementById('next-week').addEventListener('click', function() {
        // Don't navigate if already loading
        if (isLoading) return;

        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 6);

        const nextWeek = new Date(currentWeekStart);
        nextWeek.setDate(nextWeek.getDate() + 7);

        if (nextWeek <= maxDate) {
            currentWeekStart = nextWeek;
            loadCalendar();
        }
    });

    // Modal close
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    closeSuccessBtn.addEventListener('click', closeModal);
    closeErrorBtn.addEventListener('click', closeModal);

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Booking form submission
    bookingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitBooking();
    });
});

function getMonday(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    d.setDate(diff);
    d.setHours(0, 0, 0, 0); // Reset to midnight for consistent comparisons
    return d;
}

function loadCalendar() {
    // Cancel any pending requests
    pendingRequests.forEach(controller => controller.abort());
    pendingRequests = [];

    // Set loading state
    isLoading = true;
    setNavigationEnabled(false);

    const weekDisplay = document.getElementById('current-week-display');
    const calendarGrid = document.getElementById('calendar-grid');

    // Update week display
    const weekEnd = new Date(currentWeekStart);
    weekEnd.setDate(weekEnd.getDate() + 6);

    weekDisplay.textContent = formatDateRange(currentWeekStart, weekEnd);

    // Clear calendar
    calendarGrid.innerHTML = '';

    // Generate 7 days
    const dayNames = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let slotsToLoad = 0;
    let slotsLoaded = 0;

    for (let i = 0; i < 7; i++) {
        const currentDate = new Date(currentWeekStart);
        currentDate.setDate(currentDate.getDate() + i);

        const dayDiv = document.createElement('div');
        dayDiv.className = 'calendar-day';

        if (currentDate < today) {
            dayDiv.classList.add('past');
        }

        const dayHeader = document.createElement('div');
        dayHeader.className = 'day-header';
        dayHeader.innerHTML = `
            ${dayNames[i]}
            <span class="day-date">${formatDate(currentDate)}</span>
        `;

        dayDiv.appendChild(dayHeader);

        const slotsDiv = document.createElement('div');
        slotsDiv.className = 'time-slots';
        slotsDiv.id = `slots-${formatDateISO(currentDate)}`;

        dayDiv.appendChild(slotsDiv);
        calendarGrid.appendChild(dayDiv);

        // Load slots for this day if not in the past
        if (currentDate >= today) {
            slotsToLoad++;
            loadSlots(currentDate, function() {
                slotsLoaded++;
                if (slotsLoaded === slotsToLoad) {
                    // All slots loaded, re-enable navigation
                    isLoading = false;
                    setNavigationEnabled(true);
                }
            });
        } else {
            slotsDiv.innerHTML = '<div class="time-slot unavailable">Vergangener Tag</div>';
        }
    }

    // If no slots to load (all past days), re-enable immediately
    if (slotsToLoad === 0) {
        isLoading = false;
        setNavigationEnabled(true);
    }
}

function setNavigationEnabled(enabled) {
    const prevBtn = document.getElementById('prev-week');
    const nextBtn = document.getElementById('next-week');

    if (prevBtn) prevBtn.disabled = !enabled;
    if (nextBtn) nextBtn.disabled = !enabled;
}

function loadSlots(date, callback) {
    const dateStr = formatDateISO(date);
    const slotsContainer = document.getElementById(`slots-${dateStr}`);

    if (!slotsContainer) {
        if (callback) callback();
        return;
    }

    slotsContainer.innerHTML = '<div class="loading"></div>';

    // Create AbortController for this request
    const controller = new AbortController();
    pendingRequests.push(controller);

    fetch(`api/get_slots.php?date=${dateStr}&location_id=${selectedLocation}`, {
        signal: controller.signal
    })
        .then(response => response.json())
        .then(data => {
            // Check if container still exists (not cleared by new calendar load)
            const currentContainer = document.getElementById(`slots-${dateStr}`);
            if (!currentContainer) {
                if (callback) callback();
                return;
            }

            currentContainer.innerHTML = '';

            if (data.success && data.slots.length > 0) {
                data.slots.forEach(slot => {
                    const slotDiv = document.createElement('div');
                    slotDiv.className = 'time-slot available';
                    slotDiv.textContent = slot.display;
                    slotDiv.dataset.date = dateStr;
                    slotDiv.dataset.time = slot.time;
                    slotDiv.dataset.duration = slot.duration;

                    slotDiv.addEventListener('click', function() {
                        openBookingModal(this.dataset);
                    });

                    currentContainer.appendChild(slotDiv);
                });
            } else {
                currentContainer.innerHTML = '<div class="time-slot unavailable">Keine Termine</div>';
            }

            if (callback) callback();
        })
        .catch(error => {
            // Ignore abort errors (happens when navigating quickly)
            if (error.name === 'AbortError') {
                if (callback) callback();
                return;
            }

            console.error('Error loading slots:', error);
            const currentContainer = document.getElementById(`slots-${dateStr}`);
            if (currentContainer) {
                currentContainer.innerHTML = '<div class="time-slot unavailable">Fehler beim Laden</div>';
            }

            if (callback) callback();
        })
        .finally(() => {
            // Remove controller from pending requests
            const index = pendingRequests.indexOf(controller);
            if (index > -1) {
                pendingRequests.splice(index, 1);
            }
        });
}

function openBookingModal(slotData) {
    selectedSlot = slotData;

    const modal = document.getElementById('booking-modal');
    const locationSelect = document.getElementById('location-select');
    const selectedLocationText = locationSelect.options[locationSelect.selectedIndex].text;

    // Show booking form, hide success/error messages
    document.getElementById('booking-form').style.display = 'block';
    document.getElementById('booking-success').style.display = 'none';
    document.getElementById('booking-error').style.display = 'none';

    // Populate modal info
    document.getElementById('modal-location').textContent = selectedLocationText;
    document.getElementById('modal-date').textContent = formatDateDE(slotData.date);
    document.getElementById('modal-time').textContent = slotData.time.substring(0, 5);
    document.getElementById('modal-duration').textContent = slotData.duration;

    // Set hidden form fields
    document.getElementById('form-location-id').value = selectedLocation;
    document.getElementById('form-date').value = slotData.date;
    document.getElementById('form-time').value = slotData.time;
    document.getElementById('form-duration').value = slotData.duration;

    // Clear form inputs
    document.getElementById('patient-name').value = '';
    document.getElementById('patient-phone').value = '';
    document.getElementById('patient-comment').value = '';

    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('booking-modal');
    modal.style.display = 'none';
    selectedSlot = null;
}

function submitBooking() {
    const form = document.getElementById('booking-form');
    const formData = new FormData(form);

    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Wird gebucht...';

    fetch('api/book_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store booking details for ICS download
            bookedAppointment = {
                date: selectedSlot.date,
                time: selectedSlot.time,
                duration: selectedSlot.duration,
                location: document.getElementById('modal-location').textContent,
                name: document.getElementById('patient-name').value
            };

            // Show success message
            document.getElementById('booking-form').style.display = 'none';
            document.getElementById('booking-success').style.display = 'block';

            // Setup ICS download link
            const downloadLink = document.getElementById('download-ics');
            if (downloadLink) {
                // Remove any existing listeners by cloning the element
                const newDownloadLink = downloadLink.cloneNode(true);
                downloadLink.parentNode.replaceChild(newDownloadLink, downloadLink);

                newDownloadLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Download link clicked', bookedAppointment);
                    const params = new URLSearchParams({
                        date: bookedAppointment.date,
                        time: bookedAppointment.time,
                        duration: bookedAppointment.duration,
                        location: bookedAppointment.location,
                        name: bookedAppointment.name
                    });
                    const url = `api/download_ics.php?${params.toString()}`;
                    console.log('Download URL:', url);
                    window.location.href = url;
                });
            } else {
                console.error('Download link element not found');
            }

            // Reload calendar to update availability
            loadCalendar();
        } else {
            // Show error message
            document.getElementById('booking-form').style.display = 'none';
            document.getElementById('booking-error').style.display = 'block';
            document.getElementById('error-text').textContent = data.message || 'Ein Fehler ist aufgetreten.';
        }
    })
    .catch(error => {
        console.error('Error booking appointment:', error);
        document.getElementById('booking-form').style.display = 'none';
        document.getElementById('booking-error').style.display = 'block';
        document.getElementById('error-text').textContent = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Termin bestätigen';
    });
}

// Helper functions
function formatDate(date) {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}.${month}.${year}`;
}

function formatDateISO(date) {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateDE(dateStr) {
    const date = new Date(dateStr);
    const dayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    const monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
                        'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    return `${dayNames[date.getDay()]}, ${date.getDate()}. ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
}

function formatDateRange(start, end) {
    const monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
                        'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    if (start.getMonth() === end.getMonth()) {
        return `${start.getDate()} - ${end.getDate()} ${monthNames[start.getMonth()]} ${start.getFullYear()}`;
    } else {
        return `${start.getDate()} ${monthNames[start.getMonth()]} - ${end.getDate()} ${monthNames[end.getMonth()]} ${start.getFullYear()}`;
    }
}
