$(document).ready(function() {
    let status = $('#bannerstatus');
    let button = $('#file-select');

    $(document).on("click", '#file-select', function(event) {
        var form = $(this).parents('form:first');
        form.find("#image").click();
    });

    $("input[id='image']").change(function(event) {
        event.preventDefault();
        let data = new FormData($('#uploadForm')[0]);
        let size = this.files[0].size;

        if (size > 3145728) {
            status.html("File size can not exceed 3MB");
            return;
        }

        status.html("&nbsp;");
        button.attr("disabled", "disabled").html("Uploading...");
        $(this).val('');

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: "/toplist/servers/banner/",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            let percent = Math.ceil((e.loaded * 100) / e.total);

                            if (percent === 100) {
                                button.html("Processing Image");
                            } else {
                                button.html("Uploading "+percent+"%");
                            }
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                try {
                    var json = JSON.parse(data);

                    if (json.success) {
                        status.html("Upload Successful!");
                        $('#server-banner').attr('src', json.message);
                    } else {
                        status.html(json.message);
                    }

                    button.removeAttr("disabled").html("Select Image");
                } catch (err) {
                    button.removeAttr("disabled").html("Select Image");
                    console.log(err);
                    console.log(data);
                }

                $(this).val('');
            },
            error: function (e) {
                console.log("ERROR : ", e);
                $(this).val('');
            }
        });
    });
});