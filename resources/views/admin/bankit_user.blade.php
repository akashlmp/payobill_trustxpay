@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).on('click', '#exportUser', function () {
            let type = $('#exportType').val();
            let url = '{{ url('admin/download/v1/bankit-user-onboarding-download') }}?type=' + type;
            $(".loader").show();
            $.ajax({
                type: "GET",
                url: url,
                success: function (res) {
                    console.log(res)
                    $(".loader").hide();
                    if (res.status == 'success') {
                        $('#downloadButton').attr('data-filename', res.data.file_name)
                        $('#downloadButton').show()
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        });

        $(document).on('click', '#downloadButton', function () {
            let fileName = $(this).attr('data-filename');
            if (fileName) {
                let url = '{{ url('admin/download/v1/bankit-user-onboarding-zip-download') }}?file_name=' + fileName;
                var link = document.createElement('a');
                link.href = url;
                link.target = '_blank'; // Open in a new tab
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
            $(this).hide();
        });
    </script>

    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 form-group">
                                <select class="form-control" id="exportType">
                                    <option value="aeps">AEPS</option>
                                    <option value="cms">CMS</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <button class="btn btn-danger pd-x-20" id="exportUser" type="button"><i
                                        class="fas fa-download"></i>
                                    Export
                                </button>
                                <button class="btn ripple btn-primary" id="downloadButton" style="display: none"
                                        type="button">Download
                                </button>
                            </div>

                        </div>

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
                                    <td>AEPS Onboard</td>
                                    <td>CMS Onboard</td>
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
                                            {data: 'name'},
                                            {data: 'mobile_number'},
                                            {data: 'member_type'},
                                            {data: 'cms_agent_id'},
                                            {data: 'aeps_onboard_status'},
                                            {data: 'cms_onboard_status'},
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

@endsection
