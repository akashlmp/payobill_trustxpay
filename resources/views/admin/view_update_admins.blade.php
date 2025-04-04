@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function create_users() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var name = $("#name").val();
            var user_id = $("#user_id").val();

            var last_name = $("#last_name").val();

            var email = $("#email").val();
            var mobile = $("#mobile").val();
            var role = $('#assign_role').val();
            var dataString = 'name=' + name + '&last_name=' + last_name + '&user_id=' + user_id + '&email=' + email +
                '&mobile=' + mobile + '&_token=' + token + "&role=" + role;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/update-admins') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal({
                            title: "Success",
                            text: msg.message,
                            type: "success",
                            showCancelButton: false
                        }, function() {
                            window.location.href="{{url('/')}}/admin/super-admin";
                        });
                    } else if (msg.status == 'validation_error') {
                        $("#name_errors").text(msg.errors.name);

                        $("#last_name_errors").text(msg.errors.last_name);

                        $("#email_errors").text(msg.errors.email);
                        $("#mobile_errors").text(msg.errors.mobile);

                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function create_pancard_id() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var user_id = $("#user_id").val();
            var dataString = 'user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/create-pancard-id') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function() {
                            location.reload(1);
                        }, 3000);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function viewActiveService() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var user_id = $("#user_id").val();
            var dataString = '&user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/view-user-active-services') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var active_services = msg.active_services;
                        $.each(active_services.split(","), function(i, e) {
                            $("#active_services option[value='" + e + "']").prop("selected", true);
                        });
                        $('#active_services').trigger('change');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>

    <div class="main-content-body">
        {{-- perssinal details --}}
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Basic details</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">

                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">First Name</label>
                                        <input type="text" id="name" value="{{ $name }}"
                                            class="form-control" placeholder="First Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="name_errors"></li>
                                        </ul>

                                    </div>
                                </div>



                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Last Name </label>
                                        <input type="text" id="last_name" value="{{ $last_name }}"
                                            class="form-control" placeholder="Last Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="last_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>



                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Email Address</label>
                                        <input type="text" id="email" value="{{ $email }}"
                                            class="form-control" placeholder="Email Address">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="email_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Mobile Number</label>
                                        <input type="text" id="mobile" value="{{ $mobile }}"
                                            class="form-control" placeholder="Mobile Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="mobile_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="form-label">Assign Role <span class="text-danger">*</span></label>
                                        <select class="form-control filter_single_select2" name="role" id="assign_role"
                                            data-placeholder="Select">
                                            <option value="">Select Role</option>
                                            @foreach ($roles as $id => $name)
                                                <option value="{{ $id }}"
                                                    @if ($assign_role == $id) selected @endif>{{ $name ?? '--' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="assign_role_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <input type="hidden" value="{{ $user_id }}" id="user_id">
                        <button type="submit" class="btn btn-success waves-effect waves-light" onclick="create_users()">
                            Save
                            Details
                        </button>

                        <a href="{{ url()->previous() }}" class="btn btn-danger waves-effect waves-light"><i
                                class="fas fa-backward"></i> Back</a>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>


    </div>
    </div>
    </div>
@endsection
