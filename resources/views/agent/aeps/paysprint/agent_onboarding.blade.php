@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        function submitNow (){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var merchant_code = $("#merchant_code").val();
            var mobile_number = $("#mobile_number").val();
            var email = $("#email").val();
            var firm = $("#firm").val();
            var dataString = 'merchant_code=' + merchant_code + '&mobile_number=' + mobile_number + '&email=' + email + '&firm=' + firm + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/aeps/v2/agent-onboarding')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        window.open(msg.redirecturl, '_blank').focus();
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'error'){
                        $("#merchant_code_errors").text(msg.errors.merchant_code);
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#email_errors").text(msg.errors.email);
                        $("#firm_errors").text(msg.errors.firm);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>
    <div class="main-content-body">
        <div class="row">

            @include('agent.aeps.paysprint.leftSideMenu')

            <div class="col-lg-4 col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>


                        <div class="mb-4">
                            <label>Merchant Code</label>
                            <input type="text" class="form-control"  id="merchant_code" value="{{ $short_code }}" readonly>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="merchant_code_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Mobile Number</label>
                            <input type="text" class="form-control" id="mobile_number" value="{{ Auth::User()->mobile }}" readonly>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="mobile_number_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Email Address</label>
                            <input type="email" class="form-control"  id="email" value="{{Auth::User()->email}}" readonly>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="email_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Firm Name</label>
                            <input type="email" class="form-control" placeholder="Firm Name" id="firm" value="{{Auth::User()->member->shop_name}}">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="firm_errors"></li>
                            </ul>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="submitNow()">Submit</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>




        </div>

    </div>
    </div>
    </div>


@endsection
