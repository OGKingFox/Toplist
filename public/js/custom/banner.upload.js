$(document).ready(function() {
    let status = $('#uploadStatus');

    let bar = $(document).find("#banner-bar");
    let progress = bar.find("#banner-progress");
    let overlay = bar.find("#banner-overlay");

    $(document).on("click", '#file-select', function(event) {
        var form = $(this).parents('form:first');
        form.find("#image").click();
    });

    $("input[id='image']").change(function(event) {
        event.preventDefault();
        let form = $('#uploadForm')[0];
        let data = new FormData(form);
        let size = this.files[0].size;

        if (size > 3145728) {
            setProgressBar(100, "File size can not exceed 3MB", true);
            return;
        }

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
                                setProgressBar(100, "Processing Image...", false);
                            } else {
                                setProgressBar(percent, percent + "%", false);
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
                        setProgressBar(100, "Upload Successful!", false);
                        $("#banner").css("background", 'url('+json.message+') top center #efefef');
                    } else {
                        setProgressBar(100, json.message, true);
                    }
                } catch (err) {
                    console.log(err);
                    console.log(data);
                }
            },
            error: function (e) {
                console.log("ERROR : ", e);
            }
        });
    });

    function setProgressBar(percent, text, failed) {
        bar.removeClass("invisible");

        progress.css("width", percent+"%");
        overlay.html(text);

        if (failed) {
            progress.addClass("bg-danger");
        } else {
            if (progress.hasClass("bg-danger")) {
                progress.removeClass("bg-danger");
                if (percent === 100) {
                    progress.addClass("bg-success");
                }
            }
        }
    }
});