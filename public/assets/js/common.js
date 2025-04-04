function swalSuccessReload(message = "") {
    swal({
        title: "Success",
        text: message,
        type: "success",
        showCancelButton: false
    }, function () {
        location.reload(1);
    });
}
