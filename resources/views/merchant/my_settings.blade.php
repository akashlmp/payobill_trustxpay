@extends('merchant.layouts.main')
@section('title','Merchant Setting')
@section('content')

    <script type="text/javascript">



        function save_settings() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var api_key = $("#api_key").val();
            var secrete_key = $("#secrete_key").val();
            var callback_url = $("#callback_url").val();
            var dataString = 'api_key=' + api_key + '&secrete_key=' + secrete_key + '&callback_url=' + callback_url +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('merchant/save-settings')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 2000);
                    } else if(msg.status == 'validation_error'){
                        $("#api_key_errors").text(msg.errors.api_key);
                        $("#secret_key_errors").text(msg.errors.secrete_key);
                        $("#callback_url_errors").text(msg.errors.secrete_key);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function regenrateKeys() {
            swal({
                    title: "Are you sure?",
                    text: 'you want to regenerate keys? ',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, Regenerate",
                    cancelButtonText: "No, cancel",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $(".loader").show();
                        var token = $("input[name=_token]").val();
                        var dataString = '_token=' + token;
                        $.ajax({
                            type: "POST",
                            url: "{{url('merchant/regenerate-keys')}}",
                            data: dataString,
                            success: function (msg) {
                                $(".loader").hide();
                                if (msg.status == 'success') {
                                    swalSuccessReload(msg.message);
                                    // swal("Regenerate!", msg.message, "success");
                                    // setTimeout(function () { location.reload(1); }, 3000);
                                }else{
                                    swal("Faild", msg.message, "error");
                                }
                            }
                        });
                    }

                }
            );
        }

    </script>


    <div class="main-content-body">
        <div class="row row-sm">
            <!-- Col -->
            <div class="col-lg-5">

                {{-- //--}}
                <div class="card custom-card">
                    <div class="card-body text-center">

                        <div class="user-lock text-center">
                            <img alt="avatar" class="rounded-circle" src="{{ url('assets/img/profile-pic.jpg') }}">

                        </div>
                        <h5 class="mb-1 mt-3 card-title">{{ Auth::guard('merchant')->user()->first_name }} {{ Auth::guard('merchant')->user()->last_name }}</h5>
                        <p class="mb-2 mt-1 tx-inverse">Merchant</p>


                        <div class="mt-2 user-info btn-list">
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-mail mr-2"></i><span> {{ Auth::guard('merchant')->user()->email }}</span></a>
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-phone mr-2"></i><span> {{ Auth::guard('merchant')->user()->mobile_number }}</span></a>
                            <a class="btn btn-outline-light  btn-block" href="#"><i class="far fa-clock"></i> <span> {{ Auth::guard('merchant')->user()->created_at }}</span></a>


                        </div>
                    </div>
                </div>



            </div>
            <!-- /Col -->

            <!-- Col -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">{{ $page_title }}</div>
                        <hr>
                        <form class="form-horizontal">


                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Api Key</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" placeholder="Api Key" value="{{ Auth::guard('merchant')->user()->api_key }}" id="api_key" disabled>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="api_key_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Secret Key</label>
                                    </div>
                                    <div class="col-md-9">
                                    <input type="text" class="form-control" placeholder="Secret Key" value="{{ Auth::guard('merchant')->user()->secrete_key }}" id="secrete_key" disabled>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="secret_key_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Webhook Url</label>
                                    </div>
                                    <div class="col-md-9">
                                    <input type="text" class="form-control" placeholder="webhook Url" value="{{ Auth::guard('merchant')->user()->callback_url }}" id="callback_url" disabled>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="callback_url_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                <div class="col-md-3"></div>
                                    <div class="col-md-9">
                                        <button type="button" class="btn btn-danger waves-effect waves-light" onclick="regenrateKeys()">Regenerate Key</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light d-none" onclick="save_settings()">Save</button>
                    </div>
                </div>
            </div>
            <!-- /Col -->
        </div>


    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->

@endsection
