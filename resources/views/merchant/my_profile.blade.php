@extends('merchant.layouts.main')
@section('title','Merchant Profile')
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
            var dataString = 'new_password=' + new_password + '&confirm_password=' +
                confirm_password + '&_token=' + token + "&type=" + type + "&password_otp=" + password_otp;
            $.ajax({
                type: "POST",
                url: "{{ url('merchant/change-password') }}",
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
                        swal("Faild", msg.message, "error");
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

                            <img alt="avatar" class="rounded-circle" src="{{ url('assets/img/profile-pic.jpg') }}">
                        </div>
                        <h5 class="mb-1 mt-3 card-title">{{ Auth::guard('merchant')->user()->first_name }} {{ Auth::guard('merchant')->user()->last_name }}</h5>
                        <p class="mb-2 mt-1 tx-inverse">Merchant</p>


                        <div class="mt-2 user-info btn-list">
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-mail mr-2"></i><span>
                                    {{ Auth::guard('merchant')->user()->email }}</span></a>
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-phone mr-2"></i><span>
                                    {{ Auth::guard('merchant')->user()->mobile_number }}</span></a>
                            <a class="btn btn-outline-light  btn-block" href="#"><i class="far fa-clock"></i> <span>
                                    {{ Auth::guard('merchant')->user()->created_at }}</span></a>


                        </div>
                    </div>
                </div>

                <div class="card mg-b-20">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">Change Login Password</div>
                        <hr>
                        <div class="form-horizontal">


                            {{-- <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Old Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control" placeholder="Old Password"
                                            id="old_password">
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
                            <button class="btn btn-danger btn-block" onclick="change_password(1)">Change Password</button>


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

                            <div class="mb-4 main-content-label">Name</div>
                            <hr>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">First Name</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" placeholder="First Name"
                                            value="{{ Auth::guard('merchant')->user()->first_name }}" disabled>
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
                                            value="{{ Auth::guard('merchant')->user()->last_name }}" disabled>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Full Name</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" placeholder="Full Name"
                                            value="{{ Auth::User()->fullname }}" disabled>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Email Address</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="email" class="form-control" placeholder="Email Address"
                                            value="{{ Auth::guard('merchant')->user()->email }}" disabled>
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
                                                value="{{ Auth::guard('merchant')->user()->mobile_number }}" disabled>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Pan Number</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Pan Number"
                                                value="{{ Auth::guard('merchant')->user()->pan_number }}" disabled>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 main-content-label">permanent address </div>
                            <hr>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Address</label>
                                    </div>
                                    <div class="col-md-9">
                                        <textarea class="form-control" id="permanent_address" rows="2" placeholder="Office Address" disabled>{{ Auth::guard('merchant')->user()->address }}</textarea>
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
                                                    @if (Auth::guard('merchant')->user()->state == $value->id) selected="selected" @endif>
                                                    {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>


                            {{-- <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">District</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-control select2" id="permanent_state" disabled>
                                            @foreach ($district as $value)
                                                <option value="{{ $value->id }}"
                                                    @if (Auth::User()->member->permanent_district == $value->id) selected="selected" @endif>
                                                    {{ $value->district_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">City</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="permanent_city"
                                            placeholder="City" value="{{ Auth::guard('merchant')->user()->city }}"
                                            disabled>
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
                                            placeholder="Pin Code" value="{{ Auth::guard('merchant')->user()->pincode }}"
                                            disabled>
                                    </div>
                                </div>
                            </div>






                        </form>
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

                    <button class="btn ripple btn-secondary clsHideModal" data-dismiss="modal"
                        type="button">Close</button>
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
