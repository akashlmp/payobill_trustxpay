@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function change_password(type) {
            $('#successOTPMessage').html("").hide();
            $(".loader").show();
            var token = $("input[name=_token]").val();
            // var old_password = $("#old_password").val();
            var new_password = $("#new_password").val();
            var confirm_password = $("#confirm_password").val();
            var password_otp = $('#password_otp').val();
            var dataString = 'new_password=' + new_password + '&confirm_password=' + confirm_password + '&_token=' + token +
                "&type=" + type + "&password_otp=" + password_otp;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/change-password') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        if (type == 1) {
                            $('#successOTPMessage').html(msg.message).show();
                            $('#otp_process_model').show();
                        } else {
                            $('#otp_process_model').hide();
                            swal({
                                    title: "Success",
                                    text: msg.message,
                                    type: "success",
                                    confirmButtonClass: "btn-success",
                                },
                                function() {
                                    location.reload(1);
                                });
                        }
                    } else if (msg.status == 'validation_error') {
                        $("#old_password_errors").text(msg.errors.old_password);
                        if (msg.errors.new_password == 'The new password format is invalid.') {
                            $("#new_password_errors").text(
                                'Your password must be more than 8 characters long, should contain at-least 1 Uppercase, 1 Lowercase, 1 Numeric and 1 special character.'
                                );
                        } else {
                            $("#new_password_errors").text(msg.errors.new_password);
                        }
                        $("#confirm_password_errors").text(msg.errors.confirm_password);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }


        function update_profile() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var shop_name = $("#shop_name").val();
            var office_address = $("#office_address").val();
            var dataString = 'shop_name=' + shop_name + '&office_address=' + office_address + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/update-profile') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function() {
                            location.reload(1);
                        }, 5000);
                    } else if (msg.status == 'validation_error') {
                        $("#shop_name_errors").text(msg.errors.shop_name);
                        $("#office_address_errors").text(msg.errors.office_address);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        $(document).ready(function() {
            $(".clsHideModal").click(function() {
                $('#otp_process_model').hide();
            });
        });
    </script>


    <div class="main-content-body">
        <div class="row row-sm">
            <!-- Col -->
            <div class="col-lg-5">
                <div class="card custom-card">
                    <div class="card-body text-center">

                        <div class="user-lock text-center">
                            @if (isset(Auth::User()->member->profile_photo) && !empty(Auth::User()->member->profile_photo))
                                <img alt="avatar" class="rounded-circle" src="{{ Auth::User()->member->profile_photo }}">
                            @else
                                <img alt="avatar" class="rounded-circle" src="{{ url('assets/img/profile-pic.jpg') }}">
                            @endif

                        </div>
                        <h5 class="mb-1 mt-3 card-title">{{ Auth::User()->name }} {{ Auth::User()->last_name }}</h5>
                        <p class="mb-2 mt-1 tx-inverse">
                            @if(Auth::User()->role_id==1)
                            @if(Auth::User()->id==1)
                            {{ Auth::User()->role->role_title }}
                            @else
                            Admin
                            @endif
                            @else
                            {{ Auth::User()->role->role_title }}
                            @endif
                        </p>
                        <p class="text-muted text-center mt-1">Company Name
                            : {{ Auth::User()->company->company_name }}</p>


                        <div class="mt-2 user-info btn-list">
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-mail mr-2"></i><span>
                                    {{ Auth::User()->email }}</span></a>
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-phone mr-2"></i><span>
                                    {{ Auth::User()->mobile }}</span></a>
                            <a class="btn btn-outline-light  btn-block" href="#"><i class="far fa-clock"></i>
                                <span> {{ Auth::User()->created_at }}</span></a>


                        </div>
                    </div>
                </div>

                <div class="card mg-b-20">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">Change Password</div>
                        <hr>
                        <div class="form-horizontal">


                            {{-- <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Old Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control"  placeholder="Old Password" id="old_password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="old_password_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">New Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control" placeholder="New Password"
                                            id="new_password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="new_password_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Confirm Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control" placeholder="Confirm Password"
                                            id="confirm_password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="confirm_password_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-danger btn-block" onclick="change_password(1)">Change Password
                            </button>


                        </div>
                    </div>
                </div>
                <div class="card mg-b-20">
                    <div class="card-body">
                        <div class="main-content-label tx-13 mg-b-25">
                            Kyc Details
                        </div>
                        <hr>

                        <div class="form-horizontal">
                            @if (isset(Auth::User()->member->kyc_remark) && !empty(Auth::User()->member->kyc_remark))
                                <p class="text-muted text-center mt-1">
                                <div class="alert alert-danger mg-b-0" role="alert">
                                    <strong>Remark! </strong> {{ Auth::User()->member->kyc_remark }}
                                </div>
                                </p>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (Session::has('success'))
                                <p class="alert alert-success">{{ Session::get('success') }}</p>
                            @endif


                            @if (Session::has('failure'))
                                <p class="alert alert-danger">{{ Session::get('failure') }}</p>
                            @endif

                            @if (isset(Auth::User()->member->kyc_status) && Auth::User()->member->kyc_status == 1)
                            @else
                                <form role="form" action="{{ url('admin/update-profile-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">

                                            <div class="col-md-3">
                                                <label class="form-label">Profile Photo</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="profile_photo">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger" type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <form role="form" action="{{ url('admin/update-shop-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">

                                            <div class="col-md-3">
                                                <label class="form-label">Shop Photo</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="shop_photo">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger" type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <form role="form" action="{{ url('admin/update-gst-regisration-photo') }}"
                                    method="post" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Gst Registration</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input class="form-control" type="file"
                                                        name="gst_regisration_photo">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>


                                <form role="form" action="{{ url('admin/update-pancard-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Pancard</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="pancard_photo">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>


                                <form role="form" action="{{ url('admin/cancel-cheque-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Cancel Cheque</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="cancel_cheque">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>


                                <form role="form" action="{{ url('admin/address-proof-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Address Proof</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="address_proof">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">Address Proof Front And Back</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @endif


                        </div>
                    </div>
                </div>
            </div>
            <!-- /Col -->

            <!-- Col -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">Personal Information</div>
                        <hr>
                        <form class="form-horizontal">
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Member Type</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-control select2" disabled>
                                            @foreach ($roles as $value)
                                                @if (Auth::User()->role_id)
                                                @if(Auth::User()->id==1)
                                                <option value="{{ $value->id }}"
                                                    @if (Auth::User()->role_id == $value->id) selected="selected" @endif>
                                                    {{ $value->role_title }}</option>
                                                @else
                                                <option value="{{ $value->id }}"
                                                    selected="selected">
                                                    Admin</option>
                                                @endif
                                                @else
                                                    <option value="{{ $value->id }}"
                                                        @if (Auth::User()->role_id == $value->id) selected="selected" @endif>
                                                        {{ $value->role_title }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 main-content-label">Name</div>
                            <hr>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">First Name</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" placeholder="First Name"
                                            value="{{ Auth::User()->name }}" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">last Name</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" placeholder="Last Name"
                                            value="{{ Auth::User()->last_name }}" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Email Address</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="email" class="form-control" placeholder="Email Address"
                                            value="{{ Auth::User()->email }}" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Mobile Number</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input type="number" class="form-control" placeholder="Mobile Number"
                                                value="{{ Auth::User()->mobile }}" disabled>
                                            <span class="input-group-btn">
                                                {{-- <button class="btn btn-success" type="button">Verified</button> --}}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if (Auth::User()->role_id != 1)
                                <div class="mb-4 main-content-label">permanent address</div>
                                <hr>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Address</label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea class="form-control" id="permanent_address" rows="2" placeholder="Office Address" disabled>{{ isset(Auth::User()->member->permanent_address) ? Auth::User()->member->permanent_address : '' }}</textarea>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">City</label>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="permanent_city"
                                                placeholder="City"
                                                value="{{ isset(Auth::User()->member->permanent_city) ? Auth::User()->member->permanent_city : '' }}"
                                                disabled>
                                        </div>
                                    </div>
                                </div>



                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">State</label>
                                        </div>
                                        <div class="col-md-9">
                                            <select class="form-control select2" id="permanent_state" disabled>
                                                @foreach ($circles as $value)
                                                    <option value="{{ $value->id }}"
                                                        @if (isset(Auth::User()->member->permanent_state) && Auth::User()->member->permanent_state == $value->id) selected="selected" @endif>
                                                        {{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Pin Code</label>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control" id="permanent_pin_code"
                                                placeholder="Pin Code"
                                                value="{{ Auth::User()->member->permanent_pin_code }}" disabled>
                                        </div>
                                    </div>
                                </div>



                                <div class="mb-4 main-content-label">Present address</div>
                                <hr>


                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Shop Name</label>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="shop_name"
                                                placeholder="Shope Name" value="{{ Auth::User()->member->shop_name }}">
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="shop_name_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Office Address</label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea class="form-control" id="office_address" rows="2" placeholder="Office Address">{{ Auth::User()->member->office_address }}</textarea>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="office_address_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif


                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light"
                            onclick="update_profile()">Update Profile
                        </button>
                    </div>
                </div>
            </div>
            <!-- /Col -->
        </div>


    </div>
    <div class="modal show" id="otp_process_model" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo" style="border: 1px solid #9c96c1;">
                <div class="modal-header">
                    <h6 class="modal-title">OTP</h6>
                    <button aria-label="Close" class="close clsHideModal" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="alert alert-success" role="alert" id="successOTPMessage" style="">
                                </div>
                                <div class="form-group">
                                    <label for="name" id="">Enter OTP</label>
                                    <input type="password" required id="password_otp" class="form-control"
                                        placeholder="Enter OTP">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="password_otp_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="verify-pass_btn" data-type=""
                        onclick="change_password(2)">Verify
                    </button>

                    <button class="btn ripple btn-secondary clsHideModal" data-dismiss="modal" type="button">Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->

@endsection
