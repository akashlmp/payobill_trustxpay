@extends('admin.layout.header')
@section('content')




    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">API's Response</h4>
                            <form action="" id="filterForm">
                                <select class="form-control" name="type" id="typeSelect">
                                    <option value="">-- Filter by response type --</option>
                                    <option value="twoFactorAuthentication" {{ request('type') == 'twoFactorAuthentication' ? 'selected' : '' }}>
                                        twoFactorAuthentication</option>
                                    <option value="merchantAuthInitiate" {{ request('type') == 'merchantAuthInitiate' ? 'selected' : '' }}>
                                        merchantAuthInitiate</option>
                                    <option value="getCustomer" {{ request('type') == 'getCustomer' ? 'selected' : '' }}>getCustomer</option>
                                    <option value="getAllBeneficiary" {{ request('type') == 'getAllBeneficiary' ? 'selected' : '' }}>getAllBeneficiary
                                    </option>
                                    <option value="confirmSender" {{ request('type') == 'confirmSender' ? 'selected' : '' }}>confirmSender</option>
                                    <option value="BE" {{ request('type') == 'BE' ? 'selected' : '' }}>BE</option>
                                    <option value="MS" {{ request('type') == 'MS' ? 'selected' : '' }}>MS</option>
                                    <option value="CW" {{ request('type') == 'CW' ? 'selected' : '' }}>CW</option>
                                    <option value="M" {{ request('type') == 'M' ? 'selected' : '' }}>M</option>
                                    <option value="doTransaction" {{ request('type') == 'doTransaction' ? 'selected' : '' }}>doTransaction</option>
                                </select>
                            </form>

                            <!-- <button class="btn btn-danger btn-sm" data-target="#add_number_series_model" data-toggle="modal">Add Number Series Master</button> -->

                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif"  id="my_table">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">ID</th>
                                    <th class="wd-25p border-bottom-0">API Type</th>
                                    <th class="wd-25p border-bottom-0">Response Type</th>
                                    <th class="wd-25p border-bottom-0">Message</th>
                                    <th class="wd-25p border-bottom-0">Request Message</th>
                                    <th class="wd-25p border-bottom-0">Report Id</th>
                                    <th class="wd-25p border-bottom-0">Created At</th>

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
                                            { data: 'api_type' },
                                            { data: 'response_type' },
                                            { data: 'message' },
                                            { data: 'request_message' },
                                            { data: 'report_id' },
                                            { data: 'created_at' },

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

<script>
    document.getElementById('typeSelect').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

</script>
@endsection
