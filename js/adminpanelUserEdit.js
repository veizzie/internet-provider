let selectedUserId = null;

function selectUser(userId, firstName, lastName, userRole, phoneNumber, adress) {
    selectedUserId = userId;
    document.getElementById('userFirstName').value = firstName;
    document.getElementById('userLastName').value = lastName;
    document.getElementById('userRole').value = userRole;
    document.getElementById('userPhoneNumber').value = phoneNumber;
    document.getElementById('userAdress').value = adress;
    $('#userModal').modal('show');
}

function editUser() {
    if (selectedUserId) {
        let firstName = document.getElementById('userFirstName').value;
        let lastName = document.getElementById('userLastName').value;
        let userRole = document.getElementById('userRole').value;
        let phoneNumber = document.getElementById('userPhoneNumber').value;
        let adress = document.getElementById('userAdress').value;

        let formData = new FormData();
        formData.append('user_id', selectedUserId);
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('user_type', userRole);
        formData.append('phone_number', phoneNumber);
        formData.append('adress', adress);

        fetch('php/edit_user.php', {
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
        alert('Будь ласка, оберіть користувача для редагування.');
    }
}

function confirmDeleteUser() {
    if (selectedUserId) {
        if (confirm('Ви впевнені, що хочете видалити цього користувача?')) {
            window.location.href = 'php/delete_user.php?id=' + selectedUserId;
        }
    } else {
        alert('Будь ласка, оберіть користувача для видалення.');
    }
}
