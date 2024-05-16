let backUrl = "http://localhost:8080";

document.addEventListener('DOMContentLoaded', () => {
    fetchBookings();

    // Function to fetch bookings
    async function fetchBookings() {
        try {
            const response = await fetch(backUrl + '/get_bookings', {credentials: 'include'});
            if (!response.ok) {
                if (response.status === 403) {
                    window.location.href = '/login.html';
                } else {
                    throw new Error('Failed to fetch bookings');
                }
            }
            const bookings = await response.json();
            populateTable(bookings);
        } catch (error) {
            console.error('Error fetching bookings:', error);
        }
    }

    // Function to populate the table with bookings
    function populateTable(bookings) {
        const tableBody = document.querySelector('#bookings-table tbody');
        tableBody.innerHTML = '';

        bookings.forEach(booking => {
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>${booking.booking_id}</td>
                <td>${booking.cottage_id}</td>
                <td>${booking.cottage_name}</td>
                <td>${booking.client_name}</td>
                <td>${booking.client_phone_number}</td>
                <td>${booking.booking_start_at}</td>
                <td>${booking.booking_end_at}</td>
                <td>${booking.booking_confirmation_date || 'Not confirmed'}</td>
                <td class="actions">
                    ${!booking.booking_confirmation_date ? 
                        `<button class="confirm-btn" data-id="${booking.booking_id}">Confirm</button>` 
                        : ''}
                    <button class="delete-btn" data-id="${booking.booking_id}">Delete</button>
                </td>
            `;

            tableBody.appendChild(row);
        });

        document.querySelectorAll('.confirm-btn').forEach(button => {
            button.addEventListener('click', () => confirmBooking(button.dataset.id));
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => deleteBooking(button.dataset.id));
        });
    }

    // Function to confirm a booking
    async function confirmBooking(bookingId) {
        try {
            const response = await fetch(backUrl + `/update_booking/${bookingId}`, {
                method: 'PATCH',
                credentials: 'include'
            });
            if (!response.ok) {
                if (response.status === 403) {
                    window.location.href = '/login.html';
                } else {
                    throw new Error('Failed to update bookings');
                }
            }
            fetchBookings();
        } catch (error) {
            console.error('Error confirming booking:', error);
        }
    }

    // Function to delete a booking
    async function deleteBooking(bookingId) {
        try {
            const response = await fetch(backUrl + '/delete_booking', {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `booking_id=${bookingId}`
            });
            if (!response.ok) {
                if (response.status === 403) {
                    window.location.href = '/login.html';
                } else {
                    throw new Error('Failed to delete bookings');
                }
            }
            fetchBookings();
        } catch (error) {
            console.error('Error deleting booking:', error);
        }
    }
});
