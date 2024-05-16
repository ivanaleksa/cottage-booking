document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const cottageId = urlParams.get('cottage_id');
    if (cottageId) {
        fetchCottageDetails(cottageId);
        fetchBookingDates(cottageId);
    }

    document.getElementById('booking-form').addEventListener('submit', function(event) {
        event.preventDefault();
        submitBooking(cottageId);
    });
});

let backUrl = "http://localhost:8080/";
let bookingDates = [];

async function fetchCottageDetails(cottageId) {
    try {
        const response = await fetch(backUrl + `get_cottage?id=${cottageId}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const cottage = await response.json();
        document.title = cottage.cottage_name;
        document.getElementById('cottage-title').textContent = cottage.cottage_name;
        document.getElementById('cottage-details').innerHTML = `
            <p><b>Address</b>: ${cottage.cottage_address}</p>
            <p><b>Description</b>: ${cottage.cottage_description}</p>
        `;
    } catch (error) {
        console.error('Error fetching cottage details:', error);
    }
}

async function fetchBookingDates(cottageId) {
    try {
        const response = await fetch(backUrl + `get_booking_dates?id=${cottageId}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        bookingDates = await response.json();
        displayBookingDates(bookingDates);
    } catch (error) {
        console.error('Error fetching booking dates:', error);
    }
}

function displayBookingDates(bookingDates) {
    const container = document.getElementById('calendar-container');
    container.innerHTML = '';

    const today = new Date();
    for (let i = 0; i < 3; i++) {
        const month = new Date(today.getFullYear(), today.getMonth() + i, 1);
        const table = generateMonthTable(month, bookingDates);
        container.appendChild(table);
    }
}

function generateMonthTable(month, bookingDates) {
    const table = document.createElement('table');
    table.className = 'calendar';
    const caption = document.createElement('caption');
    caption.textContent = month.toLocaleString('default', { month: 'long', year: 'numeric' });
    table.appendChild(caption);

    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const headerRow = document.createElement('tr');
    daysOfWeek.forEach(day => {
        const th = document.createElement('th');
        th.textContent = day;
        headerRow.appendChild(th);
    });
    table.appendChild(headerRow);

    let date = new Date(month.getFullYear(), month.getMonth(), 1);
    const endDate = new Date(month.getFullYear(), month.getMonth() + 1, 0);

    while (date.getDay() !== 0) {
        date.setDate(date.getDate() - 1);
    }

    while (date <= endDate || date.getDay() !== 0) {
        const row = document.createElement('tr');
        for (let i = 0; i < 7; i++) {
            const cell = document.createElement('td');
            if (date.getMonth() === month.getMonth()) {
                cell.textContent = date.getDate();
                if (isDateBooked(date, bookingDates)) {
                    cell.classList.add('booked');
                }
            } else {
                cell.classList.add('empty');
            }
            row.appendChild(cell);
            date.setDate(date.getDate() + 1);
        }
        table.appendChild(row);
    }

    return table;
}

function isDateBooked(date, bookingDates) {
    return bookingDates.some(booking => {
        const start = new Date(booking.booking_start_at);
        const end = new Date(booking.booking_end_at);
        return date >= start && date <= end;
    });
}

function isDateRangeBooked(startDate, endDate, bookingDates) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    return bookingDates.some(booking => {
        const bookingStart = new Date(booking.booking_start_at);
        const bookingEnd = new Date(booking.booking_end_at);
        return (start <= bookingEnd && end >= bookingStart);
    });
}

async function submitBooking(cottageId) {
    const name = document.getElementById('client-name').value;
    const phone = document.getElementById('client-phone').value;
    const startDate = document.getElementById('booking-start').value;
    const endDate = document.getElementById('booking-end').value;

    if (isDateRangeBooked(startDate, endDate, bookingDates)) {
        alert('Selected dates overlap with existing bookings. Please choose different dates.');
        return;
    }

    const bookingData = {
        cottageId: cottageId,
        name: name,
        phoneNumber: phone,
        startDate: startDate,
        endDate: endDate
    };

    try {
        const response = await fetch(backUrl + 'add_booking', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(bookingData)
        });
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        alert('Booking successful! Please, wait for its confirmation!');
        fetchBookingDates(cottageId);
    } catch (error) {
        console.error('Error submitting booking:', error);
        alert('Failed to book cottage.');
    }
}
