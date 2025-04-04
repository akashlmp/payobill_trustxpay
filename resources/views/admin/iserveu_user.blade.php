@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).on('click', '.file_upload', function() {
            $("#update_user_model").modal('show');
        });

        function update_users() {

            $("#user_file_errors").text("");
            $("#transfer_btn_loader").show();

            $.ajax({
                type: "POST",
                url: "{{ url('admin/update-iserveu-user-onboard') }}",
                data: new FormData($('#uploadDocumentForm')[0]),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success: function(msg) {
                    $("#transfer_btn").show();
                    $("#transfer_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function() {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        //alert(msg.errors.file);
                        $("#user_file_errors").text(msg.errors.user_file);
                    } else {
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
                        <a href="{{ url('admin/download/v1/iserveu-user-onboarding-download') }}"
                            class="btn btn-danger pd-x-20 float-right" target="_blank"><i class="fas fa-download"></i>
                            Export Onboard Users</a>
                        <button type="button" class="btn btn-primary pd-x-20 mg-r-10-f float-right file_upload"><i
                                class="fas fa-upload"></i>
                            Upload CSV</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }} List</h4>
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
                                        <td>Mobile</td>
                                        <td>Member Type</td>
                                        <td>Retailer ID / Distributor ID</td>
                                        <td>ISERVEU AEPS Onboard</td>
                                        <td>Joining Date</td>
                                    </tr>
                                </thead>
                            </table>

                            <!-- Script -->
                            <script type="text/javascript">
                                $(document).ready(function() {
                                    $('#my_table').DataTable({
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $url }}",
                                        columns: [{
                                                data: 'id'
                                            },
                                            {
                                                data: 'fullname'
                                            },
                                            {
                                                data: 'mobile_number'
                                            },
                                            {
                                                data: 'member_type'
                                            },
                                            {
                                                data: 'cms_agent_id'
                                            },
                                            {
                                                data: 'iserveu_onboard_status'
                                            },
                                            {
                                                data: 'joining_date'
                                            },
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


    <div class="modal  show" id="update_user_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" style="width: 40%" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Retailer Onboard Status</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <form method="post" action="" id="uploadDocumentForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-body">

                            <div class="row">

                                <div class="col-sm-12">

                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Upload CSV File</span>
                                        </div>
                                        <div class="custom-file">

                                            <input type="file" class="custom-file-input" name="user_file" id="user_file">
                                            <label class="custom-file-label" for="inputGroupFile01">Choose CSV file</label>

                                        </div>

                                    </div>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="user_file_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="transfer_btn"
                            onclick="update_users()">Submit</button>
                        <button class="btn btn-primary" type="button" id="transfer_btn_loader" disabled
                            style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
