<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
    <title>{{ $company_name }}</title>
     <link rel="icon" href="{{asset('assets/img/trustxpay-favicon.png')}}" type="image/x-icon"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        function forgotPassword() {
            $("#forgotBtn").hide();
            $("#forgotBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile = $("#mobile_number").val();
            var dataString = 'mobile_number=' + mobile + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('merchant/forgot-password-otp')}}",
                data: dataString,
                success: function (msg) {
                    $("#forgotBtn").show();
                    $("#forgotBtn_loader").hide();
                    if (msg.status == 'success') {
                        $("#successMessage").text(msg.message);
                        $("#successMessage").show();
                        $("#otp-label").show();
                        $("#new-password-label").show();
                        $("#confirm-password-label").show();
                        $("#fogot-password-btn-label").hide();
                        $("#confirm-forgotPassword-label").show();
                    } else if (msg.status == 'validation_error') {
                        $("#mobile_errors").text(msg.errors.mobile_number);
                    } else {
                        $("#otp-label").hide();
                        $("#new-password-label").hide();
                        $("#confirm-password-label").hide();
                        $("#fogot-password-btn-label").show();
                        $("#confirm-forgotPassword-label").hide();
                        $("#failureMessage").text(msg.message);
                        $("#failureMessage").show();
                    }
                }
            });
        }

        function confirmForgotPassword() {
            // alert("confirm");
            $("#confirmForgotBtn").hide();
            $("#confirmForgotBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile = $("#mobile_number").val();
            var otp = $("#otp").val();
            var new_password = $("#new_password").val();
            var confirm_password = $("#confirm_password").val();
            var dataString = 'mobile_number=' + mobile + '&otp=' + otp +'&new_password=' +new_password + '&confirm_password=' +confirm_password +'&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('merchant/confirm-forgot-password')}}",
                data: dataString,
                success: function (msg) {
                    $("#confirmForgotBtn").show();
                    $("#confirmForgotBtn_loader").hide();
                    if (msg.status == 'success') {
                        $("#successMessage").text(msg.message);
                        $("#successMessage").show();
                        window.setTimeout(function () {
                            window.location.href = "{{url('merchant')}}";
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#mobile_errors").text(msg.errors.mobile_number);
                        $("#otp_errors").text(msg.errors.otp);
                        $("#new_password_errors").text(msg.errors.new_password);
                        $("#confirm_password_errors").text(msg.errors.confirm_password);
                    } else {
                        $("#failureMessage").text(msg.message);
                        $("#failureMessage").show();
                    }
                }
            });
        }
    </script>

</head>
<body>
@if (Auth::guest())
@else
    @if(Auth::user()->role_id <= 10)
        <script type="text/javascript">
            document.location.href = "admin/dashboard";
        </script>
    @endif
@endif
<div class="signup-form">

    <form action="#" method="post" id="login-script-form">
        {!! csrf_field() !!}
        <center><img src="{{ $cdnLink}}{{ $company_logo }}" style="height: 60px;"></center>
        <hr>
        <div class="alert alert-success" role="alert" id="successMessage" style="display: none;"></div>
        <div class="alert alert-danger" role="alert" id="failureMessage" style="display: none;"></div>

        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>Mobile Number : </label>
                    <input type="text" class="form-control" id="mobile_number" placeholder="Mobile Number">
                    <span style="color: red;" id="mobile_errors"></span>
                </div>

            </div>
        </div>

        <div class="form-group" id="otp-label" style="display: none;">
            <div class="row">
                <div class="col">
                    <label>Enter OTP : </label>
                    <input type="password" class="form-control" id="otp" placeholder="Enter OTP">
                    <span style="color: red;" id="otp_errors"></span>
                </div>

            </div>
        </div>
        <div class="form-group" id="new-password-label" style="display: none;">
            <div class="row">
                <div class="col">
                    <label>Enter New Password : </label>
                    <input type="password" class="form-control" id="new_password" placeholder="Enter Password">
                    <span style="color: red;" id="new_password_errors"></span>
                </div>

            </div>
        </div>
        <div class="form-group" id="confirm-password-label" style="display: none;">
            <div class="row">
                <div class="col">
                    <label>Enter Confirm Password : </label>
                    <input type="password" class="form-control" id="confirm_password" placeholder="Enter Confirm Password">
                    <span style="color: red;" id="confirm_password_errors"></span>
                </div>

            </div>
        </div>


        <div class="form-group" id="fogot-password-btn-label">
            <button class="btn btn-success btn-lg btn-block" type="button" id="forgotBtn" onclick="forgotPassword()">
                Forgot Password
            </button>
            <button class="btn btn-success btn-lg btn-block" type="button" id="forgotBtn_loader" disabled
                    style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                 aria-hidden="true"></span> Loading...
            </button>
        </div>

        <div class="form-group" id="confirm-forgotPassword-label" style="display: none">
            <button class="btn btn-success btn-lg btn-block" type="button" id="confirmForgotBtn"
                    onclick="confirmForgotPassword()">Confirm OTP
            </button>
            <button class="btn btn-success btn-lg btn-block" type="button" id="confirmForgotBtn_loader" disabled
                    style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                 aria-hidden="true"></span> Loading...
            </button>
        </div>
    </form>


    <div class="text-center">Already have an account? <a href="{{route('merchant.login')}}">Sign in</a></div>
</div>


<style>
    body {
        color: #fff;
        background: #63738a;
        font-family: 'Roboto', sans-serif;
    }

    .form-control {
        height: 40px;
        box-shadow: none;
        color: #969fa4;
    }

    .form-control:focus {
        border-color: #5cb85c;
    }

    .form-control, .btn {
        border-radius: 3px;
    }

    .signup-form {
        width: 500px;
        margin: 0 auto;
        padding: 30px 0;
        font-size: 15px;
    }

    .signup-form h2 {
        color: #636363;
        margin: 0 0 15px;
        position: relative;
        text-align: center;
    }

    .signup-form h2:before, .signup-form h2:after {
        content: "";
        height: 2px;
        width: 30%;
        background: #d4d4d4;
        position: absolute;
        top: 50%;
        z-index: 2;
    }

    .signup-form h2:before {
        left: 0;
    }

    .signup-form h2:after {
        right: 0;
    }

    .signup-form .hint-text {
        color: #999;
        margin-bottom: 30px;
        text-align: center;
    }

    .signup-form form {
        color: #999;
        border-radius: 3px;
        margin-bottom: 15px;
        background: #f7f7f7;
        box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
        padding: 30px;
    }

    .signup-form .form-group {
        margin-bottom: 20px;
    }

    .signup-form input[type="checkbox"] {
        margin-top: 3px;
    }

    .signup-form .btn {
        font-size: 16px;
        font-weight: bold;
        min-width: 140px;
        outline: none !important;
    }

    .signup-form .row div:first-child {
        padding-right: 10px;
    }

    .signup-form .row div:last-child {
        padding-left: 10px;
    }

    .signup-form a {
        color: #fff;
        text-decoration: underline;
    }

    .signup-form a:hover {
        text-decoration: none;
    }

    .signup-form form a {
        color: #5cb85c;
        text-decoration: none;
    }

    .signup-form form a:hover {
        text-decoration: underline;
    }
</style>

{!! csrf_field() !!}
</body>
</html>
