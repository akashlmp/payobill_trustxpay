@extends('admin.layout.header')
@section('content')


    <script type="text/javascript">
        $(document).ready(function () {
            $("#active_services").select2();
            $('.onlyNumberDot').on('input', function (event) {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });
        });

        function get_permanent_city() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var state_id = $("#state_id").find(":selected").text().trim();
            var dataString = 'state_id=' + state_id + '&_token=' + token;
            $.ajax({
                type: "post",
                url: "{{url('admin/get-city-by-state')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var cities = msg.cities;
                        console.log(cities)
                        var html = "<option value=''>Select City</option>";
                        for (var key in cities) {
                            html += '<option value="' + cities[key].city + '">' + cities[key].city + ' </option>';
                        }
                        $("#city").html(html);

                    } else {
                        alert(msg.message);
                    }
                }
            });
        }

        function create_users() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var last_name = $("#last_name").val();
            var first_name = $("#first_name").val();
            var email = $("#email").val();
            var mobile = $("#mobile").val();
            var address = $("#address").val();
            var city = $("#city").val();
            var pin_code = $("#pin_code").val();
            var state_id = $("#state_id").val();
            var pan_number = $("#pan_number").val();
            var gst_number = $("#gst_number").val();
            var active_services = $("#active_services").val();
            var is_ip_whiltelist = $("#is_ip_whiltelist").val();
            var status = $("#status").val();
            var merchant_ip = $("#merchant_ip").val();
            var server_ip = $("#server_ip").val();
            var latitude = $("#latitude").val();
            var longitude = $("#longitude").val();
            var callback_url = $("#callback_url").val();
            var dataString = 'first_name=' + first_name + '&last_name=' + last_name + '&email=' + email + '&mobile_number=' + mobile + '&address=' + address + '&city=' + city + '&state_id=' + state_id + '&pin_code=' + pin_code + '&pan_number=' + pan_number + '&gst_number=' + gst_number + '&active_services=' + active_services + '&merchant_ip=' + merchant_ip + '&is_ip_whiltelist=' + is_ip_whiltelist + '&status=' + status + '&_token=' + token + "&latitude="+latitude+"&longitude="+longitude+"&callback_url="+callback_url+"&server_ip="+server_ip;
            $.ajax({
                type: "POST",
                url: "{{url('admin/store-merchant-members')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#email_errors").text(msg.errors.email);
                        $("#mobile_errors").text(msg.errors.mobile);
                        $("#address_errors").text(msg.errors.address);
                        $("#city_errors").text(msg.errors.city);
                        $("#state_id_errors").text(msg.errors.state_id);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#pan_number_errors").text(msg.errors.pan_number);
                        $("#gst_number_errors").text(msg.errors.gst_number_errors);
                        $("#callback_url_errors").text(msg.errors.callback_url);
                        $("#longitude_errors").text(msg.errors.longitude);
                        $("#latitude_errors").text(msg.errors.latitude);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>

    <div class="main-content-body">
        {{--perssinal details--}}
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
                                        <input type="text" id="first_name" class="form-control"
                                               placeholder="First Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="first_name_errors"></li>
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
                                        <input type="text" id="email" class="form-control" placeholder="Email Address">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="email_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Mobile Number</label>
                                        <input type="text" id="mobile" class="form-control" placeholder="Mobile Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="mobile_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pan Number</label>
                                        <input type="text" id="pan_number" class="form-control"
                                               placeholder="Pan Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pan_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">GST Number</label>
                                        <input type="text" id="gst_number" class="form-control"
                                               placeholder="GST Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="gst_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>  <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="merchant_ip">Merchant ip</label>
                                        <input type="text" id="merchant_ip" class="form-control"
                                               placeholder="Merchant ip">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="merchant_ip_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="server_ip">Server ip</label>
                                        <input type="text" id="server_ip" class="form-control"
                                               placeholder="Server ip">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="server_ip_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="is_ip_whiltelist">Ip whilte list?</label>
                                        <select class="form-control select2" id="is_ip_whiltelist"
                                                style="width: 100%;">
                                            <option value="1">ON</option>
                                            <option value="0">Off</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="is_ip_whiltelist">Status</label>
                                        <select class="form-control select2" id="status"
                                                style="width: 100%;">
                                            <option value="1">Active</option>
                                            <option value="0">In-Active</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Webhook Url</label>
                                        <input type="text" id="callback_url" class="form-control" placeholder="https://example.com/api/call-back/response">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="callback_url_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>
        {{--perssinal details clase--}}



        {{--Permanent details--}}

        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Permanent details </h4>

                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">

                        <div class="form-body">
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Address</label>
                                        <input class="form-control" id="address" placeholder="Address">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="address_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">State</label>
                                        <select class="form-control select2" id="state_id"
                                                onchange="get_permanent_city(this)">
                                            <option value="">Select State</option>
                                            @foreach($state as $value)
                                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="state_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">City</label>
                                        <select class="form-control select2" id="city">
                                            <option value="">Select City</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="city_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pincode</label>
                                        <input type="text" id="pin_code" class="form-control" placeholder="Pincode">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pin_code_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Latitude</label>
                                        <input type="text" id="latitude" class="form-control onlyNumberDot" maxlength="9" placeholder="28.535517">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="latitude_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Longitude</label>
                                        <input type="text" id="longitude" class="form-control onlyNumberDot" maxlength="9" placeholder="77.391029">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="longitude_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>
        {{--Permanent details close--}}



        {{--service detail--}}
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <input type="hidden" name="active_services" id="active_services" value=""/>
                    {{-- <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Service</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">

                                @if(Auth::User()->role_id == 1)
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Active Service</label>
                                            <select class="form-control select2" id="active_services"
                                                    style="width: 100%" multiple>
                                                @foreach($services as $value)
                                                    <option value="{{ $value->id }}">{{ $value->service_name }}</option>
                                                @endforeach
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="active_services_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                @endif


                            </div>

                        </div>
                    </div> --}}
                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light" onclick="create_users()">
                            Save Details
                        </button>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>
        {{--service detail close--}}


    </div>
    </div>
    </div>




@endsection
