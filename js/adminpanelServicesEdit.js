let selectedServiceId = null;

function selectService(serviceId, serviceTitle, servicePrice, serviceDetails) {
    selectedServiceId = serviceId;
    document.getElementById('serviceTitle').value = serviceTitle;
    document.getElementById('servicePrice').value = servicePrice;
    document.getElementById('serviceDetails').value = serviceDetails;
    $('#serviceModal').modal('show');
}

function editService() {
    if (selectedServiceId) {
        let serviceTitle = document.getElementById('serviceTitle').value;
        let servicePrice = document.getElementById('servicePrice').value;
        let serviceDetails = document.getElementById('serviceDetails').value;

        let formData = new FormData();
        formData.append('service_id', selectedServiceId);
        formData.append('service_title', serviceTitle);
        formData.append('service_price', servicePrice);
        formData.append('service_details', serviceDetails);

        fetch('php/edit_service.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            window.location.href = 'adminpanel.php';
        })
        .catch(error => {
            console.error('There was an error!', error);
        });
    } else {
        alert('Будь ласка, оберіть послугу для редагування.');
    }
}

function confirmDeleteService() {
    if (selectedServiceId) {
        if (confirm('Ви впевнені, що хочете видалити цю послугу?')) {
            deleteService();
        }
    } else {
        alert('Будь ласка, оберіть послугу для видалення.');
    }
}

function deleteService() {
    let formData = new FormData();
    formData.append('service_id', selectedServiceId);

    fetch('php/delete_service.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        alert(data);
        window.location.href = 'adminpanel.php';
    })
    .catch(error => {
        console.error('There was an error!', error);
    });
}

function addService() {
    let newServiceTitle = document.getElementById('newServiceTitle').value;
    let newServicePrice = document.getElementById('newServicePrice').value;
    let newServiceDetails = document.getElementById('newServiceDetails').value;

    if (newServiceTitle && newServicePrice && newServiceDetails) {
        let formData = new FormData();
        formData.append('title', newServiceTitle);
        formData.append('price', newServicePrice);
        formData.append('service_details', newServiceDetails);

        fetch('php/add_service.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            alert(data);
            window.location.href = 'adminpanel.php';
        })
        .catch(error => {
            console.error('There was an error!', error);
        });
    } else {
        alert('Будь ласка, заповніть всі поля форми.');
    }
}