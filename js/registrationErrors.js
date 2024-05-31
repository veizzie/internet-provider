function validateForm() {
    var isValid = true;
    clearErrors();

    var fieldsToCheck = [
        { id: 'login', minLength: 5, maxLength: 25, errorMessage: 'Неприпустима довжина логіну (від 5 до 25 символів)' },
        { id: 'pass', minLength: 8, maxLength: 25, errorMessage: 'Неприпустима довжина пароля (від 8 до 25 символів)' },
        { id: 'name', minLength: 2, maxLength: 45, errorMessage: 'Неприпустима довжина імені (від 2 до 45 символів)' },
        { id: 'l_name', minLength: 2, maxLength: 45, errorMessage: 'Неприпустима довжина фамілії (від 2 до 45 символів)' },
        { id: 'phone', minLength: 9, maxLength: 9, errorMessage: 'Неприпустима довжина номеру телефону (9 цифр)' },
        { id: 'street', minLength: 5, maxLength: 50, errorMessage: 'Неприпустима довжина назви вулиці (від 5 до 50 символів)' },
        { id: 'address_number', minLength: 1, maxLength: 10, errorMessage: 'Неприпустима довжина номеру будинку чи квартири (від 1 до 10 символів)' }
    ];

    fieldsToCheck.forEach(function(field) {
        var value = document.getElementById(field.id).value.trim();
        if (value.length < field.minLength || value.length > field.maxLength) {
            showError(field.id + 'Error', field.errorMessage, field.id);
            isValid = false;
        }
    });

    return isValid;
}

function showError(errorId, errorMessage, inputId) {
    var errorField = document.getElementById(errorId);
    errorField.textContent = errorMessage;
    var inputField = document.getElementById(inputId);
    inputField.classList.add('is-invalid');
    errorField.classList.add('text-danger');
}

function clearErrors() {
    var errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(function(element) {
        element.textContent = '';
        element.classList.remove('text-danger');
    });

    var invalidFields = document.querySelectorAll('.is-invalid');
    invalidFields.forEach(function(element) {
        element.classList.remove('is-invalid');
    });
}
