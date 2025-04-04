@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">

        $(document).ready(function () {
            $("#bank_id").select2();
        });

        function getCustomer() {
            $(".loader").show();

            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            var dataString = 'mobile_number=' + mobile_number + '&_token=' + token + '&latitude=' + latitude + '&longitude=' + longitude;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v1/get-iServeU-customer')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    $("#beneficiary-details-label").hide();
                    if (msg.status == 'error') {
                        $("#sender-details-label").hide();
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                    } else if (msg.status == 'success') {
                        if (msg.data.KYCTypeFlag == false) {
                            $(".add-sender-otp-label").show();
                            $("#addSederBtn").attr('onclick', 'confirmSender()');
                            $("#is_otp_send").val(1);
                            $("#addSederBtn").text('Confirm Sender');
                            $("#sender-details-label").hide();
                            $("#add-sender-model").modal('show');
                        } else {
                            $("#sender_name").val(msg.data.name);
                            $(".name").text(msg.data.name);
                            $(".mobile_number").text(msg.data.mobile_number);
                            $(".total_limit").text(msg.data.total_limit);
                            $("#sender-details-label").show();
                            $("#beneficiary-details-label").show();
                            $("#add-sender-model").modal('hide');
                            getAllBeneficiary();
                        }
                    } else {
                        $("#sender-details-label").hide();
                    }
                }
            });
        }

        function confirmSender() {
            $("#addSederBtn").hide();
            $("#addSederBtn_loader").show();
            var mobile_number = $("#mobile_number").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            var form = $('#fileAddSender')[0];
            var data = new FormData(form);

            // If you want to add an extra field for the FormData
            var token = $("input[name=_token]").val();
            data.append("_token", token);
            data.append("latitude", latitude);
            data.append("longitude", longitude);
            data.append("mobile_number", mobile_number);
            data.append("is_otp_send", $('#is_otp_send').val());
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: "{{url('agent/money/v1/confirm-sender-iServeU')}}",
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                success: function (msg) {
                    $("#addSederBtn").show();
                    $("#addSederBtn_loader").hide();
                    if (msg.status == 'error') {
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#pincode_errors").text(msg.errors.pincode);
                        $("#state_errors").text(msg.errors.state);
                        $("#address_errors").text(msg.errors.address);
                        $("#otp_errors").text(msg.errors.otp);
                        $("#ovdData_errors").text(msg.errors.ovdData);
                        $("#ovdType_errors").text(msg.errors.ovdType);
                    } else if (msg.status == 'success') {
                        if (msg.KYCTypeFlag && $('#is_otp_send').val() == 2) {
                            $(".add-sender-otp-label").hide();
                            swal("Success", msg.message, "success");
                            $('#formDiv').removeClass('d-none');
                            $('#otpDiv').addClass('d-none');
                            $("#fileAddSender input").val('');
                            $('#is_otp_send').val(1);
                            getCustomer();
                        } else {
                            if ($('#is_otp_send').val() == 1 && msg.statusCode == 0) {
                                $('#formDiv').addClass('d-none');
                                $('#otpDiv').removeClass('d-none');
                                $('#is_otp_send').val(2);
                                swal("Success", msg.message, "success");
                            }
                        }
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function getAllBeneficiary() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var sender_name = $("#sender_name").val();
            var dataString = 'mobile_number=' + mobile_number + '&sender_name=' + sender_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v1/get-iServeU-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    var html = "";
                    if (msg.status == 'success') {
                        $(".beneficiary_list").show('');
                        var beneficiaries = msg.beneficiaries;
                        if (beneficiaries.length > 0) {
                            for (var key in beneficiaries) {

                                if (beneficiaries[key].is_verify == 1) {
                                    var is_verify = '<i class="fas fa-check-square" style="color: green;"></i>';
                                } else {
                                    var is_verify = '<i class="fas fa-times" style="color: red;"></i>';
                                }

                                if (beneficiaries[key].status_id == 1) {
                                    var status_id = '<button type="button" class="btn btn-success btn-sm" onclick="viewTransfer(' + beneficiaries[key].id + ')">Transfer</button>';
                                    //var status_id = '<button type="button" class="btn btn-success btn-sm" onclick="passwordPopup(this)" data-id="' + beneficiaries[key].id + '">Transfer</button>';
                                } else {
                                    var status_id = '<button type="button" class="btn btn-warning btn-sm" onclick="activateBeneficiary(' + beneficiaries[key].id + ')">Activate</button>';
                                }

                                html += '<input type="hidden" value="' + beneficiaries[key].beneficiary_id + '" id="beneficiaryId_' + beneficiaries[key].id + '">';
                                html += '<input type="hidden" value="' + beneficiaries[key].account_number + '" id="accountNumber_' + beneficiaries[key].id + '">';
                                html += '<input type="hidden" value="' + beneficiaries[key].ifsc_code + '" id="ifscCode_' + beneficiaries[key].id + '">';
                                html += '<input type="hidden" value="' + beneficiaries[key].beneficiary_name + '" id="beneficiaryName_' + beneficiaries[key].id + '">';
                                html += '<input type="hidden" value="' + beneficiaries[key].bank_name + '" id="bankName_' + beneficiaries[key].id + '">';

                                html += "<tr>";
                                html += '<td>' + beneficiaries[key].beneficiary_name + ' ' + is_verify + '</td>';
                                html += '<td>' + beneficiaries[key].ifsc_code + '</td>';
                                html += '<td>' + beneficiaries[key].bank_name + ' - ' + beneficiaries[key].account_number + ' </td>';
                                html += '<td>' + status_id + '</td>';
                                html += '<td><button type="button" class="btn btn-danger btn-sm" onclick="deleteBeneficiary(' + beneficiaries[key].id + ')">Delete</button></td>';
                                html += "</tr>";
                            }
                        } else {
                            html += '<tr>';
                            html += '<td colspan="5" style="text-align: center">No Data Available.</td>';
                            html += '</tr>';
                        }
                        $(".beneficiary_list").html(html);
                    } else {
                        html += '<tr>';
                        html += '<td colspan="5" style="text-align: center">No Data Available.</td>';
                        html += '</tr>';
                    }
                    $(".beneficiary_list").html(html);
                }
            });
        }

        function deleteBeneficiary(id) {
            var result = confirm("Are you sure you want to delete this beneficiary?");
            if (result) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var beneficiary_id = $("#beneficiaryId_" + id).val();
                var mobile_number = $("#mobile_number").val();
                var dataString = 'mobile_number=' + mobile_number + '&beneficiary_id=' + beneficiary_id + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/money/v1/delete-beneficiary-iserveU')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            getCustomer();
                        } else if (msg.status == 'pending') {
                            $("#beneficiary-delete-otp-model").modal('show');
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }
        }

        function getIfscCode() {
            var token = $("input[name=_token]").val();
            var bank_id = $("#bank_id").val();
            var dataString = 'bank_id=' + bank_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v1/get-ifsc-code-iserveU')}}",
                data: dataString,
                success: function (msg) {
                    if (msg.status == 'success') {
                        $("#ifsc_code").val(msg.data.ifsc);
                    }
                }
            });
        }

        function accountVerify() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var mobile_number = $("#mobile_number").val();
                var bank_id = $("#bank_id").val();
                var ifsc_code = $("#ifsc_code").val();
                var account_number = $("#account_number").val();
                var beneficiary_name = $("#beneficiary_name").val();
                var externalRefNumber = $("#externalRefNumber").val();
                var address = $("#address_bene").val();
                var pincode = $("#pincode_bene").val();
                var otp = $("#senderOtp").val();
                var dataString = 'mobile_number=' + mobile_number + '&bank_id=' + bank_id + '&externalRefNumber=' + externalRefNumber + '&otp=' + otp + '&beneficiary_name=' + beneficiary_name + '&address=' + address + '&pincode=' + pincode + '&ifsc_code=' + ifsc_code + '&account_number=' + account_number + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/money/v1/account-verify-iserveU')}}",
                    data: dataString,
                    success: function (msg) {
                        getWalletBal();
                        $(".loader").hide();
                        if (msg.status == 'error') {
                            $("#bank_id_errors").text(msg.errors.bank_id);
                            $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                            $("#account_number_errors").text(msg.errors.account_number);
                            $("#pincode_bene_errors").text(msg.errors.pincode);
                            $("#address_bene_errors").text(msg.errors.address);
                            $("#beneficiary_name_errors").text(msg.errors.beneficiary_name);
                            $("#senderOtp_errors").text(msg.errors.otp);
                        } else if (msg.status == 'success') {
                            $("#beneficiary_name").val(msg.data.beneficiary_name);
                            $("#verify_status").val(1);
                            $('.parsley-required').html('');
                            $('#AddBenefe input').val('');
                            $("#externalRefNumber").val('');
                            getCustomer();
                        } else if (msg.status == 'pending') {
                            swal("Pending", msg.message, "warning");
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

        function viewTransfer(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var beneficiary_id = $("#beneficiaryId_" + id).val();
            var account_number = $("#accountNumber_" + id).val();
            var ifsc_code = $("#ifscCode_" + id).val();
            var beneficiary_name = $("#beneficiaryName_" + id).val();
            var bank_name = $("#bankName_" + id).val();
            var dataString = 'mobile_number=' + mobile_number + '&beneficiary_id=' + beneficiary_id + '&account_number=' + account_number + '&account_number=' + account_number + '&ifsc_code=' + ifsc_code + '&beneficiary_name=' + beneficiary_name + '&bank_name=' + bank_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v1/view-account-transfer-iserveU')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        generate_millisecond();
                        $(".account_number").text(msg.data.account_number);
                        $(".ifsc_code").text(msg.data.ifsc_code);
                        $(".beneficiary_name").text(msg.data.beneficiary_name);
                        $(".bank_name").text(msg.data.bank_name);
                        // input details
                        $("#amount").val('');
                        $("#transfer_account_number").val(msg.data.account_number);
                        $("#transfer_ifsc_code").val(msg.data.ifsc_code);
                        $("#transfer_beneficiary_id").val(msg.data.beneficiary_id);
                        $("#amount").attr('placeholder', msg.data.placeholder);
                        $("#view-transaction-confirm-model").modal('show');
                        $(".transactionChargesListDiv").hide();
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }


        function transferNow() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude) {
                $("#transferBtn").hide();
                $("#transferBtn_loader").show();
                var token = $("input[name=_token]").val();
                var mobile_number = $("#mobile_number").val();
                var beneficiary_id = $("#transfer_beneficiary_id").val();
                var account_number = $("#transfer_account_number").val();
                //var ovdData = $("#ovdDataAadhaar").val();
                var ifsc_code = $("#transfer_ifsc_code").val();
                var is_transfer_otp = $("#is_transfer_otp").val();
                var channel_id = $("#channel_id").val();
                var amount = $("#amount").val();
                var transaction_pin = $("#transaction_pin").val();
                var transfer_otp = $("#transfer_otp").val();
                var externalRefNumber = $("#externalRefNumber").val();
                var transactionMiliseconds = $("#transactionMiliseconds").val();
                var dataString = 'mobile_number=' + mobile_number + '&beneficiary_id=' + beneficiary_id + '&externalRefNumber=' + externalRefNumber + '&is_transfer_otp=' + is_transfer_otp + '&transfer_otp=' + transfer_otp + '&account_number=' + account_number + '&ifsc_code=' + ifsc_code + '&channel_id=' + channel_id + '&amount=' + amount + '&transaction_pin=' + transaction_pin + '&dupplicate_transaction=' + transactionMiliseconds + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/money/v1/transfer-now-iserveU')}}",
                    data: dataString,
                    success: function (msg) {
                        getWalletBal();
                        $("#transferBtn").show();
                        $("#transferBtn_loader").hide();
                        if (msg.status == 'success') {
                            if (msg.is_send_otp) {
                                $('#transactionDiv').addClass('d-none');
                                $('#otpTransactionDiv').removeClass('d-none');
                                $("#is_transfer_otp").val(2);
                                $("#externalRefNumber").val(msg.externalRefNumber);
                                swal("Success", msg.message, "success");
                            } else {
                                $("#externalRefNumber").val('');
                                $('#transactionDiv').removeClass('d-none');
                                $('#otpTransactionDiv').addClass('d-none');
                                $('#transfer_otp').val('');
                                $("#is_transfer_otp").val(1);
                                $("#view-transaction-confirm-model").modal('hide');
                                $("#amount").val('');
                                $("#print_url").attr('href', msg.benedetails.print_url);
                                $("#thermal_print").attr('href', msg.benedetails.thermal_print);
                                $(".receipt_beneficiary_name").html(msg.benedetails.beneficiary_name);
                                $(".receipt_account_number").html(msg.benedetails.account_number);
                                $(".receipt_bank_name").html(msg.benedetails.bank_name);
                                $(".receipt_ifsc").html(msg.benedetails.ifsc);
                                $(".receipt_remiter_name").html(msg.benedetails.remiter_name);
                                $(".receipt_remiter_number").html(msg.benedetails.remiter_number);
                                $(".receipt_payment_mode").html(msg.benedetails.payment_mode);
                                $(".receipt_full_amount").html(msg.benedetails.full_amount);
                                var html = "";
                                var re = msg.reports;
                                for (var key in re) {
                                    var status = re[key].status;
                                    if (status == 'Success') {
                                        var status_call = '<span class="badge badge-success">Success</span>';
                                    } else if (status == 'Failure' || status == 'Failed') {
                                        var status_call = '<span class="badge badge-danger">Failure</span>';
                                    } else if (status == 'Pending') {
                                        var status_call = '<span class="badge badge-warning">Pending</span>';
                                    }
                                    if (typeof re[key].failure_reason != 'undefined' && re[key].failure_reason != 'null' && re[key].failure_reason != null) {
                                        if (re[key].failure_reason != '') {
                                            status_call += " (Failure reason :   " + re[key].failure_reason + " )";
                                        }
                                    }
                                    html += "<tr>";
                                    html += "<td>" + re[key].report_id + "</td>";
                                    html += "<td>" + re[key].utr_number + "</td>";
                                    html += "<td>" + re[key].amount + "</td>";
                                    html += "<td>" + re[key].charges + "</td>";
                                    html += "<td>" + status_call + "</td>";
                                    html += "</tr>";
                                }
                                $("#receipt_html").html(html);
                                $("#transaction-receipt-model").modal('show');
                            }
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

        function generate_millisecond() {
            var id = 1;
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/generate-millisecond')}}",
                data: dataString,
                success: function (msg) {
                    $("#transactionMiliseconds").val(msg.miliseconds);
                }
            });
        }

        function getCharges() {
            $("#getChargesBtn").hide();
            $("#getChargesBtn_loader").show();
            var token = $("input[name=_token]").val();
            var amount = $("#amount").val();
            var dataString = 'amount=' + amount + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v1/get-transaction-charges')}}",
                data: dataString,
                success: function (msg) {
                    $("#getChargesBtn").show();
                    $("#getChargesBtn_loader").hide();
                    if (msg.status == 'success') {
                        var list = msg.list;
                        var html = "";
                        for (var key in list) {
                            html += '<div class="d-flex tasks"><div class="mb-0"><div class="h6 fs-15 mb-0">' + list[key].amount + '</div></div><span class="float-right ml-auto">' + list[key].charges + '</span><span class="float-right ml-auto">' + list[key].total_amount + '</span></div>';
                        }
                        $(".transactionChargesListDiv").show();
                        $(".transactionChargesList").html(html);
                        $("#get_charges_model").modal('show');
                    } else if (msg.status == 'validation_error') {
                        $("#amount_errors").text(msg.errors.amount);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function sendOtpToSender() {
            $("#accountVerifyBene").prop('disabled', true);
            $("#sendOtpToBtn").hide();
            $("#sendOtpToBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            var dataString = 'mobile_number=' + mobile_number + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v1/iserveU-send-otp')}}",
                data: dataString,
                success: function (msg) {
                    $("#sendOtpToBtn").show();
                    $("#sendOtpToBtn_loader").hide();
                    if (msg.status == 'success') {
                        $("#externalRefNumber").val(msg.externalRefNumber);
                        $("#accountVerifyBene").prop('disabled', false);
                        swal("Success", 'An OTP has been sent to the retailer\'s mobile number.', "success");
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function resendOtp() {
            $("#resendOtpBtn").hide();
            $("#resendOtpBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            var dataString = 'mobile_number=' + mobile_number + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v1/iserveU-resend-otp')}}",
                data: dataString,
                success: function (msg) {
                    $("#resendOtpBtn").show();
                    $("#resendOtpBtn_loader").hide();
                    if (msg.status == 'error') {
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#pincode_errors").text(msg.errors.pincode);
                        $("#state_errors").text(msg.errors.state);
                        $("#address_errors").text(msg.errors.address);
                    } else if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>
    @include('agent.dmt.iServeU.common')
    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>

                            <div style="display: none;" id="sender-details-label">
                                <button class="btn btn-warning">Name : <span class="name"></span></button>
                                <button class="btn btn-primary">Mobile Number : <span class="mobile_number"></span>
                                </button>
                                <button class="btn btn-danger">Total Limit : <span class="total_limit"></span></button>
                            </div>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>

                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" id="mobile_number" placeholder="Mobile Number"
                                               class="form-control"
                                               oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                               maxlength="10">
                                        <span class="input-group-btn">
										    <button class="btn ripple btn-primary br-tl-0 br-bl-0" type="button"
                                                    onclick="getCustomer()" id="search_btn">Search</button>
									    </span>
                                        <input type="hidden" id="externalRefNumber">
                                    </div>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="mobile_number_errors"></li>
                                    </ul>
                                </div>

                                <div class="col-sm-4"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="row" id="beneficiary-details-label" style="display: none;'">
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Beneficiary List</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">


                        <div class="table-responsive mb-0">
                            <table class="table table-striped mg-b-0 text-md-nowrap">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>IFsc Code</th>
                                    <th>Bank Name - Account Number</th>
                                    <th>Action</th>
                                    <th>Delete</th>

                                </tr>
                                </thead>
                                <tbody class="beneficiary_list">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Add Beneficiary</h6>
                            <hr>
                        </div>

                        <div class="row" id="AddBenefe">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Bank Name</label>
                                    <select class="form-control select2" id="bank_id" style="width: 100%"
                                            onchange="getIfscCode()">
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $value)
                                            <option value="{{ $value->id }}">{{ $value->bank_name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="bank_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">IFSC Code</label>
                                    <input type="text" class="form-control" id="ifsc_code" placeholder="IFSC Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="ifsc_code_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <div class="input-group">
                                        <input type="text" id="account_number" placeholder="Account Number"
                                               class="form-control">
                                        <input type="hidden" id="verify_status" value="0"
                                               class="form-control">
                                    </div>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="account_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Beneficiary Name</label>
                                    <input type="text" class="form-control" id="beneficiary_name"
                                           placeholder="Beneficiary Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="beneficiary_name_errors"></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" class="form-control" id="address_bene"
                                           placeholder="Address">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="address_bene_errors"></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">pincode</label>
                                    <input type="text" class="form-control" id="pincode_bene"
                                           placeholder="Pincode">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="pincode_bene_errors"></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="senderOtp">OTP</label>
                                    <input type="text" id="senderOtp" placeholder="OTP"
                                           class="form-control">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="senderOtp_errors"></li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn ripple btn-danger br-tl-0 br-bl-0" type="button"
                                onclick="sendOtpToSender()" id="sendOtpToBtn">Send OTP
                        </button>
                        <button class="btn btn-danger" type="button" id="sendOtpToBtn_loader"
                                disabled style="display: none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                        <button class="btn ripple btn-primary" id="accountVerifyBene" disabled type="button"
                                onclick="accountVerify()">Verify &
                            Add
                            Beneficiary
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /row -->
    </div>
    </div>
    </div>
@endsection
