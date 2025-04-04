@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function() {

        });

        function update_user_settings() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var api_key = $("#api_key").val();
            var secret_key = $("#secret_key").val();
            var user_id = $("#user_id").val();
            var is_ip_whiltelist = $("#is_ip_whiltelist").val();
            var server_ip = $("#server_ip").val();
            var callback_url = $("#callback_url").val();
            var credentials_id = $("#credentials_id").val();


            var dataString = 'api_key=' + api_key + '&secret_key=' + secret_key + '&_token=' + token +
                '&is_ip_whiltelist=' + is_ip_whiltelist + "&callback_url=" + callback_url +
                "&server_ip=" + server_ip + "&credentials_id=" + credentials_id + "&user_id=" + user_id;
            $.ajax({
                type: "POST",
                url: "{{ url('admin/member/update/setting/') }}",
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
                           window.location.href = "{{ url('/admin/member-list/retailer') }}"
                        });
                    } else if (msg.status == 'validation_error') {
                        $("#credentials_id_errors").text(msg.credentials_id.name);
                        $("#api_key_errors").text(msg.errors.api_key);
                        $("#secret_key_errors").text(msg.errors.secret_key);
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
                                        <label for="name">Api Key</label>
                                        <input type="text" id="api_key" value="{{ $api_key }}" disabled
                                            class="form-control" placeholder="Api Key">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="api_key_errors"></li>
                                        </ul>

                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Secret Key</label>
                                        <input type="text" id="secret_key" value="{{ $secret_key }}" disabled
                                            class="form-control" placeholder="Secret Key">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="Secret_key_errors"></li>
                                        </ul>
                                    </div>
                                </div>

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
                                            @foreach ($credentials as $c)
                                                <option value="{{ $c->id }}"
                                                    @if ($c->id == $credentials_id) selected @endif>
                                                    {{ $c->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="credentials_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <input type="hidden" value="{{ $user_id }}" id="user_id">
                        <button type="submit" class="btn btn-success waves-effect waves-light"
                            onclick="update_user_settings()">
                            Save
                            Details
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-danger waves-effect waves-light"><i
                                class="fas fa-backward"></i> Back</a>
                    </div>
                </div>
            </div>
        </div>
        <!--/div-->
    </div>


    </div>
    </div>
    </div>
@endsection
