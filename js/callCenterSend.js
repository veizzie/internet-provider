$(document).ready(function() {
    $('#problemForm').on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: 'php/submit_problem.php',
            data: formData,
            dataType: 'json', // Добавлено указание на ожидаемый тип данных
            success: function(response) {
                alert(response.message); // Выводим сообщение из ответа
                $('#problemForm')[0].reset();
                $('#problemModal').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // Выводим текст ошибки в консоль
                alert('Виникла помилка при відправці заявки. Спробуйте пізніше.');
            }
        });
    });
});