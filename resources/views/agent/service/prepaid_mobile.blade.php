@extends('agent.layout.header')
@section('content')
<script type="text/javascript">
    function view_recharges() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var mobile_number = $("#mobile_number").val();
        var provider_id = $("#provider_id").val();
        var amount = $("#amount").val();
        var dataString = 'mobile_number=' + mobile_number + '&provider_id=' + provider_id + '&amount=' + amount + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/view-recharge-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    generate_millisecond();
                    $("#confirm_provider_name").val(msg.confirm_provider_name);
                    $("#confirm_mobile_number").val(msg.confirm_mobile_number);
                    $("#confirm_amount").val(msg.confirm_amount);
                    $("#confirm_provider_id").val(msg.confirm_provider_id);
                    $("#confirm_recharge_model").modal('show');
                } else if (msg.status == 'validation_error') {
                    $("#mobile_number_errors").text(msg.errors.mobile_number);
                    $("#provider_id_errors").text(msg.errors.provider_id);
                    $("#amount_errors").text(msg.errors.amount);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }
</script>




<div class="main-content-body">

    <div class="row">
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div>
                        <h6 class="card-title mb-1">{{ $page_title }}</h6>
                        <hr>
                    </div>
                    <div class="mb-4">
                        <label>Mobile Number</label>
                        <input type="text" class="form-control" placeholder="Mobile Number" id="mobile_number">
                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="mobile_number_errors"></li>
                        </ul>
                    </div>


                    <div class="mb-4">
                        <label>Provider</label>
                        <select class="form-control select2" id="provider_id" style="width: 100%;">
                            @foreach($providers as $value)
                            <option value="{{ $value->id }}">{{ $value->provider_name }}</option>
                            @endforeach
                        </select>
                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="provider_id_errors"></li>
                        </ul>
                    </div>

                    @if(Auth::User()->company->view_plan == 1)
                    <div class="mb-4">
                        <label>State</label>
                        <select class="form-control select2" id="state_id" style="width: 100%;">
                            @foreach($state as $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-4">
                        <label>Amount</label>
                        <input type="text" class="form-control" placeholder="Amount" id="amount" onkeyup="amountToWords();">
                        @if(Auth::User()->company->view_plan == 1)
                        <span style="position: relative;top: -30px;right: 10px;float: right;"><a
                                    style="cursor: pointer;" onclick="getprepaidPlan(3)" id="vbil">View Plan</a></span>
                        @endif
                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="amount_errors"></li>
                        </ul>
                        <strong style="color: red;" id="amountToWordsText"></strong>
                    </div>


                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" onclick="view_recharges()">Pay Now</button>
                    @if(Auth::User()->company->view_plan == 1)
                        <button class="btn ripple btn-danger" type="button" onclick="getRofferPlan()">R Offer</button>
                    @endif
                </div>
            </div>
        </div>


        @include('agent.service.left_banner')

    </div>

</div>
</div>
</div>

@include('common.recharge_confirmation_model')
@include('common.prepaid_view_plan_model')

@endsection