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

        function view_details(id) {
            // $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('agent/gift-card/v1/view-voucher-details') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_model").html(msg.html);
                        $("#view_model").modal('show');
                    } else {
                        swal("Faild", msg.message, "error");
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

                        <form action="{{ url('agent/gift-card/v1/voucher-history') }}" method="get">
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
                            <a href="{{ url('agent/gift-card/v1/welcome') }}" class="btn btn-danger btn-sm"><i
                                    class="fas fa-download"></i> Back</a>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="@if (Auth::User()->company->table_format == 1) table text-md-nowrap @else display responsive nowrap @endif"
                                id="my_table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Created At</th>
                                        <th class="wd-15p border-bottom-0">Code</th>
                                        <th class="wd-15p border-bottom-0">Pin</th>
                                        <th class="wd-15p border-bottom-0">Validity</th>
                                        <th class="wd-15p border-bottom-0">Amount</th>
                                        <th class="wd-15p border-bottom-0">Category</th>
                                        <th class="wd-15p border-bottom-0">Product Name</th>
                                        <th class="wd-15p border-bottom-0">Action</th>

                                    </tr>
                                </thead>
                            </table>

                            <script type="text/javascript">
                                $(document).ready(function() {
                                    $('#my_table').DataTable({
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
                                                data: 'code'
                                            },
                                            {
                                                data: 'pin'
                                            },
                                            {
                                                data: 'validity_date'
                                            },
                                            {
                                                data: 'amount'
                                            },
                                            {
                                                data: 'categories'
                                            },
                                            {
                                                data: 'product_name'
                                            },
                                            {
                                                data: 'view'
                                            },
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

        <div class="modal fade" id="view_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        </div>
    </div>
    </div>
    </div>
@endsection
