@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $(".select2").select2();
        });

        function checkValidationPopup() {
            $(".parsley-required").html('');
            var error = false;
            var mobile_number = $("#mobile_number").val();
            var type = $("#type").val();
            var providerId = 'service_provider';
            if (type == 2) {
                providerId = 'service_provider_dth'
            }
            var service_provider = $("#" + providerId).val();
            var amount = $("#amount").val();
            if (!mobile_number) {
                error = true;
                $("#mobile_number_errors").text('Mobile number is required.');

            }
            if (!service_provider) {
                error = true;
                $("#" + providerId + "_errors").text('Service Provider is required.');

            }
            if (!amount) {
                error = true;
                $("#amount_errors").text('Amount is required.');
            }
            if (!error) {
                $("#transaction_process_model").modal('show');
            }
        }

        function verifyPassword() {
            var error = false;
            var password = $('#verify_password').val();
            if (!password) {
                error = true;
                $('#verify_password_errors').text('Password is required');
            }
            if (!error) {
                var token = $("input[name=_token]").val();
                $.ajax({
                    type: "POST",
                    url: "{{url('verify-password')}}",
                    data: {
                        password: password,
                        _token: token,
                    },
                    success: function (msg) {
                        if (msg.status == 'success') {
                            $("#transaction_process_model").modal('hide');
                            submitRecharge();
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }
        }

        function submitRecharge() {
            $(".loader").show();
            $(".parsley-required").html('');
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var type = $("#type").val();
            var providerId = 'service_provider';
            if (type == 2) {
                providerId = 'service_provider_dth'
            }
            var service_provider = $("#" + providerId).val();
            var amount = $("#amount").val();
            var dataString = 'mobile_number=' + mobile_number + '&_token=' + token + '&service_provider=' + service_provider+ '&mode=WEB&amount=' + amount;
            $.ajax({
                type: "POST",
                url: "{{url('agent/recharge-2/v1/store-recharge')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'error') {
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#" + providerId + "_errors").text(msg.errors.service_provider);
                        $("#amount_errors").text(msg.errors.amount);
                    } else if (msg.status == 'success') {
                        $(".receipt_provider_name").text(msg.transaction_details.provider_name);
                        $(".receipt_payid").text(msg.transaction_details.payid);
                        $(".receipt_date").text(msg.transaction_details.date);
                        $(".receipt_number").text(msg.transaction_details.number);
                        $(".receipt_amount").text(msg.transaction_details.amount);
                        $(".receipt_profit").text(msg.transaction_details.profit);
                        $(".receipt_txnid").text(msg.transaction_details.operator_ref);
                        $(".receipt_message").text(msg.message);
                        $("#print_url").attr('href', msg.transaction_details.print_url);
                        $("#mobile_anchor").attr('href', msg.transaction_details.mobile_anchor);
                        $("#receipt_model").modal('show');
                    } else if (msg.status == 'failure') {
                        swal("Failed", msg.message, "error");
                    } else {
                    }
                }
            });
        }

        function changeServiceProvider(type) {
            $('#dthDiv').addClass('d-none');
            $('#mobileDiv').addClass('d-none');
            if (type == 1) {
                $('#mobileDiv').removeClass('d-none');
            } else {
                $('#dthDiv').removeClass('d-none');
            }
            $('#type').val(type);
            $(".select2").select2();
        }

        function closeModal() {
            $("#mobile_number").val('');
            changeServiceProvider($("#type").val());
            $("#service_provider").val('');
            $("#service_provider_dth").val('');
            $("#amount").val('');
            $(".select2").select2();
        }
    </script>
    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>

                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-home-tab" data-toggle="pill"
                                   href="javascript:void(0);" onclick="changeServiceProvider(1)"
                                   role="tab" aria-controls="pills-home" aria-selected="true">Mobile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="javascript:void(0);"
                                   onclick="changeServiceProvider(2)"
                                   role="tab" aria-controls="pills-profile" aria-selected="false">DTH</a>
                            </li>
                        </ul>
                        <div class="mb-2">
                            <label>Mobile Number</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">+91</span>
                                </div>
                                <input type="hidden" id="type" value="1">
                                <input type="text" class="form-control" placeholder="Mobile Number" data-id="1"
                                       id="mobile_number" aria-label="Username" aria-describedby="basic-addon1">
                            </div>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="mobile_number_errors"></li>
                            </ul>
                        </div>
                        <div class="mb-2" id="mobileDiv">
                            <div class="form-group">
                                <label for="service_provider">Service Provider</label>
                                <select class="form-control select2" id="service_provider">
                                    <option value="">Service Provider</option>
                                    @foreach($providerList as $key=>$value)
                                        @if($value['provider_type'] == 1)
                                            <option
                                                value="{{$value['provider_code']}}">{{$value['provider_name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="service_provider_errors"></li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-2 d-none" id="dthDiv">
                            <div class="form-group">
                                <label for="service_provider_dth">Service Provider</label>
                                <select class="form-control select2" id="service_provider_dth">
                                    <option value="">Service Provider</option>
                                    @foreach($providerList as $key=>$value)
                                        @if($value['provider_type'] == 2)
                                            <option
                                                value="{{$value['provider_code']}}">{{$value['provider_name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="service_provider_dth_errors"></li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>Amount</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">&#8377;</span>
                                </div>
                                <input type="text" class="form-control" placeholder="Amount"
                                       id="amount" aria-label="Amount" aria-describedby="basic-addon1">
                            </div>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="amount_errors"></li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="checkValidationPopup()">Submit
                        </button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    <div class="modal  show" id="receipt_model" data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title"><img src="{{$cdnLink}}{{ $company_logo }}" style="height: 40px;"></h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" onclick="closeModal()" type="button">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="task-stat pb-0">
                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Provider Name:</div>
                                </div>
                                <span class="float-right ml-auto receipt_provider_name"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Order Id : <span class="receipt_payid"></span></div>
                                </div>
                                <span class="float-right ml-auto">Date : <span class="receipt_date"></span></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Number : <span class="receipt_number"></span></div>
                                </div>
                                <span class="float-right ml-auto">Amount: <span class="receipt_amount"></span></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Profit : <span class="receipt_profit"></span></div>
                                </div>
                                <span class="float-right ml-auto">Txn Id : <span class="receipt_txnid"></span></span>
                            </div>

                        </div>
                    </div>

                    <div class="alert alert-success" role="alert">
                        <span class="receipt_message"></span>
                    </div>

                </div>
                <div class="modal-footer">
                    <a href="" class="btn ripple btn-primary" target="_blank" id="print_url">Print</a>
                    <a href="" class="btn ripple btn-primary" target="_blank" id="mobile_anchor">Mobile Print</a>
                    <a href="{{ request()->fullUrl() }}" class="btn ripple btn-danger">Another Transaction</a>

                </div>
            </div>
        </div>
    </div>

    <div class="modal show" id="transaction_process_model" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Transaction Password</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Your Transaction Password</label>
                                    <input type="password" id="verify_password" class="form-control"
                                           placeholder="Transaction Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="verify_password_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="verify-pass_btn"
                            onclick="verifyPassword()">Verify
                    </button>

                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
