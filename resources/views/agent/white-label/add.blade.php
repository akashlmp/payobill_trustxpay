<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <form method="POST" enctype="multipart/form-data" id="fileUploadForm">
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Bank Name</label>
                                <input type="text" id="bank_name" name="bank_name" class="form-control"
                                       placeholder="Bank Name">
                                <ul class="parsley-errors-list filled">
                                    <li class="bank_name-required error-text" id="bank_name_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Payee Name</label>
                                <input type="text" id="payee_name" name="payee_name" class="form-control"
                                       placeholder="Payee name">
                                <ul class="parsley-errors-list filled">
                                    <li class="payee_name-required error-text" id="payee_name_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Account number</label>
                                <input type="text" id="account_number" name="account_number" class="form-control"
                                       placeholder="Account number">
                                <ul class="parsley-errors-list filled">
                                    <li class="account_number-required error-text" id="account_number_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">IFSC Code</label>
                                <input type="text" id="ifsc_code" name="ifsc_code" class="form-control"
                                       placeholder="IFSC Code">
                                <ul class="parsley-errors-list filled">
                                    <li class="ifsc_code-required error-text" id="ifsc_code_errors"></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Bank proof/Cancel check</label>
                                <input type="file" id="bank_proof" class="form-control" name="bank_proof">
                                <ul class="parsley-errors-list filled">
                                    <li class="bank_proof-required error-text" id="bank_proof_errors"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" id="btnSubmit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {

        $("#btnSubmit").click(function (event) {

            //stop submit the form, we will post it manually.
            event.preventDefault();
            // Get form
            var form = $('#fileUploadForm')[0];

            // Create an FormData object
            var data = new FormData(form);

            // If you want to add an extra field for the FormData
            var token = $("input[name=_token]").val();
            data.append("_token", token);

            // disabled the submit button
            $("#btnSubmit").prop("disabled", true);

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: "{{url('agent/white-label-add')}}",
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                success: function (msg) {
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                        $("#account_number_errors").text(msg.errors.account_number);
                        $("#payee_name_errors").text(msg.errors.payee_name);
                        $("#bank_name_errors").text(msg.errors.bank_name);
                        $("#bank_proof_errors").text(msg.errors.bank_proof);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                    $("#btnSubmit").prop("disabled", false);
                },
                error: function (e) {
                    swal("Faild", msg.responseText, "error");
                    $("#btnSubmit").prop("disabled", false);

                }
            });
        });
    });
</script>
