@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#merchant_id").select2();
            $("#fromdate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });
            $("#todate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });
        });
    function download_report() {
        $("#download_btn").hide();
        $("#download_btn_loader").show();
        var token = $("input[name=_token]").val();
        var download_menu_name = $("#download_menu_name").val();
        var download_password = $("#download_password").val();
        var fromdate = $("#fromdate").val();
        var todate = $("#todate").val();
        var download_optional1 = $("#download_optional1").val();
        var download_optional2 = $("#download_optional2").val();
        var download_optional3 = $("#download_optional3").val();
        var download_optional4 = $("#download_optional4").val();
        var dataString = 'menu_name=' + download_menu_name + '&password=' + download_password + '&fromdate=' + fromdate + '&todate=' + todate + '&download_optional1=' + download_optional1 + '&download_optional2=' + download_optional2 + '&download_optional3=' + download_optional3 + '&download_optional4=' + download_optional4 + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/merchant-file-download')}}",
            data: dataString,
            success: function (msg) {
                $("#download_btn").show();
                $("#download_btn_loader").hide();
                if (msg.status == 'success') {
                    $("#download-label").show();
                    $("#download_link").attr('href', msg.download_link);
                } else if(msg.status == 'validation_error'){
                    $("#download_menu_name_errors").text(msg.errors.menu_name);
                    $("#download_password_errors").text(msg.errors.password);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function view_recharges(id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/merchant-view-recharge-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#view_id").val(msg.details.id);
                    $("#view_company").val(msg.details.company);
                    $("#view_created_at").val(msg.details.created_at);
                    $("#view_user").val(msg.details.user);
                    $("#view_provider").val(msg.details.provider);
                    $("#view_account_number").val(msg.details.account_number);
                    $("#view_txnid").val(msg.details.txnid);
                    $("#view_opening_balance").val(msg.details.opening_balance);
                    $("#view_amount").val(msg.details.amount);
                    $("#view_profit").val(msg.details.profit);
                    $("#view_total_balance").val(msg.details.total_balance);
                    $("#view_mode").val(msg.details.mode);
                    $("#view_status_id").val(msg.details.status_id);
                    $("#view_ip_address").val(msg.details.ip_address);

                  
                    $("#view_recharge_model").modal('show');
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function view_refund_recharge(id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/report/v1/view-recharge-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                $("#view_recharge_model").modal('hide');
                if (msg.status == 'success') {
                    $("#refund_id").val(msg.details.id);
                    $("#refund_user").val(msg.details.user);
                    $("#refund_provider").val(msg.details.provider);
                    $("#refund_number").val(msg.details.number);
                    $("#refund_txnid").val(msg.details.txnid);
                    $("#refund_status_id").val(msg.details.status_id);
                    $("#refund_wallet_type").val(msg.details.wallet_type);
                    $("#view_recharge_refund_model").modal('show');
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function update_recharge_refund() {
        $("#recharge_refund_btn").hide();
        $("#recharge_refund_btn_loader").show();
        var token = $("input[name=_token]").val();
        var id = $("#refund_id").val();
        var txnid = $("#refund_txnid").val();
        var status_id = $("#refund_status_id").val();
        var wallet_type = $("#refund_wallet_type").val();
        var dataString = 'id=' + id + '&txnid=' + txnid + '&status_id=' + status_id + '&wallet_type=' + wallet_type + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/report/v1/recharge-update-for-refund')}}",
            data: dataString,
            success: function (msg) {
                $("#recharge_refund_btn").show();
                $("#recharge_refund_btn_loader").hide();
                $("#view_recharge_refund_model").modal('hide');
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                    setTimeout(function () { location.reload(1); }, 3000);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });

    }

    function view_transaction_logs(id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/report/v1/view-transaction-logs')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                $("#view_recharge_model").modal('hide');
                if (msg.status == 'success') {
                    var logs = msg.logs;
                    var html = "";
                    for (var key in logs) {
                        if (logs[key].id == 1){
                            var aria_expanded = true;
                        }else{
                            var aria_expanded = false;
                        }
                        html += ' <div class="card"><div class="card-header" id="headingOne" role="tab"><a aria-controls="collapseOne" aria-expanded="' + aria_expanded + '" data-toggle="collapse" href="#requestlogs_' + logs[key].id + '">' + logs[key].request_message + '</a></div><div aria-labelledby="headingOne" class="collapse show" data-parent="#accordion" id="requestlogs_' + logs[key].id + '" role="tabpanel"><div class="card-body">' + logs[key].response + '</div></div></div>';
                    }
                    $(".logs_data").html(html);
                    $("#view_logs_model").modal('show');
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function findIpLocation (){
        $("#findIpLocationBtn").hide();
        $("#findIpLocationBtn_loader").show();
        var ip_address = $("#view_ip_address").val();
        var token = $("input[name=_token]").val();
        var dataString = 'ip_address=' + ip_address + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/merchant-find-ip-location')}}",
            data: dataString,
            success: function (msg) {
                $("#findIpLocationBtn").show();
                $("#findIpLocationBtn_loader").hide();
                if (msg.status == 'success') {
                    $(".location_ip_address").text(msg.details.ip_address);
                    $(".location_country_name").text(msg.details.country_name);
                    $(".location_country_code").text(msg.details.country_code);
                    $(".location_region_code").text(msg.details.region_code);
                    $(".location_region_name").text(msg.details.region_name);
                    $(".location_city_name").text(msg.details.city_name);
                    $(".location_zip_code").text(msg.details.zip_code);
                    $(".location_latitude").text(msg.details.latitude);
                    $(".location_longitude").text(msg.details.longitude);
                    $("#view_recharge_model").modal('hide');
                    $("#location_details_model").modal('show');
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{url('admin/merchant-transaction-report')}}" method="get">
                            <div class="row">
                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">From: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">To: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Status: <span class="tx-danger">*</span></label>
                                    <select class="form-control select2" id="download_optional1" name="status_id" style="width: 100%;">
                                        <option value="0" @if($status_id == 0) selected @endif> All Status</option>
                                        @foreach($status as $value)
                                        <option value="{{ $value->id }}" @if($status_id == $value->id) selected @endif> {{ $value->status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="form-label">Select Merchant:</label>
                                        <select class="form-control select2" id="download_optional2" name="child_id" style="width: 100%;" id="merchant_id">
                                            <option value="0" @if($child_id == 0) selected @endif> All Merchant</option>
                                            @foreach($users as $value)
                                                <option value="{{ $value->id }}" @if($child_id == $value->id) selected @endif>{{ $value->first_name }} {{ $value->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-main-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
                                    <button class="btn btn-danger pd-x-20" type="button"  data-toggle="modal" data-target="#transaction_download_model"><i class="fas fa-download"></i> Download</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display responsive nowrap" id="my_table">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">ID</th>
                                    <th class="wd-15p border-bottom-0">Date Time</th>
                                    <th class="wd-15p border-bottom-0">Merchant</th>
                                    <th class="wd-15p border-bottom-0">Provider</th>
                                    <th class="wd-15p border-bottom-0">Account Number</th>
                                    <th class="wd-15p border-bottom-0">Txn Id</th>
                                    <th class="wd-15p border-bottom-0">Opening Balance</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">Charges</th>
                                    {{-- <th class="wd-15p border-bottom-0">TDS</th> --}}
                                    <th class="wd-15p border-bottom-0">Closing Balance</th>
                                    {{-- <th class="wd-15p border-bottom-0">Wallet</th> --}}
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Failure Reason</th>
                                    {{-- <th class="wd-15p border-bottom-0">State</th> --}}
                                    <th class="wd-15p border-bottom-0">Action</th>

                                </tr>
                                </thead>
                            </table>

                            <script type="text/javascript">
                                $(document).ready(function(){
                                    $('#my_table').DataTable({
                                        "order": [[ 1, "desc" ]],
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $urls }}",
                                        columns: [
                                            { data: 'id' },
                                            { data: 'created_at' },
                                            { data: 'merchant_id' },
                                            { data: 'provider' },
                                            { data: 'number' },
                                            { data: 'txnid' },
                                            { data: 'opening_balance' },
                                            { data: 'amount' },
                                            { data: 'profit' },
                                            // { data: 'tds' },
                                            { data: 'total_balance' },
                                            // { data: 'wallet_type' },
                                            { data: 'status' },
                                            { data: 'failure_reason' },
                                            // { data: 'state_name' },
                                            { data: 'view' },
                                        ]
                                    });
                                    $("input[type='search']").wrap("<form>");
                                    $("input[type='search']").closest("form").attr("autocomplete","off");
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>

    <style>
    .modal-dialog-slideout {min-height: 100%; margin: 0 0 0 auto;background: #fff;}
    .modal.fade .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(100%,0)scale(1);transform: translate(100%,0)scale(1);}
    .modal.fade.show .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(0,0);transform: translate(0,0);display: flex;align-items: stretch;-webkit-box-align: stretch;height: 100%;}
    .modal.fade.show .modal-dialog.modal-dialog-slideout .modal-body{overflow-y: auto;overflow-x: hidden;}
    .modal-dialog-slideout .modal-content{border: 0;}
    .modal-dialog-slideout .modal-header, .modal-dialog-slideout .modal-footer {height: 69px; display: block;}
    .modal-dialog-slideout .modal-header h5 {float:left;}
</style>

    <!--download data model -->
    <div class="modal  show" id="transaction_download_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Download Data</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Menu Name</label>
                                    <input type="text" id="download_menu_name" class="form-control" value="Merchant Transaction Report" readonly>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Your Login Password</label>
                                    <input type="password" id="download_password" class="form-control" placeholder="Login Password">
                                    <span class="invalid-feedback d-block" id="download_password_errors"></span>
                                </div>
                            </div>


                        </div>

                    </div>

                    <div class="alert alert-outline-danger" role="alert" id="download-label" style="display: none;">
                        <strong> Download File :  <a href="" target="_blank" id="download_link">Click Here</a> </strong>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="download_btn" onclick="download_report()">Verify And Download</button>
                    <button class="btn btn-primary" type="button"  id="download_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal  show" id="view_logs_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-history"></i> View Transaction Logs</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div aria-multiselectable="true" class="accordion accordion-gray" id="accordion" role="tablist">
                    <div class="logs_data"></div>

                    </div><!-- accordion -->
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!--update model -->
    <div class="modal  show" id="view_recharge_refund_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Transaction Details</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="refund_id">

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Type</label>
                                    <select class="form-control" id="refund_wallet_type">
                                        <option value="1">Normal Wallet</option>
                                        @if(Auth::User()->company->aeps == 1)
                                            <option value="2">Aeps Wallet</option>
                                            @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">User Name</label>
                                    <input type="text" id="refund_user" class="form-control" placeholder="User Name" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Provider</label>
                                    <input type="text" id="refund_provider" class="form-control" placeholder="Provider" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Number</label>
                                    <input type="text" id="refund_number" class="form-control" placeholder="Number" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Txn Id</label>
                                    <input type="text" id="refund_txnid" class="form-control" placeholder="Txn Id">
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="refund_status_id">
                                    <option value="1">Success</option>
                                    <option value="2">Failure</option>
                                    <option value="3">Pending</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="recharge_refund_btn" onclick="update_recharge_refund()">Update Now</button>
                    <button class="btn btn-primary" type="button"  id="recharge_refund_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="view_recharge_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Report Id</label>
                                    <input type="text" id="view_id" class="form-control" placeholder="Report ID" disabled>
                                </div>
                            </div>

                            @if(Auth::User()->role_id <=  2)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Company Name</label>
                                        <input type="text" id="view_company" class="form-control" placeholder="Company Name" disabled>
                                    </div>
                                </div>
                            @endif

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Transaction Date</label>
                                    <input type="text" id="view_created_at" class="form-control" placeholder="Date" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Merchant</label>
                                    <input type="text" id="view_user" class="form-control" placeholder="User" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Provider</label>
                                    <input type="text" id="view_provider" class="form-control" placeholder="Provider" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <input type="text" id="view_account_number" class="form-control" placeholder="Account Number" disabled>
                                </div>
                            </div>
                            

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Txn Id</label>
                                    <input type="text" id="view_txnid" class="form-control" placeholder="TXNID" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Opening Balance</label>
                                    <input type="text" id="view_opening_balance" class="form-control" placeholder="Opening Balance" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" id="view_amount" class="form-control" placeholder="Amount" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Profit</label>
                                    <input type="text" id="view_profit" class="form-control" placeholder="Profit" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Total Balance</label>
                                    <input type="text" id="view_total_balance" class="form-control" placeholder="Total Balance" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Mode</label>
                                    <input type="text" id="view_mode" class="form-control" placeholder="Mode" disabled>
                                </div>
                            </div>

                          

                           
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="view_status_id" disabled>
                                        @foreach($status as $value)
                                            <option value="{{ $value->id }}">{{ $value->status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Ip Address</label>
                                    <div class="input-group">
                                        <input type="text" id="view_ip_address" class="form-control" placeholder="Ip Address" disabled>
                                        <span class="input-group-btn">
                                            <button class="btn ripple btn-danger br-tl-0 br-bl-0" type="button" id="findIpLocationBtn" onclick="findIpLocation()">Find Location</button>
                                            <button class="btn ripple btn-danger br-tl-0 br-bl-0" type="button"  id="findIpLocationBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                                        </span>
                                    </div>


                                </div>
                            </div>

                            

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
     
    <!--location detail model -->
    <div class="modal  show" id="location_details_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Details About <span class="location_ip_address"></span></h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="task-stat pb-0">

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Country Name: </div>
                                </div>
                                <span class="float-right ml-auto location_country_name"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Country Code: </div>
                                </div>
                                <span class="float-right ml-auto location_country_code"></span>
                            </div>


                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Region Code: </div>
                                </div>
                                <span class="float-right ml-auto location_region_code"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Region Name: </div>
                                </div>
                                <span class="float-right ml-auto location_region_name"></span>
                            </div>


                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">City Name: </div>
                                </div>
                                <span class="float-right ml-auto location_city_name"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Zip Code: </div>
                                </div>
                                <span class="float-right ml-auto location_zip_code"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Latitude: </div>
                                </div>
                                <span class="float-right ml-auto location_latitude"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Longitude: </div>
                                </div>
                                <span class="float-right ml-auto location_longitude"></span>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-danger" aria-label="Close" class="close" data-dismiss="modal" type="button">Cancel</button>
                </div>
            </div>
        </div>
    </div>






@endsection
