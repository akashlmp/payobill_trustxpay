@extends('admin.layout.header')
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

                        <form action="{{url('admin/merchant-payout-report')}}" method="get">
                            <div class="row">
                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">{{ $fromdate }} From: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">To: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Status: <span class="tx-danger">*</span></label>
                                    <select class="form-control select2" id="download_optional1" name="status_id" style="width: 100%;">
                                        <option value="all" @if($status_id == "all") selected @endif> All Status</option>
                                        <option value="0" @if($status_id == 0) selected @endif> Pending</option>
                                        <option value="1" @if($status_id == 1) selected @endif> Success</option>
                                        <option value="2" @if($status_id == 2) selected @endif> Failed</option>
                                        <option value="3" @if($status_id == 3) selected @endif> Refunded</option>                                       
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
                                    {{-- <button class="btn btn-danger pd-x-20" type="button"  data-toggle="modal" data-target="#transaction_download_model"><i class="fas fa-download"></i> Download</button> --}}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

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
                                        <table class="display responsive nowrap"  id="my_table">
                                            <thead>
                                                <tr>
                                                    <th class="wd-15p border-bottom-0">ID</th>
                                                    <th class="wd-15p border-bottom-0">Date Time</th>
                                                    <th class="wd-15p border-bottom-0">Transaction Id</th>
                                                    <th class="wd-15p border-bottom-0">User Name</th>
                                                    <th class="wd-15p border-bottom-0">Bank Name</th>
                                                    <th class="wd-15p border-bottom-0">Account Name</th>
                                                    <th class="wd-15p border-bottom-0">Account Number</th>
                                                    <th class="wd-15p border-bottom-0">IFSC Code</th>
                                                    <th class="wd-15p border-bottom-0">Amount</th>
                                                    <th class="wd-15p border-bottom-0">Status</th>
                                                    <th class="wd-15p border-bottom-0">Mode</th>

                                                </tr>
                                            </thead>
                                        </table>
                                            <script type="text/javascript">
                                            $(document).ready(function(){

                                                // DataTable
                                                $('#my_table').DataTable({

                                                    processing: true,
                                                    serverSide: true,
                                                    ajax: "{{ $urls }}",
                                                    columns: [
                                                        { data: 'id' },
                                                        { data: 'created_at' },
                                                        { data: 'transaction_id' },
                                                        { data: 'user_name' },
                                                        { data: 'bank_name' },
                                                        { data: 'bene_name' },
                                                        { data: 'account_number' },
                                                        { data: 'ifsc' },
                                                        { data: 'amount' },
                                                        { data: 'status' },
                                                        { data: 'mode' },

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
        </div>



    </div>
    </div>
    </div>



@endsection
