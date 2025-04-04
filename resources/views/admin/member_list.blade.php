@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).on("click",".clsViewApiSettings",function(){
            var id = $(this).data("id");
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
            type: "POST",
            url: "{{ url('admin/member/view-settings') }}",
            data: dataString,
            success: function(msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#secret_key").val(msg.details.secret_key);
                    $("#api_key").val(msg.details.api_key);
                    $("#is_ip_whiltelist").val(msg.details.is_ip_whiltelist);
                    $("#server_ip").val(msg.details.server_ip);
                    $("#callback_url").val(msg.details.callback_url);
                    $("#update_anchor_url_settings").attr('href', msg.details.update_anchor_url);
                    $('.idShowKey').hide();
                    if (msg.details.role_id == 8) {
                        $('.idShowKey').show();
                    }
                    $("#view_api_settings_model").modal('show');
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
        });
        function adminUpdatePackage(user_id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var scheme_id = $("#packageId_" + user_id).val();
            var dataString = 'scheme_id=' + scheme_id + '&user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/update-dropdown-package') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function() {
                            location.reload(1);
                        }, 1000);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function adminUpdateParent(user_id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var parent_id = $("#parentId_" + user_id).val();
            var dataString = 'parent_id=' + parent_id + '&user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/update-dropdown-parent') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function() {
                            location.reload(1);
                        }, 1000);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });

        }
    </script>


    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }} List</h4>

                            @if (Auth::User()->role_id == 1)
                                <button class="btn btn-danger btn-sm" type="button" data-toggle="modal"
                                    onclick="logoutAllUsers()"> Logout All Users
                                </button>
                            @endif

                            @if (Auth::User()->role_id == 2 && $permission_download_member == 1)
                                <button class="btn btn-primary btn-sm" type="button" data-toggle="modal"
                                    data-target="#member_download_model"> Download {{ $page_title }}</button>
                            @elseif(Auth::user()->role_id != 2)
                                <button class="btn btn-primary btn-sm" type="button" data-toggle="modal"
                                    data-target="#member_download_model"> Download {{ $page_title }}</button>
                            @endif



                            @if (Auth::User()->role_id == 2 && $permission_create_member == 1)
                                <a href="{{ url('admin/create-user') }}/{{ $role_slug }}" class="btn btn-danger btn-sm">
                                    Create {{ $page_title }}</a>
                            @elseif(Auth::user()->role_id == 1 && $role_slug == 'super-admin')
                                <a href="{{ url('admin/create-super-admin') }}" class="btn btn-danger btn-sm">
                                    Create Admin</a>
                            @elseif(Auth::user()->role_id != 2)
                                <a href="{{ url('admin/create-user') }}/{{ $role_slug }}" class="btn btn-danger btn-sm">
                                    Create {{ $page_title }}</a>
                            @endif


                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">


                            <table id='my_table' class="display responsive nowrap" data-order='[[ 0, "desc" ]]'>
                                <thead>

                                    @if ($role_slug != 'super-admin')
                                        <tr>
                                            <td>User Id</td>
                                            <td>Joining Date</td>
                                            <td>Name</td>
                                            <td>Mobile</td>
                                            <td>Member Type</td>
                                            <td>Retailer ID / Distributor ID</td>
                                            <td>Normal Balance</td>
                                            <td>AEPS Balance</td>
                                            <td>Parent</td>
                                            <td>Package</td>
                                            <td>Status</td>
                                            <td>User Activity</td>
                                            <td>Statement</td>
                                            <td>Api Settings</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>User Id</td>
                                            <td>Name</td>
                                            <td>Mobile</td>
                                            <td>Role</td>
                                            <td>Status</td>
                                        </tr>
                                    @endif
                                </thead>
                            </table>

                            <!-- Script -->
                            <script type="text/javascript">
                                $(document).ready(function() {
                                    $('#my_table').DataTable({
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $url }}",
                                        columns: [

                                            @if ($role_slug != 'super-admin')
                                                {
                                                    data: 'id'
                                                }, {
                                                    data: 'joining_date'
                                                }, {
                                                    data: 'name'
                                                }, {
                                                    data: 'mobile_number'
                                                }, {
                                                    data: 'member_type'
                                                }, {
                                                    data: 'cms_agent_id'
                                                }, {
                                                    data: 'user_balance'
                                                }, {
                                                    data: 'aeps_balance'
                                                }, {
                                                    data: 'parent_name'
                                                }, {
                                                    data: 'package_name'
                                                }, {
                                                    data: 'status'
                                                }, {
                                                    data: 'is_online'
                                                }, {
                                                    data: 'statement'
                                                },{
                                                    data: 'api_settings'
                                                },
                                            @else
                                                {
                                                    data: 'id'
                                                }, {
                                                    data: 'name'
                                                }, {
                                                    data: 'mobile_number'
                                                }, {
                                                    data: 'member_type'
                                                }, {
                                                    data: 'status'
                                                },
                                            @endif
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
            <!--/div-->

        </div>

    </div>
    </div>
    </div>

    @include('common.adminMemberViewModel')
@endsection
