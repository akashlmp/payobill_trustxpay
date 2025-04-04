<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-body">
            <div class="form-body">
                <div class="row">
                    <p>Are you sure want to delete?</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" onclick="submitForm({{$id}})" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>
<script>
    function submitForm(id) {
        $('.error-text').text('');
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id+ '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('merchant/white-label-delete')}}",
            data: dataString,
            success: function (msg) {
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                    setTimeout(function () {
                        location.reload(1);
                    }, 1000);
                } else {
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }
</script>
