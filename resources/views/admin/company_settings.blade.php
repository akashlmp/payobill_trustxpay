@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#same_amount").select2();
            $("#server_down").select2();
            $("#login_type").select2();
            $("#table_format").select2();
            $("#transaction_pin").select2();
            $("#state_id").select2();
            $("#active_services").select2();
            $("#default_services").select2();
            viewActiveService();
            viewDefaultService();
        });

        function update_company() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var company_id = $("#company_id").val();
            var company_name = $("#company_name").val();
            var company_email = $("#company_email").val();
            var company_website = $("#company_website").val();
            var company_address = $("#company_address").val();
            var company_address_two = $("#company_address_two").val();
            var support_number = $("#support_number").val();
            var whatsapp_number = $("#whatsapp_number").val();
            var news = $("#news").val();
            var update_one = $("#update_one").val();
            var update_two = $("#update_two").val();
            var update_three = $("#update_three").val();
            var sender_id = $("#sender_id").val();
            var same_amount = $("#same_amount").val();
            var server_down = $("#server_down").val();
            var server_message = $("#server_message").val();
            var state_id = $("#state_id").val();
            var pin_code = $("#pin_code").val();
            var pan_number = $("#pan_number").val();
            var gst_number = $("#gst_number").val();
            var login_type = $("#login_type").val();
            var color_start = $("#color_start").val();
            var color_end = $("#color_end").val();
            var table_format = $("#table_format").val();
            var transaction_pin = $("#transaction_pin").val();
            var facebook_link = $("#facebook_link").val();
            var instagram_link = $("#instagram_link").val();
            var twitter_link = $("#twitter_link").val();
            var youtube_link = $("#youtube_link").val();
            var active_services = $("#active_services").val();
            var default_services = $("#default_services").val();
            var dmt_provider = $("#dmt_provider").val();
            var aeps_provider = $("#aeps_provider").val();
            var cms_provider = $("#cms_provider").val();
            var payout_provider = $("#payout_provider").val();
            var dataString = 'company_id=' + company_id + '&company_name=' + company_name + '&company_email=' + company_email + '&company_website=' + company_website + '&company_address=' + company_address + '&company_address_two=' + company_address_two + '&support_number=' + support_number + '&whatsapp_number=' + whatsapp_number + '&news=' + news + '&update_one=' + update_one + '&update_two=' + update_two + '&update_three=' + update_three + '&sender_id=' + sender_id + '&same_amount=' + same_amount + '&server_down=' + server_down + '&server_message=' + server_message + '&state_id=' + state_id + '&pin_code=' + pin_code + '&pan_number=' + pan_number + '&gst_number=' + gst_number + '&login_type=' + login_type + '&color_start=' + color_start + '&color_end=' + color_end + '&table_format=' + table_format + '&transaction_pin=' + transaction_pin + '&facebook_link=' + encodeURIComponent(facebook_link) + '&instagram_link=' + encodeURIComponent(instagram_link) + '&twitter_link=' + encodeURIComponent(twitter_link) + '&youtube_link=' + encodeURIComponent(youtube_link) + '&active_services=' + active_services + '&default_services='+ default_services +'&dmt_provider=' + dmt_provider + '&aeps_provider=' + aeps_provider + '&cms_provider=' + cms_provider + '&_token=' + token +"&payout_provider="+payout_provider;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-company-seeting')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swalSuccessReload(msg.message);
                    } else if (msg.status == 'validation_error') {
                        $("#company_id_errors").text(msg.errors.company_id);
                        $("#company_name_errors").text(msg.errors.company_name);
                        $("#company_email_errors").text(msg.errors.company_email);
                        $("#company_address_errors").text(msg.errors.company_address);
                        $("#support_number_errors").text(msg.errors.support_number);
                        $("#whatsapp_number_errors").text(msg.errors.whatsapp_number);
                        $("#news_errors").text(msg.errors.news);
                        $("#sender_id_errors").text(msg.errors.sender_id);
                        $("#same_amount_errors").text(msg.errors.same_amount);
                        $("#login_type_errors").text(msg.errors.login_type);
                        $("#state_name_errors").text(msg.errors.state_name);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#pan_number_errors").text(msg.errors.pan_number);
                        $("#gst_number_errors").text(msg.errors.gst_number);
                        $("#facebook_link_errors").text(msg.errors.facebook_link);
                        $("#instagram_link_errors").text(msg.errors.instagram_link);
                        $("#twitter_link_errors").text(msg.errors.twitter_link);
                        $("#youtube_link_errors").text(msg.errors.youtube_link);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function viewActiveService() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = '_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-company-active-services')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var active_services = msg.active_services;
                        $.each(active_services.split(","), function (i, e) {
                            $("#active_services option[value='" + e + "']").prop("selected", true);
                        });
                        $('#active_services').trigger('change');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        function viewDefaultService() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = '_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-company-default-services')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var active_services = msg.default_services;
                        $.each(active_services.split(","), function (i, e) {
                            $("#default_services option[value='" + e + "']").prop("selected", true);
                        });
                        $('#default_services').trigger('change');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>



    <div class="main-content-body">
        {{--perssinal details--}}

        {{--perssinal details clase--}}



        {{--Permanent details--}}



        {{--service detail--}}
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
                                        <label for="name">Company Name</label>
                                        <input type="text" class="form-control" id="company_name"
                                               placeholder="Company Name" value="{{ $update_company_name }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="company_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Company Email</label>
                                        <input type="text" class="form-control" id="company_email"
                                               placeholder="Company Email" value="{{ $update_company_email }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="company_email_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Company Website</label>
                                        <input type="text" class="form-control" id="company_website"
                                               placeholder="Company Website" value="{{ $update_company_website }}"
                                               readonly>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Support Number</label>
                                        <input type="text" class="form-control" id="support_number"
                                               placeholder="Support Number" value="{{ $update_support_number }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="support_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Whatsapp Number</label>
                                        <input type="text" class="form-control" id="whatsapp_number"
                                               placeholder="Whatsapp Number" value="{{ $update_whatsapp_number }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="whatsapp_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">News</label>
                                        <input type="text" class="form-control" id="news" placeholder="Update New"
                                               value="{{ $update_news }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="news_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Sms Sender</label>
                                        <input type="text" class="form-control" id="sender_id"
                                               placeholder="6 Digit Sender Id" value="{{ $update_sender_id }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="sender_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Same Amount Recharge</label>
                                        <select class="form-control select2" id="same_amount">
                                            <option value="0" @if($update_same_amount == 0) selected @endif>0 Minute
                                            </option>
                                            <option value="1" @if($update_same_amount == 1) selected @endif>1 Minute
                                            </option>
                                            <option value="2" @if($update_same_amount == 2) selected @endif>2 Minute
                                            </option>
                                            <option value="3" @if($update_same_amount == 3) selected @endif>3 Minute
                                            </option>
                                            <option value="4" @if($update_same_amount == 4) selected @endif>4 Minute
                                            </option>
                                            <option value="5" @if($update_same_amount == 5) selected @endif>5 Minute
                                            </option>
                                            <option value="10" @if($update_same_amount == 10) selected @endif>10
                                                Minute
                                            </option>
                                            <option value="15" @if($update_same_amount == 15) selected @endif>15
                                                Minute
                                            </option>
                                            <option value="20" @if($update_same_amount == 20) selected @endif>20
                                                Minute
                                            </option>
                                            <option value="30" @if($update_same_amount == 30) selected @endif>30
                                                Minute
                                            </option>
                                            <option value="40" @if($update_same_amount == 40) selected @endif>40
                                                Minute
                                            </option>
                                            <option value="50" @if($update_same_amount == 50) selected @endif>50
                                                Minute
                                            </option>
                                            <option value="60" @if($update_same_amount == 60) selected @endif>60
                                                Minute
                                            </option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="same_amount_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Server</label>
                                        <select class="form-control select2" id="server_down">
                                            <option value="0" @if($server_down == 0) selected @endif>Maintenance
                                            </option>
                                            <option value="1" @if($server_down == 1) selected @endif>Running</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="server_down_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Maintenance Message</label>
                                        <input type="text" class="form-control" id="server_message"
                                               placeholder="Server Message" value="{{ $server_message }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="server_message_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Select State</label>
                                        <select class="form-control select2" id="state_id">
                                            @foreach($states as $value)
                                                <option value="{{ $value->id }}"
                                                        @if($value->id == $state_id) selected @endif>{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="state_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pin Code</label>
                                        <input type="text" class="form-control" id="pin_code" placeholder="Pin Code"
                                               value="{{ $pin_code }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pin_code_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pan Number</label>
                                        <input type="text" class="form-control" id="pan_number" placeholder="Pan Number"
                                               value="{{ $pan_number }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pan_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">GST Number</label>
                                        <input type="text" class="form-control" id="gst_number" placeholder="GST Number"
                                               value="{{ $gst_number }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="gst_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Login Type</label>
                                        <select class="form-control select2" id="login_type">
                                            <option value="0" @if($login_type == 0) selected @endif>Without OTP</option>
                                            <option value="1" @if($login_type == 1) selected @endif>With OTP</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="login_type_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Company Address</label>
                                        <textarea class="form-control" id="company_address"
                                                  placeholder="Company Address">{{ $update_company_address }}</textarea>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="company_address_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Company Address Two</label>
                                        <textarea class="form-control" id="company_address_two"
                                                  placeholder="Company Address Two">{{ $update_company_address_two }}</textarea>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Header Color Start</label>
                                        <input type="color" class="form-control" id="color_start"
                                               value="{{ $color_start }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="color_start_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Header Color End</label>
                                        <input type="color" class="form-control" id="color_end"
                                               value="{{ $color_end }}">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="color_end_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Facebook Link</label>
                                        <input type="text" class="form-control" id="facebook_link"
                                               value="{{ $facebook_link }}" placeholder="Facebook Link">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="facebook_link_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Instagram Link</label>
                                        <input type="text" class="form-control" id="instagram_link"
                                               value="{{ $instagram_link }}" placeholder="Instagram Link">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="instagram_link_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Twitter Link</label>
                                        <input type="text" class="form-control" id="twitter_link"
                                               value="{{ $twitter_link }}" placeholder="Twitter Link">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="twitter_link_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Youtube Link</label>
                                        <input type="text" class="form-control" id="youtube_link"
                                               value="{{ $youtube_link }}" placeholder="Youtube Link">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="youtube_link_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Table Fromat</label>
                                        <select class="form-control select2" id="table_format">
                                            <option value="1" @if($table_format == 1) selected @endif>Table 1</option>
                                            <option value="2" @if($table_format == 2) selected @endif>Table 2</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="table_format_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Transaction Pin</label>
                                        <select class="form-control select2" id="transaction_pin">
                                            <option value="0" @if($transaction_pin == 0) selected @endif>Disable
                                            </option>
                                            <option value="1" @if($transaction_pin == 1) selected @endif>Enable</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="transaction_pin_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Active Service</label>
                                        <select class="form-control select2" id="active_services" style="width: 100%"
                                                multiple>
                                            @foreach($services as $value)
                                                <option value="{{ $value->id }}">{{ $value->service_name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="active_services_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Retailer Default Service</label>
                                        <select class="form-control select2" id="default_services" style="width: 100%"
                                                multiple>
                                            @foreach($services as $value)
                                                <option value="{{ $value->id }}">{{ $value->service_name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="default_services_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">DMT Provider</label>
                                        <select class="form-control select2" id="dmt_provider">
                                            <option value="1" @if($dmt_provider == 1)selected @endif>Pays-print</option>
                                            <option value="2" @if($dmt_provider == 2)selected @endif>Bankit</option>
                                            <option value="3" @if($dmt_provider == 3)selected @endif>iServeU</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="dmt_provider_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">AEPS Provider</label>
                                        <select class="form-control select2" id="aeps_provider">
                                            <option value="1" @if($aeps_provider == 1)selected @endif>Pays-print</option>
                                            <option value="2" @if($aeps_provider == 2)selected @endif>Bankit</option>
                                            <option value="3" @if($aeps_provider == 3)selected @endif>iServeU</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="aeps_provider_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">CMS Provider</label>
                                        <select class="form-control select2" id="cms_provider">
                                            <option value="1" @if($cms_provider == 1)selected @endif>Pays-print</option>
                                            <option value="2" @if($cms_provider == 2)selected @endif>Bankit</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="cms_provider_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Payout Provider</label>
                                        <select class="form-control select2" id="payout_provider">
                                            <option value="1" @if($payout_provider == 1) selected @endif>Pays-print</option>
                                            {{-- <option value="2" @if($cms_provider == 2)selected @endif>Bankit</option> --}}
                                            <option value="3" @if($payout_provider == 3) selected @endif>iServeU</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="payout_provider_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <input type="hidden" value="{{ $update_company_id }}" id="company_id">
                        <button type="submit" class="btn btn-danger waves-effect waves-light"
                                onclick="update_company()">Update Details
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
