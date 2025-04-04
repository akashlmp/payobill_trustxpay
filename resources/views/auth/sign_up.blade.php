<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
    <title>{{ $company_name }}</title>
    <link rel="icon" href="{{ asset('assets/img/trustxpay-favicon.png') }}" type="image/x-icon" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <link href="{{ url('assets/plugins/sweet-alert/sweetalert.css') }}" rel="stylesheet">
    <script src="{{ url('assets/plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ url('assets/plugins/sweet-alert/jquery.sweet-alert.js') }}"></script>

    <script type="text/javascript">
        function sign_up() {
            $("#registerBtn").hide();
            $("#registerBtn_loader").show();
            var token = $("input[name=_token]").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var fullname = $("#fullname").val();
            var middle_name = $("#middle_name").val();
            var gender = $("#gender").val();
            var mobile = $("#mobile").val();
            var dob = $("#dob").val();
            var email = $("#email").val();
            var shop_name = $("#shop_name").val();
            var address = $("#address").val();
            var pin_code = $("#pin_code").val();
            var city = $("#city").val();
            var pan_number = $("#pan_number").val();
            var referral_code = $("#referral_code").val();
            var is_check = 0
            if ($("#is_check").is(':checked')) {
                is_check = 1;
            }
            var dataString = 'first_name=' + first_name + '&middle_name=' + middle_name + '&fullname=' + fullname + '&last_name=' + last_name + '&mobile=' + mobile + '&gender=' + gender + '&dob=' + dob + '&email=' +
                email + '&shop_name=' + shop_name + '&address=' + address + '&pin_code=' + pin_code + '&city=' + city +
                '&referral_code=' + referral_code + '&pan_number=' + pan_number + '&_token=' + token + '&is_check=' +
                is_check;
            $(".errors").text('');
            $.ajax({
                type: "POST",
                url: "{{ url('sign-up') }}",
                data: dataString,
                success: function(msg) {
                    $("#registerBtn").show();
                    $("#registerBtn_loader").hide();
                    if (msg.status == 'success') {
                        swal({
                                title: "Success",
                                text: msg.message,
                                type: "success",
                                confirmButtonClass: "btn-success",
                            },
                            function() {
                                window.location.href = "{{ url('login') }}";
                            });
                        // $("#successMessage").text(msg.message);
                        // $("#successMessage").show();
                        // window.setTimeout(function () {
                        //     window.location.href = "{{ url('login') }}";
                        // }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#middle_name_errors").text(msg.errors.middle_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#fullname_errors").text(msg.errors.fullname);
                        $("#gender_errors").text(msg.errors.gender);
                        $("#mobile_errors").text(msg.errors.mobile);
                        $("#dob_errors").text(msg.errors.dob);
                        $("#email_errors").text(msg.errors.email);
                        $("#shop_name_errors").text(msg.errors.shop_name);
                        $("#address_errors").text(msg.errors.address);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#city_errors").text(msg.errors.city);
                        $("#pan_number_errors").text(msg.errors.pan_number);
                        if (msg.errors.is_check) {
                            $("#is_check_errors").text(msg.errors.is_check);
                        } else {
                            $("#is_check_errors").text('');
                        }
                    } else {
                        alert(msg.message);
                    }
                }
            });
        }

        function getZipcodeDetails() {
            var token = $("input[name=_token]").val();
            var pin_code = $("#pin_code").val();
            var dataString = 'zipcode=' + pin_code + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('getZipcodeDetails') }}",
                data: dataString,
                success: function(msg) {
                    if (msg.status == '1') {
                            $('#city').val(msg.data.city)
                    }
                }
            });
        }
        $(document).on("keyup", "#pin_code", function() {
            if ($("#pin_code").val().length > 5) {
                getZipcodeDetails();
            }
        });
    </script>
</head>

<body>
    <div class="signup-form">
        <form action="/examples/actions/confirmation.php" method="post">
            <center><img src="{{ $cdnLink }}{{ $company_logo }}" style="height: 60px;"></center>
            <hr>
            <div class="alert alert-success" role="alert" id="successMessage" style="display: none;"></div>
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>First Name : </label>
                        <input type="text" class="form-control" id="first_name" placeholder="First Name">
                        <span class="errors" style="color: red;" id="first_name_errors"></span>
                    </div>
                    <div class="col">
                        <label>Last Name : </label>
                        <input type="text" class="form-control" id="last_name" placeholder="Last Name">
                        <span class="errors" style="color: red;" id="last_name_errors"></span>
                    </div>
                {{-- <div class="col">
                    <label>Middle Name : </label>
                    <input type="text" class="form-control" id="middle_name" placeholder="Middle Name">
                    <span class="errors" style="color: red;" id="middle_name_errors"></span>
                </div> --}}
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>Full Name (As per Aadhaar) :  </label>
                    <input type="text" class="form-control" id="fullname" placeholder="Full Name">
                    <span class="errors" style="color: red;" id="fullname_errors"></span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">

                <div class="col">
                    <label>Gender : </label>
                    <select class="form-control" id="gender">
                        <option value="">-- Select --</option>
                        <option value="MALE">Male</option>
                        <option value="FEMALE">Female</option>
                    </select>
                    <span class="errors" style="color: red;" id="gender_errors"></span>
                </div>
                <div class="col">
                    <label>Date of Birth : </label>
                    <input type="date" class="form-control" id="dob">
                    <span class="errors" style="color: red;" id="dob_errors"></span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>Email Address : </label>
                    <input type="text" class="form-control" id="email" placeholder="Email Address">
                    <span class="errors" style="color: red;" id="email_errors"></span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>Mobile Number : </label>
                    <input type="text" class="form-control" id="mobile" placeholder="Mobile Number">
                    <span class="errors" style="color: red;" id="mobile_errors"></span>
                </div>
                <div class="col">
                    <label>Shop Name : </label>
                    <input type="text" class="form-control" id="shop_name" placeholder="Shop Name">
                    <span class="errors" style="color: red;" id="shop_name_errors"></span>
                </div>
            </div>
        </div>

            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Address : </label>
                        <input type="text" class="form-control" id="address" placeholder="Address">
                        <span class="errors" style="color: red;" id="address_errors"></span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Pin Code : </label>
                        <input type="text" class="form-control" id="pin_code" placeholder="Pin Code">
                        <span class="errors" style="color: red;" id="pin_code_errors"></span>
                    </div>

                    <div class="col">
                        <label>City : </label>
                        <input type="text" class="form-control" id="city" placeholder="City">
                        <span class="errors" style="color: red;" id="city_errors"></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Pan Number: </label>
                        <input type="text" class="form-control" id="pan_number" placeholder="Pan Number">
                        <span class="errors" style="color: red;" id="pan_number_errors"></span>
                    </div>


                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <input type="checkbox" id="is_check" name="is_check">
                        <label for="is_check"> Requested for accept <a target="_blank"
                                href="{{ url('term-conditions') }}" class="text-underline">Terms
                                and Conditions</a></label></br>
                        <span class="errors" style="color: red;" id="is_check_errors"></span>
                    </div>
                </div>
            </div>

            <input type="hidden" class="form-control" id="referral_code" placeholder="Referral Code"
                value="{{ $referral_code }}" @if ($referral_code) disabled @endif>

            {{-- <div class="form-group">
             <label class="form-check-label"><input type="checkbox" required="required"> I accept the <a href="#">Terms of Use</a> &amp; <a href="#">Privacy Policy</a></label>
         </div> --}}
            <div class="form-group">
                <button class="btn btn-success btn-lg btn-block" type="button" id="registerBtn"
                    onclick="sign_up()">Register
                    Now
                </button>
                <button class="btn btn-success btn-lg btn-block" type="button" id="registerBtn_loader" disabled
                    style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                        aria-hidden="true"></span> Loading...
                </button>
            </div>
        </form>
        <div class="text-center">Already have an account? <a href="{{ url('login') }}">Sign in</a></div>
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

        .form-control,
        .btn {
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

        .signup-form h2:before,
        .signup-form h2:after {
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
