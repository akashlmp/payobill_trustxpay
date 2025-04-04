@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function() {
            $("#other_id").select2();
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

        $(document).on('click', '.changeStatus', function() {
            var status = $(this).data('status');
            var id = $(this).data('id');
            var text = "deactivate";
            var head = "Deactivate";
            if (status == 1) {
                text = "activate";
                head = "Activate";
            }
            swal({
                    title: head ,
                    text: "Are you sure you want to " + text + " this QR code ? ",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    cancelButtonText: "Cancel",
                    closeOnConfirm: false,
                    closeOnCancel: false,
                    showLoaderOnConfirm: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var token = $("input[name=_token]").val();
                        $.ajax({
                            type: "POST",
                            url: "{{ url('agent/virtual-account/status-change') }}",
                            data: "id="+id+"&status="+status+"&_token="+token,
                            success: function(msg) {
                                if (msg.status == 'success') {
                                    swal("Success", msg.message, "success");
                                    $table.ajax.reload();
                                } else {
                                    swal("Failed", msg.message, "error");
                                }
                            }
                        });
                    }
                });
        });
    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ url('agent/virtual-account-static-qr') }}" method="get">
                            <div class="row">
                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">From: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text"
                                        id="fromdate" name="fromdate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">To: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $todate }}" type="text"
                                        id="todate" name="todate" autocomplete="off">
                                </div>



                                <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-main-primary pd-x-20" type="submit"><i
                                            class="fas fa-search"></i> Search</button>
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
                            <table class=" table text-md-nowrap display responsive nowrap " id="my_table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Date Time</th>
                                        <th class="wd-15p border-bottom-0">Merchant Reference Id</th>
                                        <th class="wd-15p border-bottom-0">Reference ID</th>
                                        <th class="wd-15p border-bottom-0">Upi QR Code Image</th>
                                        <th class="wd-15p border-bottom-0">Upi QR Code Pdf</th>
                                        <th class="wd-15p border-bottom-0">Virtual Account Id</th>
                                        {{-- <th class="wd-15p border-bottom-0">Account Number</th> --}}
                                        <th class="wd-15p border-bottom-0">Virtual Account Number</th>
                                        <th class="wd-15p border-bottom-0">Status</th>
                                        {{-- <th class="wd-15p border-bottom-0">Auto Deactivate At</th> --}}
                                        <th class="wd-15p border-bottom-0">Action</th>
                                    </tr>
                                </thead>
                            </table>

                            <script type="text/javascript">
                            $table = "";
                                $(document).ready(function() {
                                    $table =  $('#my_table').DataTable({
                                        "order": [
                                            [1, "desc"]
                                        ],
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $urls }}",
                                        columns: [{
                                                data: 'id'
                                            },
                                            {
                                                data: 'created_at'
                                            },
                                            {
                                                data: 'merchant_reference_id'
                                            },
                                            {
                                                data: 'unique_request_number'
                                            },
                                            {
                                                data: 'upi_qrcode_remote_file_location'
                                            },
                                            {
                                                data: 'upi_qrcode_scanner_remote_file_location'
                                            },
                                            {
                                                data: 'virtual_account_id'
                                            },
                                            // { data: 'account_number' },
                                            {
                                                data: 'virtual_account_number'
                                            },
                                            {
                                                data: 'is_active'
                                            },
                                            // { data: 'auto_deactivate_at' }
                                            {
                                                data: 'action'
                                            }
                                        ]
                                    });
                                    $("input[type='search']").wrap("<form>");
                                    $("input[type='search']").closest("form").attr("autocomplete", "off");
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

    @include('agent.report.view_model')
@endsection
