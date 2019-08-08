$(document).ready(function() {
    let locked = false;

    $(document).on('click', 'button[id="delete"]', function(event) {
        if (locked) {
            return;
        }

        locked = true;

        let image  = $(this).data("image");
        let server = $(this).data("server");

        var listItem = $(this).parents('.list-group-item:first');

        $.post('/servers/removeimage', {
            image: image,
            server_id: server
        }, function(response) {
            try {
                let json = JSON.parse(response);

                if (json.success) {
                    listItem.remove();
                }

                Swal.fire({
                    title: json.success ? 'Deleted!' : 'Error',
                    html: json.message,
                    type: json.error ? 'error' : 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    onBeforeOpen: () => {
                        timerInterval = setInterval(() => {}, 100)
                    },
                    onClose: () => {
                        clearInterval(timerInterval);
                    }
                });
            } catch (err) {
                console.log(err);
            }

            locked = false;
        });
    });
});