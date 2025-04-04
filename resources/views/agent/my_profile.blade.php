@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">
        function transaction_change_password(type) {
            $('#successTOTPMessage').html("").hide();
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var new_password = $("#transaction_new_password").val();
            var confirm_password = $("#transaction_confirm_password").val();
            var password_otp = $('#trans_password_otp').val();
            var dataString = 'new_password=' + new_password + '&confirm_password=' +
                confirm_password + '&_token=' + token + "&type=" + type + "&password_otp=" + password_otp;
            $.ajax({
                type: "POST",
                url: "{{ url('agent/transaction-change-password') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        if (type == 1) {
                            $('#successTOTPMessage').html(msg.message).show();
                            $('#trans_otp_process_model').show();
                        } else {
                            $('#trans_otp_process_model').hide();
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
                        if (msg.errors.new_password == 'The new password format is invalid.') {
                            $("#transaction_new_password_errors").text(
                                'Your password must be more than 8 characters long, should contain at-least 1 Uppercase, 1 Lowercase, 1 Numeric and 1 special character.'
                            );
                        } else {
                            $("#transaction_new_password_errors").text(msg.errors.new_password);
                        }
                        $("#transaction_confirm_password_errors").text(msg.errors.confirm_password);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

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
                url: "{{ url('agent/change-password') }}",
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


        function update_profile() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var shop_name = $("#shop_name").val();
            var office_address = $("#office_address").val();
            var pay_name = $("#pay_name").val();
            var bank_name = $("#bank_name").val();
            var ifsc_code = $("#ifsc_code").val();
            var bank_account_no = $("#bank_account_no").val();
            var dataString = 'shop_name=' + shop_name + '&office_address=' + office_address + '&pay_name='+ pay_name +'&bank_name='+ bank_name +'&ifsc_code='+ ifsc_code +'&bank_account_no=' +bank_account_no + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('agent/update-profile') }}",
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
                        $("#pay_name_errors").text(msg.errors.pay_name);
                        $("#bank_name_errors").text(msg.errors.bank_name);
                        $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                        $("#bank_account_no_errors").text(msg.errors.bank_account_no);
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

            $(".clsTransHideModal").click(function() {
                $('#trans_otp_process_model').hide();
            });
        });
    </script>


    <div class="main-content-body">
        <div class="row row-sm">
            <!-- Col -->
            <div class="col-lg-5">

                {{-- // --}}
                <div class="card custom-card">
                    <div class="card-body text-center">

                        <div class="user-lock text-center">
                            @if (Auth::User()->member->profile_photo)
                                <img alt="avatar" class="rounded-circle"
                                    src="{{ $cdnLink }}{{ Auth::User()->member->profile_photo }}">
                            @else
                                <img alt="avatar" class="rounded-circle" src="{{ url('assets/img/profile-pic.jpg') }}">
                            @endif

                        </div>
                        <h5 class="mb-1 mt-3 card-title">{{ Auth::User()->name }} {{ Auth::User()->last_name }}</h5>
                        <p class="mb-2 mt-1 tx-inverse">{{ Auth::User()->role->role_title }}</p>
                        <p class="text-muted text-center mt-1">Company Name : {{ Auth::User()->company->company_name }}</p>


                        <div class="mt-2 user-info btn-list">
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-mail mr-2"></i><span>
                                    {{ Auth::User()->email }}</span></a>
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-phone mr-2"></i><span>
                                    {{ Auth::User()->mobile }}</span></a>
                            <a class="btn btn-outline-light  btn-block" href="#"><i class="far fa-clock"></i> <span>
                                    {{ Auth::User()->created_at }}</span></a>


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
                <div class="card mg-b-20">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">Change Transaction Password</div>
                        <hr>
                        <div class="form-horizontal">
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">New Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control"
                                            placeholder="New Password" id="transaction_new_password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="transaction_new_password_errors"></li>
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
                                        <input type="password" class="form-control"
                                            placeholder="Confirm Password" id="transaction_confirm_password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="transaction_confirm_password_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-danger btn-block" onclick="transaction_change_password(1)">Change
                                Password</button>


                        </div>
                    </div>
                </div>
                <div class="card mg-b-20">
                    <div class="card-body">
                        <div class="main-content-label tx-13 mg-b-25">
                            Kyc Details <a href="{{ url('agent/view-kyc') }}" style="float: right">View Kyc</a>
                        </div>
                        <hr>

                        <div class="form-horizontal">



                            @if (Auth::User()->member->kyc_remark)
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



                            <form role="form" action="{{ url('agent/update-profile-photo') }}" method="post"
                                enctype="multipart/form-data">
                                {!! csrf_field() !!}
                                <div class="form-group ">
                                    <div class="row">

                                        <div class="col-md-3">
                                            <label class="form-label">Profile Photo</label>
                                        </div>
                                        <div class="{{ Auth::User()->member->profile_photo  ? 'col-md-7' :  'col-md-9' }}">
                                            <div class="input-group">
                                                <input class="form-control" type="file" name="profile_photo">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-danger" type="submit">Upload</button></span>
                                            </div>
                                        </div>
                                        @if(Auth::User()->member->profile_photo)
                                        <div class="col-md-2">
                                            <div class="input-group">

                                                <span class="input-group-btn">
                                                    <a href="{{$cdnLink}}{{ Auth::User()->member->profile_photo }}" target="_blank"
                                                    class="btn btn-success btn-block mb-0">Show</a>
                                                </span>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                            @if (Auth::User()->member->kyc_status == 1)
                            @else
                                <form role="form" action="{{ url('agent/update-shop-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">

                                            <div class="col-md-3">
                                                <label class="form-label">Shop Photo</label>
                                            </div>
                                            <div class="{{ Auth::User()->member->shop_photo  ? 'col-md-7' :  'col-md-9' }}">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="shop_photo">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                            @if(Auth::User()->member->shop_photo)
                                            <div class="col-md-2">
                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <a href="{{$cdnLink}}{{ Auth::User()->member->shop_photo }}" target="_blank"
                                                        class="btn btn-success btn-block mb-0">Show</a>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>

                                <form role="form" action="{{ url('agent/update-gst-regisration-photo') }}"
                                    method="post" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Gst Registration</label>
                                            </div>
                                            <div class="{{ Auth::User()->member->gst_regisration_photo  ? 'col-md-7' :  'col-md-9' }}">
                                                <div class="input-group">
                                                    <input class="form-control" type="file"
                                                        name="gst_regisration_photo">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                            @if(Auth::User()->member->gst_regisration_photo)
                                            <div class="col-md-2">
                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <a href="{{$cdnLink}}{{ Auth::User()->member->gst_regisration_photo }}" target="_blank"
                                                        class="btn btn-success btn-block mb-0">Show</a>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>


                                <form role="form" action="{{ url('agent/update-pancard-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Pancard</label>
                                            </div>
                                            <div class="{{ Auth::User()->member->pancard_photo  ? 'col-md-7' :  'col-md-9' }}">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="pancard_photo">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                            @if(Auth::User()->member->pancard_photo)
                                            <div class="col-md-2">
                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <a href="{{$cdnLink}}{{ Auth::User()->member->pancard_photo }}" target="_blank"
                                                        class="btn btn-success btn-block mb-0">Show</a>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>


                                <form role="form" action="{{ url('agent/cancel-cheque-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Cancel Cheque</label>
                                            </div>
                                            <div class="{{ Auth::User()->member->cancel_cheque  ? 'col-md-7' :  'col-md-9' }}">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="cancel_cheque">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                            </div>
                                            @if(Auth::User()->member->cancel_cheque)
                                            <div class="col-md-2">
                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <a href="{{$cdnLink}}{{ Auth::User()->member->cancel_cheque }}" target="_blank"
                                                        class="btn btn-success btn-block mb-0">Show</a>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>


                                {{-- <form role="form" action="{{ url('agent/address-proof-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Address Proof</label>
                                            </div>
                                            <div class="{{ Auth::User()->member->address_proof  ? 'col-md-7' :  'col-md-9' }}">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="address_proof">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                                <!-- <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">Address Proof Front And Back</li>
                                                </ul> -->
                                            </div>
                                            @if(Auth::User()->member->address_proof)
                                            <div class="col-md-2">
                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <a href="{{$cdnLink}}{{ Auth::User()->member->address_proof }}" target="_blank"
                                                        class="btn btn-success btn-block mb-0">Show</a>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </form> --}}

                                <form role="form" action="{{ url('agent/aadhar-front-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Aadhar Front                                                </label>
                                            </div>
                                            <div class="{{ Auth::User()->member->aadhar_front  ? 'col-md-7' :  'col-md-9' }}">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="aadhar_front">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                                <!-- <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">Address Proof Front And Back</li>
                                                </ul> -->
                                            </div>
                                            @if(Auth::User()->member->aadhar_front)
                                            <div class="col-md-2">
                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <a href="{{$cdnLink}}{{ Auth::User()->member->aadhar_front }}" target="_blank"
                                                        class="btn btn-success btn-block mb-0">Show</a>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                                <form role="form" action="{{ url('agent/aadhar-back-photo') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Aadhar Back                                                </label>
                                            </div>
                                            <div class="{{ Auth::User()->member->aadhar_back  ? 'col-md-7' :  'col-md-9' }}">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="aadhar_back">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                                <!-- <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">Address Proof Front And Back</li>
                                                </ul> -->
                                            </div>
                                            @if(Auth::User()->member->aadhar_back)
                                            <div class="col-md-2">
                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <a href="{{$cdnLink}}{{ Auth::User()->member->aadhar_back }}" target="_blank"
                                                        class="btn btn-success btn-block mb-0">Show</a>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>

                                <form role="form" action="{{ url('agent/agreement-form-doc') }}" method="post"
                                    enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Agreement/ASM Recommendation Form                                                </label>
                                            </div>
                                            <div class="{{ Auth::User()->member->agreement_form  ? 'col-md-7' :  'col-md-9' }}">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="agreement_form">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-danger"
                                                            type="submit">Upload</button></span>
                                                </div>
                                                <!-- <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">Address Proof Front And Back</li>
                                                </ul> -->
                                            </div>
                                            @if(Auth::User()->member->agreement_form)
                                            <div class="col-md-2">
                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <a href="{{$cdnLink}}{{ Auth::User()->member->agreement_form }}" target="_blank"
                                                        class="btn btn-success btn-block mb-0">Show</a>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
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
                                                <option value="{{ $value->id }}"
                                                    @if (Auth::User()->role_id == $value->id) selected="selected" @endif>
                                                    {{ $value->role_title }}</option>
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
                                        <label class="form-label">Full Name</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" placeholder="Full Name"
                                            value="{{ Auth::User()->fullname }}" disabled>
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
                                                <button class="btn btn-success" type="button">Verified</button>
                                            </span>
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
                                        <textarea class="form-control" id="permanent_address" rows="2" placeholder="Office Address" disabled>{{ Auth::User()->member->permanent_address }}</textarea>
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
                                                    @if (Auth::User()->member->permanent_state == $value->id) selected="selected" @endif>
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
                                            placeholder="City" value="{{ Auth::User()->member->permanent_city }}"
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
                                            placeholder="Pin Code" value="{{ Auth::User()->member->permanent_pin_code }}"
                                            disabled>
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

                            <div class="mb-4 main-content-label">Bank Detail</div>
                            <hr>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Pay Name</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="pay_name"
                                            placeholder="Pay Name" value="{{ Auth::User()->member->pay_name }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pay_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Bank Name</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="bank_name"
                                            placeholder="Bank Name" value="{{ Auth::User()->member->bank_name }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="bank_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">IFSC Code</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="ifsc_code"
                                            placeholder="IFSC Code" value="{{ Auth::User()->member->ifsc_code }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="ifsc_code_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Bank Account Number</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="bank_account_no"
                                            placeholder="Bank Account Number" value="{{ Auth::User()->member->bank_account_no }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="bank_account_no_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>





                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light"
                            onclick="update_profile()">Update Profile</button>
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
    <div class="modal show" id="trans_otp_process_model" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo" style="border: 1px solid #9c96c1;">
                <div class="modal-header">
                    <h6 class="modal-title">OTP</h6>
                    <button aria-label="Close" class="close clsTransHideModal" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="alert alert-success" role="alert" id="successTOTPMessage" style="">
                                </div>
                                <div class="form-group">
                                    <label for="name" id="">Enter OTP</label>
                                    <input type="password" required id="trans_password_otp" class="form-control"
                                        placeholder="Enter OTP">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="trans_password_otp_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="verify-pass_btn" data-type=""
                        onclick="transaction_change_password(2)">Verify
                    </button>

                    <button class="btn ripple btn-secondary clsTransHideModal" data-dismiss="modal"
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
