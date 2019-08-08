$(document).ready(function() {
    let button = $('#imagesUpload');
    let statusMsg = $('#upstatus');

    $(document).on("click", '#imagesUpload', function(event) {
        console.log("clicked");

        let form = $(this).parents('form:first');
        form.find("#images").click();
    });

    $("input[id='images']").change(function(event) {
        event.preventDefault();

        let data = new FormData();

        if (this.files.length > 10) {
            console.log("too many files!");
            return;
        }

        data.append("server_id", $(this).data("server"));

        let count = 0;

        for (let i = 0; i < this.files.length; i++) {
            let file = this.files[i];

            if (file.size > 3145728) {
                console.log("Skipped file "+file.name+": Too large.");
                continue;
            }

            if (file.type !== 'image/png' && file.type !== 'image/gif' && file.type !== 'image/jpeg') {
                console.log("Skipped file "+file.name+": Wrong type.");
                continue;
            }

            data.append(file.name, file);
            count++;
        }

        if (count === 0) {
            statusMsg.html("No valid images to upload.");
            return;
        }

        statusMsg.html("&nbsp;");
        button.attr("disabled", "disabled").html("Uploading...");

        let input = $(this);
        input.val('');

        $.ajax({
            url: '/toplist/servers/images',
            type: 'post',
            data: data,
            contentType: false,
            processData: false,
            cache: false,
            timeout: 600000,
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            let percent = Math.ceil((e.loaded * 100) / e.total);
                            if (percent === 100) {
                                button.html("Processing images");
                            } else {
                                button.html("Uploading - "+percent+"%");
                            }
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function(response) {
                console.log(response);
                try {
                    let json = JSON.parse(response);

                    if (json.success) {
                        let server_id = json.server_id;
                        let images = json.message;
                        for (let i = 0; i < images.length; i++) {
                            let image = images[i];
                            $('#imagelist').append('' +
                                '<div class="list-group-item d-flex justify-content-between align-items-center small">'+
                                '<a href="'+image+'" target="_blank">'+image+'</a>'+
                                '<button id="delete" class="btn btn-link btn-sm" data-image="'+image+'" data-server="'+server_id+'"> ' +
                                '<i class="fal fa-times text-danger"></i> ' +
                                '</button>'+
                                '</div>');
                        }

                        statusMsg.html("Images successfully uploaded.");
                    } else {
                        statusMsg.html(json.message);
                    }

                    button.removeAttr("disabled").html("Select Images");
                } catch (err) {
                    button.removeAttr("disabled").html("Select Images");
                    console.log(err)
                }

                input.val('');
            },
            error: function (e) {
                console.log("ERROR : ", e);
                input.val('');
            }
        });
    });
});