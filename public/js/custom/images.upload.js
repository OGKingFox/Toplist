let bar = $(document).find("#images-bar");
let progress = bar.find("#images-progress");
let overlay = bar.find("#images-overlay");
let button = $('#imagesUpload');

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
    }

    $('#upstatus').html("&nbsp;");
    button.attr("disabled", "disabled").html("Uploading...");

    $(this).val('');

    $.ajax({
        url: '/servers/images',
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
                    button.removeAttr("disabled").html("Select Images");
                    $('#upstatus').html("Images successfully uploaded.");
                } else {
                    $('#upstatus').html(json.message);
                }
            } catch (err) {
                console.log(err)
            }
        },
    });
});

function setImageProgress(percent, text, failed) {
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