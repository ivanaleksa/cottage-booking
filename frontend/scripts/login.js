let backUrl = "http://localhost:8080";

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const login = document.getElementById('login').value;
        const password = document.getElementById('password').value;
        const response = await fetch(backUrl + '/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ login, password })
        });

        const result = await response.json();
        if (response.ok) {
            document.cookie = `admin_token=${result.token}; path=/; max-age=3600`;
            window.location.href = 'admin_dashboard.html';
        } else {
            document.getElementById('error-message').textContent = result.error;
        }
    });
});
