@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">
        function get_amount_label(type){
            $('#scan_btn').attr('disabled', false);
            if (type.value == 'CW'){
                $("#amount_label").show();
                $("#submit_btn").show();
                $("#mini_submit_btn").hide();
                $(".customer-scan-label").hide();
                $(".merchant-scan-label").show();
            } else if (type.value == 'APW'){
                $("#amount_label").show();
                $("#submit_btn").show();
                $("#mini_submit_btn").hide();
                $(".customer-scan-label").show();
                $(".merchant-scan-label").hide();
            } else if (type.value == 'MS'){
                $("#amount_label").hide();
                $("#submit_btn").hide();
                $("#mini_submit_btn").show();
                $(".customer-scan-label").show();
                $(".merchant-scan-label").hide();
            } else {
                $("#amount_label").hide();
                $("#submit_btn").show();
                $("#mini_submit_btn").hide();
                $(".customer-scan-label").show();
                $(".merchant-scan-label").hide();
            }
        }

        function initiate_transaction() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var bank_id = $("#bank_id").val();
                var mobile_number = $("#mobile_number").val();
                var aadhar_number = $("#aadhar_number").val();
                var BiometricData = $("#BiometricData").val();
                var amount = $("#amount").val();
                var ci = $("#ci").val();
                var pidtype = $("#pidtype").val();
                var transaction_type = $("#transaction_type").val();
                var MerAuthTxnId = $("#MerAuthTxnId").val();
                var dataString = 'bank_id=' + bank_id + '&mobile_number=' + mobile_number + '&aadhar_number=' + aadhar_number + '&BiometricData=' + encodeURIComponent(BiometricData) + '&amount=' + amount + '&ci=' + ci + '&pidtype=' + pidtype + '&transaction_type=' + transaction_type + '&MerAuthTxnId=' + MerAuthTxnId + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/aeps/v2/initiate-transaction')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            $("#success_message").text(msg.message);
                            $(".bank_name").text(msg.details.bank_name);
                            $(".amount").text(msg.details.amount);
                            $(".total_balance").text(msg.details.total_balance);
                            $(".utr").text(msg.details.utr);
                            $(".aadhar_number").text(msg.details.aadhar_number);
                            $(".shop_name").text(msg.details.shop_name);
                            $(".shop_address").text(msg.details.shop_address);
                            $("#aeps_receipt_anchor").attr('href', msg.details.receipt_anchor);
                            $("#aeps_receipt_model").modal('show');
                            $('#scan_btn').attr('disabled', false);
                            $('#submit_btn').attr('disabled', true);
                        } else if(msg.status == 'error'){
                            $("#bank_id_errors").text(msg.errors.bank_id);
                            $("#mobile_number_errors").text(msg.errors.mobile_number);
                            $("#aadhar_number_errors").text(msg.errors.aadhar_number);
                            $("#BiometricData_errors").text(msg.errors.BiometricData);
                            $('#scan_btn').attr('disabled', false);
                            $('#submit_btn').attr('disabled', true);
                        }else{
                            swal("Failed", msg.message, "error");
                            $('#scan_btn').attr('disabled', false);
                            $('#submit_btn').attr('disabled', true);
                        }
                    }
                });
            }else{
                getLocation();
                alert('Please allow this site to access your location');
            }
        }

        function mini_statement() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var bank_id = $("#bank_id").val();
                var mobile_number = $("#mobile_number").val();
                var aadhar_number = $("#aadhar_number").val();
                var BiometricData = $("#BiometricData").val();
                var ci = $("#ci").val();
                var pidtype = $("#pidtype").val();
                var transaction_type = $("#transaction_type").val();
                var dataString = 'bank_id=' + bank_id + '&mobile_number=' + mobile_number + '&aadhar_number=' + aadhar_number + '&BiometricData=' + encodeURIComponent(BiometricData) + '&ci=' + ci + '&pidtype=' + pidtype + '&transaction_type=' + transaction_type + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/aeps/v2/initiate-transaction')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            $("#ms_success_message").text(msg.message);
                            $(".bank_name").text(msg.details.bank_name);
                            $(".total_balance").text(msg.details.total_balance);
                            $(".utr").text(msg.details.utr);
                            $(".aadhar_number").text(msg.details.aadhar_number);
                            $(".shop_name").text(msg.details.shop_name);
                            $(".shop_address").text(msg.details.shop_address);
                            $("#ms_aeps_receipt_anchor").attr('href', msg.details.receipt_anchor);
                            var ministatement = msg.ministatement;
                            var html = "";
                            for (var key in ministatement) {
                                html += "<tr>";
                                html += '<td>' + ministatement[key].date + '</td>';
                                html += '<td>' + ministatement[key].txnType + '</td>';
                                html += '<td>' + ministatement[key].amount + '</td>';
                                html += '<td>' + ministatement[key].narration + '</td>';
                                html += "</tr>";
                            }
                            $(".ministatement_list").html(html);

                            $("#mini_aeps_receipt_model").modal('show');
                            $('#mini_submit_btn').attr('disabled', false);
                        } else if(msg.status == 'error'){
                            $("#bank_id_errors").text(msg.errors.bank_id);
                            $("#mobile_number_errors").text(msg.errors.mobile_number);
                            $("#aadhar_number_errors").text(msg.errors.aadhar_number);
                            $("#BiometricData_errors").text(msg.errors.BiometricData);
                            $('#mini_submit_btn').attr('disabled', false);
                        }else{
                            swal("Failed", msg.message, "error");
                            $('#mini_submit_btn').attr('disabled', false);
                        }
                    }
                });
            }else{
                getLocation();
                alert('Please allow this site to access your location');
            }
        }
    </script>


    <style>
        .instantpayAeps .nav-item {
            border: 1px solid #aba9c7;
            font-size: 16px;
            padding: 5px;
        }
        .instantpayAeps a{
            color: #555268;
        }
        .instantpayAeps .active {
            color: #3857f6;
        }

        .instantpayAeps .nav-active {
            border-color: #3857f6;
        }

    </style>

    @include('agent.aeps.paysprint.include')
    <div class="main-content-body">
        <div class="row">

            @include('agent.aeps.paysprint.leftSideMenu')

            <div class="col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-body">
                        <form class="form-horizontal">
                            <div class="mb-4 main-content-label">{{ $page_title }}</div>
                            <hr>

                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Transaction Type</label>
                                        <select class="form-control" id="transaction_type" onchange="get_amount_label(this)">
                                            <option value="BE">Balance Enquiry</option>
                                            <option value="CW">Cash Withdrawal</option>
                                            <option value="MS">Mini Statement</option>
                                            <option value="APW">Aadhar Pay Withdrawal</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="transaction_type_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank Name</label>
                                        <select class="form-control select2" id="bank_id">
                                            @foreach($banks as $value)
                                                <option value="{{$value->iinno}}">{{ $value->bank_name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="bank_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Aadhaar Number</label>
                                        <input type="text" class="form-control"  placeholder="Aadhar Number" id="aadhar_number" maxlength="12">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="aadhar_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Mobile Number</label>
                                        <input type="text" class="form-control"  placeholder="Customer Mobile Number" id="mobile_number"
                                               oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "10">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="mobile_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-md-12" style="display:none;" id="amount_label">
                                    <div class="form-group">
                                        <labe>Amount</labe>
                                        <input type="text" class="form-control"  placeholder="Amount" id="amount">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="amount_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" style="display:none;">
                                <div class="row">
                                    <div class="col-md-2 col">
                                        <label class="rdiobox"><input checked name="device" id="device" value="MANTRA_PROTOBUF" type="radio"> <span>MANTRA</span></label>
                                    </div>

                                    <div class="col-md-2 col">
                                        <label class="rdiobox"><input  name="device" id="device" value="MORPHO_PROTOBUF" type="radio"> <span>MORPHO</span></label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-2 main-content-label">Terms And Conditions</div>
                            <div class="form-group mb-0">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="ckbox mg-b-10">
                                            <input type="checkbox" checked>
                                            <span style="color: blue;" data-target="#condtion_for_retailer" data-toggle="modal">I retailer hareby accepts and confirms all the terms and conditions under best of my knowledge</span></label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="row">

                                    <div class="col-md-12">
                                        <label class="ckbox mg-b-10">
                                            <input type="checkbox" checked>
                                            <span style="color: blue;" data-target="#condtion_for_customer" data-toggle="modal">I customer hare by accepts and confirms all the terms and conditions under best of my knowledge</span></label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-danger" role="alert" id="failure_alert" style="display: none;"></div>
                                        <div class="alert alert-success" role="alert" id="success_alert" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-white">
                        <div class="merchant-scan-label" style="text-align:center; display: none;">
                            <label>Merchant Aadhar Number</label>
                            <input type="text" class="form-control" placeholder="Merchant Aadhar Number" id="merchant_aadhar_number" value="{{ Auth::user()->member->aadhar_number }}">
                            <br>
                            <button class="btn btn-danger" id="merchantAuthScanBtn" onclick="merchant_auth_device_scans()">Merchant Scan</button>
                        </div>
                        <div class="customer-scan-label" style="text-align:center;">
                            <button class="btn btn-danger" id="scan_btn" onclick="device_scans()">Scan</button>
                            <button  class="btn btn-success" id="submit_btn" onclick="initiate_transaction()" disabled>Submit</button>
                            <button  class="btn btn-success" id="mini_submit_btn" onclick="mini_statement()" disabled style="display:none;">Submit</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
    </div>
    </div>

    @include('agent.aeps.paysprint.merchant-authenticity')

@endsection
