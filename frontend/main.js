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
        cottageDiv.addEventListener('click', () => fetchCottageDetails(cottage.cottage_id));
        cottagesList.appendChild(cottageDiv);
    });
}

// Fetch and display details of a selected cottage
async function fetchCottageDetails(cottageId) {
    try {
        const response = await fetch(backUrl + `get_cottage?id=${cottageId}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const cottageDetails = await response.json();
        alert(`Cottage Name: ${cottageDetails.cottage_name}\nAddress: ${cottageDetails.cottage_address}\nDescription: ${cottageDetails.cottage_description}`);
    } catch (error) {
        console.error('Error fetching cottage details:', error);
    }
}