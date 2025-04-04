@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function create_users() {
            $(".loader").show();
            $(".parsley-required").text('');
            var token = $("input[name=_token]").val();
            var name = $("#name").val();

            var last_name = $("#last_name").val();

            var email = $("#email").val();
            var mobile = $("#mobile").val();
            var password = $("#password").val();
            var cpassword = $("#cpassword").val();
            var role = $('#assign_role').val();
            var dataString = 'name=' + name + '&last_name=' + last_name + '&password=' + password + '&cpassword=' +
                cpassword + '&email=' + email + '&mobile=' + mobile + '&_token=' + token + "&role="+role;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/store-admin-users') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function() {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#name_errors").text(msg.errors.name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#email_errors").text(msg.errors.email);
                        $("#mobile_errors").text(msg.errors.mobile);
                        $("#password_errors").text(msg.errors.password);
                        $("#cpassword_errors").text(msg.errors.cpassword);
                        $("#assign_role_errors").text(msg.errors.role);
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
                                        <input type="text" id="name" class="form-control" placeholder="First Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="name_errors"></li>
                                        </ul>

                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Last Name </label>
                                        <input type="text" id="last_name" class="form-control" placeholder="Last Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="last_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Email Address</label>
                                        <input type="text" id="email" class="form-control"
                                            placeholder="Email Address">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="email_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Mobile Number</label>
                                        <input type="text" id="mobile" class="form-control"
                                            placeholder="Mobile Number">
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
                                                <option value="{{ $id }}">{{ $name ?? '--' }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="assign_role_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" id="password" class="form-control" placeholder="Password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="password_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="cpassword">Confirm Password</label>
                                        <input type="password" id="cpassword" class="form-control"
                                            placeholder="Confirm Password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="cpassword_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light" onclick="create_users()">
                            Save Details
                        </button>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>


    </div>
    </div>
    </div>
@endsection
