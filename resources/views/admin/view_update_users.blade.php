@extends('admin.layout.header')
@section('content')


    <script type="text/javascript">
        $(document).ready(function() {
            $("#gender").select2();
            $("#role_id").select2();
            $("#scheme_id").select2();
            $("#company_id").select2();
            $("#parent_id").select2();
            $("#gst_type").select2();
            $("#user_gst_type").select2();
            $("#day_book").select2();
            $("#monthly_statement").select2();
            $("#active_services").select2();
            viewActiveService();

            $("#dob").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
                yearRange: "1900:{{ date('Y') }}",
                maxDate: 0
            });
        });

        function get_permanent_distric() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var state_id = $("#state_id").val();
            var dataString = 'state_id=' + state_id + '&_token=' + token;
            $.ajax({
                type: "post",
                url: "{{ url('admin/get-distric-by-state') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var districts = msg.districts;
                        var html = "";
                        for (var key in districts) {
                            html += '<option value="' + districts[key].district_id + '">' + districts[key]
                                .district_name + ' </option>';
                        }
                        $("#district_id").html(html);

                    } else {
                        alert(msg.message);
                    }
                }
            });
        }

        function get_permanent_city() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var state_id = $("#state_id").find(":selected").text().trim();
            var dataString = 'state_id=' + state_id + '&_token=' + token;
            $.ajax({
                type: "post",
                url: "{{ url('admin/get-city-by-state') }}",
                data: dataString,
                success: function(msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var cities = msg.cities;
                        console.log(cities)
                        var html = "<option value=''>Select City</option>";
                        for (var key in cities) {
                            html += '<option value="' + cities[key].city + '">' + cities[key].city +
                                ' </option>';
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
            var name = $("#name").val();
            var middle_name = $("#middle_name").val();
            var last_name = $("#last_name").val();
            var fullname = $("#fullname").val();
            var email = $("#email").val();
            var mobile = $("#mobile").val();
            var gender = $("#gender").val();
            var dob = $("#dob").val();
            var role_id = $("#role_id").val();
            var scheme_id = $("#scheme_id").val();
            var shop_name = $("#shop_name").val();
            var office_address = $("#office_address").val();
            var lock_amount = $("#lock_amount").val();
            var company_id = $("#company_id").val();
            var status_id = $("#status_id").val();

            var address = $("#address").val();
            var city = $("#city").val();
            var state_id = $("#state_id").val();
            var district_id = $("#district_id").val();
            var pin_code = $("#pin_code").val();

            var seller = $("#seller").val();
            var day_book = $("#day_book").val();
            var monthly_statement = $("#monthly_statement").val();

            var user_id = $("#user_id").val();
            var parent_id = $("#parent_id").val();
            var gst_type = $("#gst_type").val();
            var pan_number = $("#pan_number").val();
            var gst_number = $("#gst_number").val();
            var user_gst_type = $("#user_gst_type").val();
            var active_services = $("#active_services").val();
            var login_restrictions = $("#login_restrictions").val();
            var latitude = $("#latitude").val();
            var longitude = $("#longitude").val();
            var cdm_card_number = $("#cdm_card_number").val();
            var embossed_card_number = $("#embossed_card_number").val();
            var cms_agent_id = $("#cms_agent_id").val();
            var cms_onboard_status = $("#cms_onboard_status").val();
            var aeps_onboard_status = $("#aeps_onboard_status").val();
            var iserveu_onboard_status = $("#iserveu_onboard_status").val();

            var is_ip_whiltelist = $("#is_ip_whiltelist").val();
            var server_ip = $("#server_ip").val();
            var callback_url = $("#callback_url").val();
            var credentials_id = $("#credentials_id").val();
            var type_rs_per =0;// $("#type_rs_per").val();
            var charges =0;// $("#charges").val();

            var dataString = 'name=' + name + '&last_name=' + last_name + '&middle_name=' + middle_name + '&fullname=' +
                fullname + '&email=' + email + '&mobile=' + mobile + '&gender=' + gender + '&dob=' + dob + '&role_id=' +
                role_id + '&scheme_id=' + scheme_id + '&shop_name=' + shop_name + '&office_address=' + office_address +
                '&lock_amount=' + lock_amount + '&company_id=' + company_id + '&address=' + address + '&city=' + city +
                '&state_id=' + state_id + '&district_id=' + district_id + '&pin_code=' + pin_code + '&user_id=' + user_id +
                '&parent_id=' + parent_id + '&seller=' + seller + '&gst_type=' + gst_type + '&pan_number=' + pan_number +
                '&gst_number=' + gst_number + '&user_gst_type=' + user_gst_type + '&day_book=' + day_book +
                '&monthly_statement=' + monthly_statement + '&status_id=' + status_id + '&active_services=' +
                active_services + '&login_restrictions=' + login_restrictions + '&latitude=' + latitude + '&longitude=' +
                longitude + '&cdm_card_number=' + cdm_card_number + '&cms_agent_id=' + cms_agent_id +
                '&cms_onboard_status=' + cms_onboard_status + '&aeps_onboard_status=' + aeps_onboard_status +
                '&iserveu_onboard_status=' + iserveu_onboard_status + '&embossed_card_number=' + embossed_card_number +
                '&_token=' + token + '&is_ip_whiltelist=' + is_ip_whiltelist + "&callback_url=" + callback_url +
                "&server_ip=" + server_ip + "&credentials_id="+credentials_id+"&type_rs_per="+type_rs_per+"&charges="+charges;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/update-members') }}",
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
                        $("#middle_name_errors").text(msg.errors.middle_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#fullname_errors").text(msg.errors.fullname);
                        $("#email_errors").text(msg.errors.email);
                        $("#mobile_errors").text(msg.errors.mobile);
                        $("#gender_errors").text(msg.errors.gender);
                        $("#dob_errors").text(msg.errors.dob);
                        $("#role_id_errors").text(msg.errors.role_id);
                        $("#scheme_id_errors").text(msg.errors.scheme_id);
                        $("#shop_name_errors").text(msg.errors.shop_name);
                        $("#office_address_errors").text(msg.errors.office_address);
                        $("#address_errors").text(msg.errors.address);
                        $("#city_errors").text(msg.errors.city);
                        $("#state_id_errors").text(msg.errors.state_id);
                        $("#district_id_errors").text(msg.errors.district_id);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#pan_number_errors").text(msg.errors.pan_number);
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
                        console.log(active_services);
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

                                {{-- <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Middle Name</label>
                                        <input type="text" id="middle_name" value="{{ $middle_name }}"
                                               class="form-control"
                                               placeholder="Middle Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="middle_name_errors"></li>
                                        </ul>

                                    </div>
                                </div> --}}

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
                                        <label for="name">Full Name (As per Aadhaar)</label>
                                        <input type="text" id="fullname" value="{{ $fullname }}"
                                            class="form-control" placeholder="Full Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="fullname_errors"></li>
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
                                        <label for="name">Date of Birth</label>
                                        <input class="form-control fc-datepicker" placeholder="dad"
                                            value="{{ $dob }}" type="text" id="dob" autocomplete="off">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="dob_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Gender</label>
                                        <select class="form-control select2" id="gender">
                                            <option value="">-- Select --</option>
                                            <option value="MALE"
                                                @if ($gender == 'MALE') selected="selected" @endif>Male
                                            </option>
                                            <option value="FEMALE"
                                                @if ($gender == 'FEMALE') selected="selected" @endif>Female
                                            </option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="gender_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Member Type</label>
                                        <select class="form-control select2" id="role_id">
                                            @foreach ($roledetails as $value)
                                                <option value="{{ $value->id }}"
                                                    @if ($role_id == $value->id) selected="selected" @endif>
                                                    {{ $value->role_title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="role_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                @if (Auth::User()->role_id == 1)
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">Package</label>
                                            <select class="form-control select2" id="scheme_id">
                                                <option value="">Select Package</option>
                                                @foreach ($schemes as $value)
                                                    <option value="{{ $value->id }}"
                                                        @if ($scheme_id == $value->id) selected="selected" @endif>
                                                        {{ $value->scheme_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="scheme_id_errors"></li>
                                            </ul>
                                        </div>
                                    </div>


                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">Parents</label>
                                            <select class="form-control select2" id="parent_id">
                                                @foreach ($parents as $value)
                                                    <option value="{{ $value->id }}"
                                                        @if ($parent_id == $value->id) selected="selected" @endif>
                                                        {{ $value->name }} {{ $value->last_name }}
                                                        - {{ $value->mobile }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">Day Book Alert</label>
                                            <select class="form-control select2" id="day_book" style="width: 100%;">
                                                <option value="1" @if ($day_book == 1) selected @endif>
                                                    Enabled</option>
                                                <option value="0" @if ($day_book == 0) selected @endif>
                                                    Disabled</option>
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="day_book_errors"></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">Daily Statement Alert</label>
                                            <select class="form-control select2" id="monthly_statement"
                                                style="width: 100%;">
                                                <option value="1" @if ($monthly_statement == 1) selected @endif>
                                                    Enabled
                                                </option>
                                                <option value="0" @if ($monthly_statement == 0) selected @endif>
                                                    Disabled
                                                </option>
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="monthly_statement_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                @endif


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Shop Name</label>
                                        <input type="text" id="shop_name" value="{{ $shop_name }}"
                                            class="form-control" placeholder="Shop Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="shop_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Lock Amount</label>
                                        <input type="text" id="lock_amount" value="{{ $lock_amount }}"
                                            class="form-control" placeholder="Lock Amount">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="lock_amount_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pan Number</label>
                                        <input type="text" id="pan_number" value="{{ $pan_number }}"
                                            class="form-control" placeholder="Pan Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pan_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">GST Number</label>
                                        <input type="text" id="gst_number" value="{{ $gst_number }}"
                                            class="form-control" placeholder="GST Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="gst_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Bankit CMS Onboard Status</label>
                                        <select class="form-control select2" id="cms_onboard_status"
                                            style="width: 100%;">
                                            <option value="0" @if ($cms_onboard_status == 0) selected @endif>
                                                Pending</option>
                                            <option value="1" @if ($cms_onboard_status == 1) selected @endif>
                                                Completed</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Bankit AEPS Onboard Status</label>
                                        <select class="form-control select2" id="aeps_onboard_status"
                                            style="width: 100%;">
                                            <option value="0" @if ($aeps_onboard_status == 0) selected @endif>
                                                Pending</option>
                                            <option value="1" @if ($aeps_onboard_status == 1) selected @endif>
                                                Completed</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">IserveU AEPS Onboard Status</label>
                                        <select class="form-control select2" id="iserveu_onboard_status"
                                            style="width: 100%;">
                                            <option value="0" @if ($iserveu_onboard_status == 0) selected @endif>
                                                Pending</option>
                                            <option value="1" @if ($iserveu_onboard_status == 1) selected @endif>
                                                Completed</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Retailer ID / Distributor ID</label>
                                        <input type="text" id="cms_agent_id" value="{{ $cms_agent_id }}"
                                            class="form-control" placeholder="Retailer ID">

                                    </div>
                                </div>

                                @if (Auth::User()->role_id <= 2 && Auth::User()->company->invoice == 1)
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">GST Invoce</label>
                                            <select class="form-control select2" id="gst_type" style="width: 100%;">
                                                <option value="1" @if ($gst_type == 1) selected @endif>
                                                    Yes</option>
                                                <option value="0" @if ($gst_type == 0) selected @endif>
                                                    No</option>
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="gst_number_errors"></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">GST Type</label>
                                            <select class="form-control select2" id="user_gst_type" style="width: 100%;">
                                                <option value="">Select GST Type</option>
                                                <option value="1" @if ($user_gst_type == 1) selected @endif>
                                                    I GST</option>
                                                <option value="2" @if ($user_gst_type == 2) selected @endif>
                                                    CGST</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                @if (Auth::User()->role_id == 1 && Auth::User()->company->pancard == 1)
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">Pan Username</label>
                                            <input type="text" id="pan_username" value="{{ $pan_username }}"
                                                class="form-control" placeholder="Pan Username" readonly>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">Pan Password</label>
                                            <input type="text" id="pan_password" value="{{ $pan_password }}"
                                                class="form-control" placeholder="Pan Password" readonly>
                                        </div>
                                    </div>
                                @endif

                                <?php
                                if ($server_ip) {
                                    $server_ip = json_decode($server_ip);
                                    $server_ip = implode(',', $server_ip);
                                }
                                ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="server_ip">Server ip</label>
                                        <input type="text" id="server_ip" class="form-control"
                                            value="{{ $server_ip }}" placeholder="Server ip">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="server_ip_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Is ip whiltelist?</label>
                                        <select class="form-control select2" id="is_ip_whiltelist">
                                            <option value="">-- Select --</option>
                                            <option value="1"
                                                @if ($is_ip_whiltelist == '1') selected="selected" @endif>ON
                                            </option>
                                            <option value="0"
                                                @if ($is_ip_whiltelist == '0') selected="selected" @endif>Off
                                            </option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="is_ip_whiltelist_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Webhook Url</label>
                                        <input type="text" id="callback_url" value="{{ $callback_url }}"
                                            class="form-control" placeholder="https://example.com/api/call-back/response">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="callback_url_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">

                                        <label for="name">EaseBuzz Credentials</label>
                                        <select class="form-control" id="credentials_id">
                                            @foreach($credentials as $c)
                                            <option value="{{$c->id}}" @if ($c->id == $credentials_id) selected @endif>
                                                {{$c->name}}
                                            </option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="credentials_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                {{-- <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Charges</label>
                                        <input type="text" id="charges" value="{{ $charges }}"
                                            class="form-control" placeholder="">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="charges_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <label for="name">Charges Type (% or Rs)</label>
                                            <select class="form-control" id="type_rs_per">
                                                <option value="1" @if ($type_rs_per == 1) selected @endif>
                                                    %
                                                </option>
                                                <option value="2" @if ($type_rs_per == 2) selected @endif>
                                                    Rs
                                                </option>
                                            </select>
                                        </div>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="type_rs_per_errors"></li>
                                        </ul>
                                    </div>
                                </div> --}}
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Office Address</label>
                                        <textarea class="form-control" id="office_address" placeholder="Office Address">{{ $office_address }}</textarea>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="office_address_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                @if (Auth::User()->role_id == 1 && Auth::User()->company->login_restrictions == 1)
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">User Login Restrictions</label>
                                            <select class="form-control" id="login_restrictions">
                                                <option value="1" @if ($login_restrictions == 1) selected @endif>
                                                    Enable
                                                </option>
                                                <option value="0" @if ($login_restrictions == 0) selected @endif>
                                                    Disable
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">User Latitude</label>
                                            <input type="text" id="latitude" value="{{ $latitude }}"
                                                class="form-control" placeholder="User Latitude">
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">User Longitude</label>
                                            <input type="text" id="longitude" value="{{ $longitude }}"
                                                class="form-control" placeholder="User Longitude">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="alert alert-danger" role="alert">
                                            If you will enable login restrictions, users will be able to login only
                                            within 1 KM.
                                        </div>
                                    </div>
                                @endif


                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>
        {{-- perssinal details clase --}}


        {{-- Permanent details --}}

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
                                        <input class="form-control" id="address" placeholder="Address"
                                            value="{{ $address }}">
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
                                            @foreach ($state as $value)
                                                <option value="{{ $value->id }}"
                                                    @if ($state_id == $value->id) selected="selected" @endif>
                                                    {{ $value->name }}
                                                </option>
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
                                            @foreach ($cities as $c)
                                                <option @if ($city == $c->city) selected @endif
                                                    value="{{ $c->city }}">{{ $c->city }}</option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="text" id="city" value="{{ $city }}" class="form-control"
                                               placeholder="City"> --}}
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="city_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pincode</label>
                                        <input type="text" id="pin_code" value="{{ $pin_code }}"
                                            class="form-control" placeholder="Pincode">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pin_code_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                @if (Auth::User()->role_id == 1 && $role_id == '8')
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">Latitude</label>
                                            <input type="text" id="latitude" value="{{ $latitude }}"
                                                class="form-control onlyNumberDot" maxlength="9"
                                                placeholder="28.535517">
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="latitude_errors"></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">Longitude</label>
                                            <input type="text" id="longitude" value="{{ $longitude }}"
                                                class="form-control onlyNumberDot" maxlength="9"
                                                placeholder="77.391029">
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="longitude_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                {{-- <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">District</label>
                                        <select class="form-control select2" id="district_id">
                                            <option value="">Select District</option>
                                            @foreach ($permanentdistrict as $value)
                                                <option value="{{ $value->id }}" @if ($district_id == $value->id)
                                                selected="selected" @endif>{{ $value->district_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="district_id_errors"></li>
                                        </ul>
                                    </div>
                                </div> --}}


                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>
        {{-- Permanent details close --}}


        {{-- service detail --}}
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Service</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Member Status</label>
                                        <select class="form-control" id="status_id">
                                            <option value="1"
                                                @if ($status_id == '1') selected="selected" @endif>Active
                                            </option>
                                            <option value="0"
                                                @if ($status_id == '0') selected="selected" @endif>De
                                                Active
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                @if (Auth::User()->role_id == 1)
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="cdm_card_number">CDM Card Number</label>
                                            <input type="text" id="cdm_card_number" value="{{ $cdm_card_number }}"
                                                class="form-control" placeholder="CDM Card Number">
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="cdm_card_number_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="embossed_card_number">Embossed Card Number</label>
                                            <input type="text" id="embossed_card_number"
                                                value="{{ $embossed_card_number }}" class="form-control"
                                                name="embossed_card_number" placeholder="Embossed Card Number">
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="embossed_card_number_errors"></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Active Service</label>
                                            <select class="form-control select2" id="active_services" style="width: 100%"
                                                multiple>
                                                @foreach ($services as $value)
                                                    <option value="{{ $value->id }}">{{ $value->service_name }}
                                                    </option>
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
                    </div>
                    <div class="card-footer">
                        <input type="hidden" value="{{ $user_id }}" id="user_id">
                        <button type="submit" class="btn btn-success waves-effect waves-light" onclick="create_users()">
                            Save
                            Details
                        </button>
                        @if (Auth::User()->role_id == 1 && Auth::User()->company->pancard == 1)
                            <button type="submit" class="btn btn-info waves-effect waves-light"
                                onclick="create_pancard_id()">
                                Create Pancard Id
                            </button>
                        @endif
                        <a href="{{ url()->previous() }}" class="btn btn-danger waves-effect waves-light"><i
                                class="fas fa-backward"></i> Back</a>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>
        {{-- service detail close --}}


    </div>
    </div>
    </div>


@endsection
