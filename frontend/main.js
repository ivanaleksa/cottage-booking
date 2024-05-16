let backUrl = "http://localhost:8080/";

// Fetch cottages from the server
async function fetchCottages() {
    try {
        const response = await fetch(backUrl + 'get_cottages');
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const cottages = await response.json();
        displayCottages(cottages);
    } catch (error) {
        console.error('Error fetching cottages:', error);
    }
}

// Display the list of cottages
function displayCottages(cottages) {
    const cottagesList = document.getElementById('cottages-list');
    cottagesList.innerHTML = '';
    cottages.forEach(cottage => {
        const cottageDiv = document.createElement('div');
        cottageDiv.className = 'cottage';
        cottageDiv.textContent = `${cottage.cottage_name} - ${cottage.cottage_address}`;
        cottageDiv.addEventListener('click', () => {
            window.location.href = `cottage_page.html?cottage_id=${cottage.cottage_id}`;
        });
        cottagesList.appendChild(cottageDiv);
    });
}
