@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
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



    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{url('agent/report/v1/all-transaction-report')}}" method="get">
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
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="my_table">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">ID</th>
                                    <th class="wd-15p border-bottom-0">Date Time</th>
                                    <th class="wd-15p border-bottom-0">Provider</th>
                                    <th class="wd-15p border-bottom-0">Number</th>
                                    <th class="wd-15p border-bottom-0">Txn Id</th>
                                    <th class="wd-15p border-bottom-0">Opening Balance</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">Charge</th>
                                    <th class="wd-15p border-bottom-0">TDS</th>
                                    <th class="wd-15p border-bottom-0">Closing Balance</th>
                                    <th class="wd-15p border-bottom-0">Wallet</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Failure Reason</th>
                                    <th class="wd-15p border-bottom-0">State</th>
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
                                            { data: 'opening_balance' },
                                            { data: 'amount' },
                                            { data: 'profit' },
                                            { data: 'tds' },
                                            { data: 'total_balance' },
                                            { data: 'wallet_type' },
                                            { data: 'status' },
                                            { data: 'failure_reason' },
                                            { data: 'state_name' },
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
