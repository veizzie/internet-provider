function validateLoginForm() {
    var isValid = true;
    clearErrors();

    var fieldsToCheck = [
        { id: 'login', minLength: 5, maxLength: 25, errorMessage: 'Неприпустима довжина логіну (від 5 до 25 символів)' },
        { id: 'pass', minLength: 8, maxLength: 25, errorMessage: 'Неприпустима довжина пароля (від 8 до 25 символів)' }
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
