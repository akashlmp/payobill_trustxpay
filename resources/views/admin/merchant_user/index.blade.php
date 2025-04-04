@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
    </script>

    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Merchants List</h4>
                            @if (Auth::User()->role_id == 1 )
                                <a href="{{ url('admin/download/v1/merchant-users-download') }}"
                                   class="btn btn-danger btn-sm  float-right" target="_blank"><i
                                        class="fas fa-download"></i>
                                    Export</a>
                            @endif
                            @if (Auth::User()->role_id == 1)
                                <a href="{{ url('admin/create-merchant') }}"
                                   class="btn btn-danger btn-sm">
                                    Create merchant</a>
                            @endif
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id='my_table' class="display responsive nowrap" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <td>User Id</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                    <td>Commission/Charges Set Up</td>
                                    <td>Wallet</td>
                                    <td>Mobile</td>
                                    <td>Status</td>
                                    <td>Joining Date</td>
                                </tr>
                                </thead>
                            </table>

                            <!-- Script -->
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    $('#my_table').DataTable({
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $url }}",
                                        columns: [
                                            {data: 'id'},
                                            {data: 'fullname'},
                                            {data: 'email'},
                                            {data: 'api_commission'},
                                            {data: 'wallet'},
                                            {data: 'mobile_number'},
                                            {data: 'status'},
                                            {data: 'joining_date'},
                                        ],
                                    });
                                    $("input[type='search']").wrap("<form>");
                                    $("input[type='search']").closest("form").attr("autocomplete", "off");
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    @include('admin.merchant_user.view_model')
@endsection
