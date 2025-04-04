@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#provider_id,#dth_provider_id").select2({
                width: "100%"
            });
            $('.onlyNumber').on('input', function (event) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            $('.onlyNumberDot').on('input', function (event) {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });
        });

        function checkValidationMobile() {
            $(".parsley-required").html('');
            var error = false;
            var mobile_number = $("#mobile_number").val();
            var service_provider = $("#provider_id").val();
            var amount = $("#amount").val();
            if (!mobile_number) {
                error = true;
                $("#mobile_number_errors").text('Mobile number is required.');

            }
            if (!service_provider) {
                error = true;
                $("#provider_id_errors").text('Service Provider is required.');

            }
            if (!amount) {
                error = true;
                $("#amount_errors").text('Amount is required.');
            }
            if (!error) {
                $("#transaction_process_model").modal('show');
                $("#verify-pass_btn").attr('data-type', 1);
            }
        }

        function checkValidationDTH() {
            $(".parsley-required").html('');
            var error = false;
            var dth_mobile_number = $("#dth_mobile_number").val();
            var service_provider = $("#dth_provider_id").val();
            var amount = $("#dth_amount").val();
            if (!dth_mobile_number) {
                error = true;
                $("#dth_mobile_number_errors").text('Mobile number is required.');

            }
            if (!service_provider) {
                error = true;
                $("#dth_provider_id_errors").text('Service Provider is required.');

            }
            if (!amount) {
                error = true;
                $("#dth_amount_errors").text('Amount is required.');
            }
            if (!error) {
                $("#transaction_process_model").modal('show');
                $("#verify-pass_btn").attr('data-type', 2);
            }
        }

        function verifyPassword(e) {
            var error = false;
            var password = $('#verify_password').val();
            var type = $(e).attr('data-type');
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
                            if (type == 1) {
                                submitMobile();
                            } else {
                                submitDTH();
                            }
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }
        }

        function submitMobile() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (parseFloat(latitude) && parseFloat(longitude)) {
                $(".loader").show();
                $("#mobile_number_errors").text("");
                $("#provider_id_errors").text("");
                $("#amount_errors").text("");
                $.ajax({
                    type: "POST",
                    url: "{{ url('agent/recharge/v1/create') }}",
                    data: $('#rechargeForm').serialize() + "&latitude=" + latitude +
                        "&longitude=" + longitude,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            getWalletBal();
                            $(".receipt_provider_name").text(msg.transaction_details
                                .provider_name);
                            $(".receipt_payid").text(msg.transaction_details.payid);
                            $(".receipt_date").text(msg.transaction_details.date);
                            $(".receipt_number").text(msg.transaction_details.number);
                            $(".receipt_amount").text(msg.transaction_details.amount);
                            $(".receipt_profit").text(msg.transaction_details.profit);
                            $(".receipt_txnid").text(msg.transaction_details.operator_ref);
                            $(".receipt_message").text(msg.message);
                            $("#print_url").attr('href', msg.transaction_details.print_url);
                            $("#mobile_anchor").attr('href', msg.transaction_details
                                .mobile_anchor);
                            $("#recharge_receipt_model").modal('show');
                            $('#rechargeForm').trigger("reset");
                            $('#provider_id').val('').trigger('change');
                        } else if (msg.status == 'validation_error') {
                            $("#mobile_number_errors").text(msg.errors.mobile_number);
                            $("#provider_id_errors").text(msg.errors.provider_id);
                            $("#amount_errors").text(msg.errors.amount);
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            } else {
                getLocation();
                alert('Please allow this site to access your location');
            }
        }

        function submitDTH() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (parseFloat(latitude) && parseFloat(longitude)) {
                $(".loader").show();
                $("#dth_mobile_number_errors").text("");
                $("#dth_provider_id_errors").text("");
                $("#dth_amount_errors").text("");
                $.ajax({
                    type: "POST",
                    url: "{{ url('agent/recharge/v1/create') }}",
                    data: $('#dthRechargeForm').serialize() + "&latitude=" + latitude +
                        "&longitude=" + longitude,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            getWalletBal();
                            $(".receipt_provider_name").text(msg.transaction_details
                                .provider_name);
                            $(".receipt_payid").text(msg.transaction_details.payid);
                            $(".receipt_date").text(msg.transaction_details.date);
                            $(".receipt_number").text(msg.transaction_details.number);
                            $(".receipt_amount").text(msg.transaction_details.amount);
                            $(".receipt_profit").text(msg.transaction_details.profit);
                            $(".receipt_txnid").text(msg.transaction_details.operator_ref);
                            $(".receipt_message").text(msg.message);
                            $("#print_url").attr('href', msg.transaction_details.print_url);
                            $("#mobile_anchor").attr('href', msg.transaction_details
                                .mobile_anchor);
                            $("#recharge_receipt_model").modal('show');
                            $('#dthRechargeForm').trigger("reset");
                            $('#dth_provider_id').val('').trigger('change');
                        } else if (msg.status == 'validation_error') {
                            $("#dth_mobile_number_errors").text(msg.errors.mobile_number);
                            $("#dth_provider_id_errors").text(msg.errors.provider_id);
                            $("#dth_amount_errors").text(msg.errors.amount);
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            } else {
                getLocation();
                alert('Please allow this site to access your location');
            }
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
                                <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#pills-home"
                                   role="tab" aria-controls="pills-home" aria-selected="true">Mobile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile"
                                   role="tab" aria-controls="pills-profile" aria-selected="false">DTH</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                                 aria-labelledby="pills-home-tab">
                                <form action="{{ url('agent/recharge/v1/create') }}" method="post" name="rechargeForm"
                                      id="rechargeForm">
                                    @csrf
                                    <input type="hidden" name="service_type" value="M">
                                    <div class="mb-2">
                                        <label>Mobile Number</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon11">+91</span>
                                            </div>
                                            <input type="text" class="form-control onlyNumber" minlength="10"
                                                   maxlength="10" placeholder="Enter mobile number" id="mobile_number"
                                                   name="mobile_number" aria-describedby="basic-addon11" value="">
                                        </div>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="mobile_number_errors"></li>
                                        </ul>
                                    </div>
                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="name">Service Provider</label>
                                            <select class="form-control select2"
                                                    data-placeholder="Select Service Provider"
                                                    id="provider_id" name="provider_id">
                                                <option value="">Select Service Provider</option>
                                                @foreach ($providers as $p)
                                                    @if ($p->provider_type == 1)
                                                        <option value="{{ $p->id }}">{{ $p->provider_name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="provider_id_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="name">Postpaid/Prepaid</label>
                                            <select class="form-control select2"
                                                    data-placeholder="Select Service Provider"
                                                    id="is_post_paid" name="is_post_paid">
                                                <option value="N">Prepaid</option>
                                                <option value="Y">Postpaid</option>
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="is_post_paid_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label>Amount</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1">&#8377;</span>
                                            </div>
                                            <input type="text" class="form-control onlyNumberDot"
                                                   placeholder="Enter amount" id="amount" name="amount"
                                                   aria-describedby="basic-addon1" value="">
                                        </div>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="amount_errors"></li>
                                        </ul>
                                    </div>
                                    <a href="javascript:;" id="idShowPlans" style="display: none;" class="text-left">View
                                        Popular Recharges</a>
                                    <div class="modal-footer mt-2">

                                        <button class="btn ripple btn-primary" id="idSubmit" type="button"
                                                onclick="checkValidationMobile()">Submit
                                        </button>
                                        <button class="btn ripple btn-secondary" data-dismiss="modal"
                                                type="button">Close
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="pills-profile" role="tabpanel"
                                 aria-labelledby="pills-profile-tab">
                                <form action="{{ url('agent/recharge/v1/create') }}" method="post"
                                      name="dthRechargeForm" id="dthRechargeForm">
                                    @csrf
                                    <input type="hidden" name="service_type" value="D">
                                    <div class="mb-2">
                                        <label>Subscriber Id/Customer Id</label>

                                        <input type="text" class="form-control onlyNumber" minlength="10"
                                               maxlength="11" placeholder="Enter subscriber or customer id"
                                               id="dth_mobile_number" name="mobile_number"
                                               aria-describedby="basic-addon11"
                                               value="">

                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="dth_mobile_number_errors"></li>
                                        </ul>
                                    </div>
                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="name">Service Provider</label>
                                            <select class="form-control select2"
                                                    data-placeholder="Select Service Provider" id="dth_provider_id"
                                                    name="provider_id">
                                                <option value="">Select Service Provider</option>
                                                @foreach ($providers as $p)
                                                    @if ($p->provider_type == 2)
                                                        <option value="{{ $p->id }}">
                                                            {{ $p->provider_name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="dth_provider_id_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label>Amount</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1">&#8377;</span>
                                            </div>
                                            <input type="text" class="form-control onlyNumberDot"
                                                   placeholder="Enter amount" id="dth_amount" name="amount"
                                                   aria-describedby="basic-addon1" value="">
                                        </div>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="dth_amount_errors"></li>
                                        </ul>
                                    </div>
                                    <div class="modal-footer mt-2">
                                        <button class="btn ripple btn-primary" id="idSubmit"
                                                type="button" onclick="checkValidationDTH()">Submit
                                        </button>
                                        <button class="btn ripple btn-secondary" data-dismiss="modal"
                                                type="button">Close
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <div class="modal  show" id="recharge_receipt_model" data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title"><img src="{{ $cdnLink }}{{ $company_logo }}" style="height: 40px;">
                    </h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
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
                    {{-- <a href="{{ request()->fullUrl() }}" class="btn ripple btn-danger">Another Transaction</a> --}}

                </div>
            </div>
        </div>
    </div>
    @include('agent.recharge.prepaid_view_plan_model')
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
                    <button class="btn ripple btn-primary" type="button" id="verify-pass_btn" data-type=""
                            onclick="verifyPassword(this)">Verify
                    </button>

                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
