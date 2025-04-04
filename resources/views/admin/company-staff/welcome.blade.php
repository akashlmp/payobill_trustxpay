@extends('admin.layout.header')
@section('content')


    <div class="main-content-body">
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


                            <table id='my_table' class="display responsive nowrap" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <td>User Id</td>
                                    <td>Joining Date</td>
                                    <td>Name</td>
                                    <td>Mobile</td>
                                    <td>Member Type</td>
                                    <td>Normal Balance</td>
                                    <td>Status</td>
                                    <td>Permission</td>
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
                                            {data: 'created_at'},
                                            {data: 'name'},
                                            {data: 'mobile'},
                                            {data: 'member_type', orderable: false},
                                            {data: 'user_balance', orderable: false},
                                            {data: 'status', orderable: false},
                                            {data: 'permission'},
                                        ],
                                    });

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

@endsection
