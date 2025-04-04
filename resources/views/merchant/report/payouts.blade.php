@extends('merchant.layouts.main')
@section('content')




    <div class="main-content-body">
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
                                                        { data: 'account_no' },
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
