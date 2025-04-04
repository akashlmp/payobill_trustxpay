<script type="text/javascript">
    function view_members(id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/view-merchant-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#first_name").val(msg.details.first_name);
                    $("#last_name").val(msg.details.last_name);
                    $("#mobile").val(msg.details.mobile);
                    $("#email").val(msg.details.email);
                    $("#wallet").val(msg.details.wallet);
                    $("#address").val(msg.details.address);
                    $("#city").val(msg.details.city);
                    $("#state_name").val(msg.details.state_name);
                    $("#pin_code").val(msg.details.pin_code);
                    $("#pan_number").val(msg.details.pan_number);
                    $("#gst_number").val(msg.details.gst_number);
                    $("#is_ip_whiltelist").val(msg.details.is_ip_whiltelist);
                    $("#status").val(msg.details.status);
                    $("#merchant_ip").val(msg.details.merchant_ip);
                    $("#server_ip").val(msg.details.server_ip);
                    $("#api_key").val(msg.details.api_key);
                    $("#callback_url").val(msg.details.callback_url);
                    $("#secrete_key").val(msg.details.secrete_key);
                    $("#update_anchor_url").attr('href', msg.details.update_anchor_url);
                    $("#view_member_model").modal('show');
                    $( "#add_balance" ).attr( "data-id", id );
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }


    function download_member() {
        $("#download_btn").hide();
        $("#download_btn_loader").show();
        var token = $("input[name=_token]").val();
        var download_menu_name = $("#download_menu_name").val();
        var download_password = $("#download_password").val();
        var dataString = 'menu_name=' + download_menu_name + '&password=' + download_password + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/download/v1/merchant-users-download')}}",
            data: dataString,
            success: function (msg) {
                $("#download_btn").show();
                $("#download_btn_loader").hide();
                if (msg.status == 'success') {
                    $("#download-label").show();
                    $("#download_link").attr('href', msg.download_link);
                } else if (msg.status == 'validation_error') {
                    $("#download_menu_name_errors").text(msg.errors.menu_name);
                    $("#download_password_errors").text(msg.errors.password);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    $(document).on('click', '.add_balmodel', function () {

        $("#view_member_model").modal('hide');
        $("#add_balance_model").modal('show');
    });

    function add_balance() {

            var token = $("input[name=_token]").val();
            var id = $( "#add_balance" ).attr( "data-id");
            var refno = $("#ref_no").val();
            var amount = $("#view_amount").val();
            var password = $("#view_password").val();
            $("#ref_no_errors").text("");
            $("#view_amount_errors").text("");
            $("#view_password_errors").text("");
                $("#transfer_btn").hide();
                $("#transfer_btn_loader").show();
                var dataString = 'id=' + id + '&refno=' + refno + '&amount=' + amount + '&password=' + password + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/add-merchant-balance')}}",
                    data: dataString,
                    success: function (msg) {
                        $("#transfer_btn").show();
                        $("#transfer_btn_loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else if (msg.status == 'validation_error') {
                            $("#ref_no_errors").text(msg.errors.refno);
                            $("#view_amount_errors").text(msg.errors.amount);
                            $("#view_password_errors").text(msg.errors.password);
                            //$("#dupplicate_transaction_errors").text(msg.errors.dupplicate_transaction);
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
        }
</script>

<div class="modal fade" id="view_member_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="first_name" class="form-control" placeholder="Name" disabled>

                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Last Name</label>
                                <input type="text" id="last_name" class="form-control" placeholder="Last Name" disabled>

                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Mobile Number</label>
                                <input type="text" id="mobile" class="form-control" placeholder="Mobile Number"
                                       disabled>

                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Email Address</label>
                                <input type="text" id="email" class="form-control" placeholder="Email Address" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Wallet Amount</label>
                                <input type="text" id="wallet" class="form-control" placeholder="Wallet Amount"
                                       disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Address</label>
                                <input type="text" id="address" class="form-control" placeholder="Address" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">City</label>
                                <input type="text" id="city" class="form-control" placeholder="City" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">State Name</label>
                                <input type="text" id="state_name" class="form-control" placeholder="State Name"
                                       disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Pincode</label>
                                <input type="text" id="pin_code" class="form-control" placeholder="Pin Code" disabled>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Pan Number</label>
                                <input type="text" id="pan_number" class="form-control" placeholder="Pan Number"
                                       disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">GST Number</label>
                                <input type="text" id="gst_number" class="form-control" placeholder="GST Number"
                                       disabled>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Merchant ip</label>
                                <input type="text" id="merchant_ip" class="form-control" placeholder="Merchant ip"
                                       disabled>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Server ip</label>
                                <input type="text" id="server_ip" class="form-control" placeholder="Server ip"
                                       disabled>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Status</label>
                                <select class="form-control" id="status" disabled>
                                    <option value="1">Active</option>
                                    <option value="0">De Active</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Ip Whiltelist</label>
                                <select class="form-control" id="is_ip_whiltelist" disabled>
                                    <option value="1">On</option>
                                    <option value="0">Off</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">API Key</label>
                                <input type="text" id="api_key" class="form-control" placeholder="API Key"
                                       disabled>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Secrete Key</label>
                                <input type="text" id="secrete_key" class="form-control" placeholder="Secrete Key"
                                       disabled>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Callback URL</label>
                                <input type="text" id="callback_url" class="form-control" placeholder="Callback URL"
                                       disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                @if(Auth::User()->role_id == 2 && $permission_update_member == 1)
                    <a href="" class="btn btn-success" id="update_anchor_url"><i class="fas fa-edit"></i> Edit</a>
                @elseif(Auth::user()->role_id != 2)
                    <a href="" class="btn btn-success" id="update_anchor_url"><i class="fas fa-edit"></i> Edit</a>
                @endif
                <button type="button" class="btn btn-danger add_balmodel" id="add_balance" ><i class="fas fa-add"></i> Add Balance</button>
            </div>
        </div>
    </div>
</div>


    <div class="modal  show" id="reset_password_model" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Reset Password</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="reset_user_id">

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">User Name</label>
                                    <input type="text" id="reset_name" class="form-control" placeholder="Your Name"
                                           disabled>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Your Login Password</label>
                                    <input type="password" id="reset_admin_password" class="form-control"
                                           placeholder="Login Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="reset_admin_password_errors"></li>
                                    </ul>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Password</label>
                                    <input type="password" id="reset_password" class="form-control"
                                           placeholder="Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="reset_password_errors"></li>
                                    </ul>

                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="alert alert-outline-danger" role="alert">
                        <strong> Password will be send to customer register mobile number</strong>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="pass_reset_btn"
                            onclick="password_resent_now()">Reset Now
                    </button>
                    <button class="btn btn-primary" type="button" id="pass_reset_btn_loader" disabled
                            style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                         aria-hidden="true"></span> Loading...
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>


    <style>
        .modal-dialog-slideout {
            min-height: 100%;
            margin: 0 0 0 auto;
            background: #fff;
        }

        .modal.fade .modal-dialog.modal-dialog-slideout {
            -webkit-transform: translate(100%, 0) scale(1);
            transform: translate(100%, 0) scale(1);
        }

        .modal.fade.show .modal-dialog.modal-dialog-slideout {
            -webkit-transform: translate(0, 0);
            transform: translate(0, 0);
            display: flex;
            align-items: stretch;
            -webkit-box-align: stretch;
            height: 100%;
        }

        .modal.fade.show .modal-dialog.modal-dialog-slideout .modal-body {
            overflow-y: auto;
            overflow-x: hidden;
        }

        .modal-dialog-slideout .modal-content {
            border: 0;
        }

        .modal-dialog-slideout .modal-header, .modal-dialog-slideout .modal-footer {
            height: 69px;
            display: block;
        }

        .modal-dialog-slideout .modal-header h5 {
            float: left;
        }
    </style>

<div class="modal  show" id="add_balance_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Add Balance</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <input type="hidden" id="view_id">
                    <input type="hidden" id="trasnfer_millisecond">

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Ref Number</label>
                                <input type="text" id="ref_no" class="form-control" placeholder="Ref no">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="ref_no_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="text" id="view_amount" class="form-control" placeholder="Amount">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_amount_errors"></li>
                                    <li class="parsley-required" id="dupplicate_transaction_errors"></li>
                                </ul>
                                <strong style="color: red;" id="amountToWordsText"></strong>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Login Password</label>
                                <input type="password" id="view_password" class="form-control" placeholder="Login Password">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_password_errors"></li>
                                </ul>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="transfer_btn" onclick="add_balance()">Submit</button>
                <button class="btn btn-primary" type="button"  id="transfer_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


    <div class="modal  show" id="member_download_model" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Download Data</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Member Type</label>
                                    <input type="text" id="download_menu_name" class="form-control"
                                           value="{{ $page_title }}" readonly>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Your Login Password</label>
                                    <input type="password" id="download_password" class="form-control"
                                           placeholder="Login Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="download_password_errors"></li>
                                    </ul>

                                </div>
                            </div>


                        </div>

                    </div>

                    <div class="alert alert-outline-danger" role="alert" id="download-label" style="display: none;">
                        <strong> Download File : <a href="" target="_blank" id="download_link">Click Here</a> </strong>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="download_btn" onclick="download_member()">
                        Verify And Download
                    </button>
                    <button class="btn btn-primary" type="button" id="download_btn_loader" disabled
                            style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
