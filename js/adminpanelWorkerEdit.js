let selectedWorkerId = null;

function selectWorker(workerId, workerDescription) {
    selectedWorkerId = workerId;
    document.getElementById('workerDescription').value = workerDescription;
    $('#workerModal').modal('show');
}

function editWorker() {
    if (selectedWorkerId) {
        let newDescription = document.getElementById('workerDescription').value;
        let formData = new FormData();
        formData.append('worker_id', selectedWorkerId);
        formData.append('worker_description', newDescription);

        fetch('php/edit_worker.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log(response);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            console.log(data);
            // Перезагружаем текущую страницу после успешного обновления
            location.reload();
        })
        .catch(error => {
            console.error('There was an error!', error);
        });
    } else {
        alert('Будь ласка, оберіть працівника для редагування.');
    }
}