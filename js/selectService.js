let selectedServiceId;

function showConfirmationModal(serviceId) {
    selectedServiceId = serviceId;
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    confirmationModal.show();
}

function confirmSubscription() {
    // Отправка запроса на сервер с выбранным сервисом (selectedServiceId)
    console.log('Підтвердження підписки на сервіс з ID:', selectedServiceId);
    // Можно добавить AJAX запрос для отправки данных на сервер
    window.location.href = 'php/subscribe.php?service_id=' + selectedServiceId;
    const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
    confirmationModal.hide();
    alert('Ви успішно вибрали тариф! З вами скоро зв\'яжуться.');
}