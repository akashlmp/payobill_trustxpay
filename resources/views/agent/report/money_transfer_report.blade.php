@extends('agent.layout.header')
@section('content')

<script type="text/javascript">
    $(document).ready(function () {

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



</script>



<div class="main-content-body">

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{url('agent/money-transfer-report')}}" method="get">
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
                                <label class="form-label">Sender Number: <span class="tx-danger">*</span></label>
                                <input type="text" class="form-control" name="sender_number" value="{{ $sender_number }}" placeholder="Sender Number">
                            </div>

                            <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                <button class="btn btn-main-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
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
                        <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="my_table">
                            <thead>
                            <tr>
                                <th class="wd-15p border-bottom-0">ID</th>
                                <th class="wd-15p border-bottom-0">Date Time</th>
                                <th class="wd-15p border-bottom-0">Provider</th>
                                <th class="wd-15p border-bottom-0">Account Number</th>
                                <th class="wd-15p border-bottom-0">UTR</th>
                                <th class="wd-15p border-bottom-0">Amount</th>
                                <th class="wd-15p border-bottom-0">Charges</th>
                                <th class="wd-15p border-bottom-0">Balance</th>
                                <th class="wd-15p border-bottom-0">Type</th>
                                <th class="wd-15p border-bottom-0">Status</th>
                                <th class="wd-15p border-bottom-0">Remitter Number</th>
                                <th class="wd-15p border-bottom-0">Bene Name</th>
                                <th class="wd-15p border-bottom-0">Bank Name</th>
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
                                        { data: 'provider' },
                                        { data: 'number' },
                                        { data: 'txnid' },
                                        { data: 'amount' },
                                        { data: 'profit' },
                                        { data: 'balance' },
                                        { data: 'payment_mode' },
                                        { data: 'status' },
                                        { data: 'remiter_number' },
                                        { data: 'bene_name' },
                                        { data: 'bank_name' },
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

@include('agent.report.view_model')


@endsection
