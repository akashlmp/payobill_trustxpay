@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
     

        function getMnpDetail() {
            $(".loader").show();
            $(".parsley-required").html('');
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
           var dataString = 'mobile_number=' + mobile_number + '&mode=WEB&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/mnp/v1/store-mnp')}}",
                data: dataString,
                success: function (res) {
                    $(".loader").hide();
                    if (res.status == 'error') {
                        $("#mobile_number_errors").text(res.errors.mobile_number);
                      
                    } else if (res.status == 'success') {
                        if(res.data.Error)
                        {
                            swal("Failed", res.data.Error, "error");
                        }
                        else{
                            $("#view_mnp_detail_model").modal('show');
                            $("#td_mobile_number").text(res.data.MobileNo);
                            $("#td_system_reference_no").text(res.data.SystemReferenceNo);
                            $("#td_corp_ref_no").text(res.data.CorpRefNo);
                            $("#td_current_operator").text(res.data.CurrentOperator);
                            $("#td_current_location").text(res.data.CurrentLocation);
                            $("#td_previous_operator").text(res.data.PreviousOperator);
                            $("#td_previous_location").text(res.data.PreviousLocation);
                            $("#td_ported").text(res.data.Ported);
                            $("#td_charged").text(res.data.Charged);
                            $("#td_error").text(res.data.Error);
                         
                        }
                    }
                }
            });
        }

        function getMnpBalance() {
            $(".loader").show();
            $(".parsley-required").html('');
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var dataString = '_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/mnp/v1/get-mnp-balance')}}",
                data: dataString,
                success: function (res) {
                    console.log(res);
                    $(".loader").hide();
                    if (res.status == 'success') {
                      
                        if (res.data.Status == 2) {
                            swal("Failed", res.data.Balance, "error");
                        }
                        else if (res.data.Status == 0) {
                        swal("Success", res.data.Balance, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 1000);

                        }

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
                    <div class="mb-2">
                        <label>Mobile Number</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">+91</span>
                            </div>
                            <input type="hidden" id="type" value="1">
                            <input type="text" class="form-control" placeholder="Mobile Number" data-id="1"
                                       id="mobile_number" aria-label="Username" aria-describedby="basic-addon1">
                        </div>
                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="mobile_number_errors"></li>
                        </ul>
                    </div>
                      
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" onclick="getMnpDetail()">MNP Detail</button>
                    <!-- <button class="btn ripple btn-primary" type="button" onclick="getMnpBalance()">MNP Balance</button> -->
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
    <div class="modal  show" id="view_mnp_detail_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">MNP Detail</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">

                        
                            <div class="card-body">
                                <div class="table-responsive mb-0">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped ">
                                        
                                        <tbody>
                                        
                                            <tr>
                                                <td>MobileNo</td>
                                                <td id="td_mobile_number"></td>
                                            </tr>
                                            <tr>
                                                <td>SystemReferenceNo</td>
                                                <td id="td_system_reference_no"></td>
                                            </tr>
                                            <tr>
                                                <td>CorpRefNo</td>
                                                <td id="td_corp_ref_no"></td>
                                            </tr>
                                            <tr>
                                                <td>CurrentOperator</td>
                                                <td id="td_current_operator"></td>
                                            </tr>
                                            
                                            <tr>
                                                <td>CurrentLocation</td>
                                                <td id="td_current_location"></td>
                                            </tr>
                                            <tr>
                                                <td>PreviousOperator</td>
                                                <td id="td_previous_operator"></td>
                                            </tr>
                                            <tr>
                                                <td>PreviousLocation</td>
                                                <td id="td_previous_location"></td>
                                            </tr>
                                            
                                            <tr>
                                                <td>Ported</td>
                                                <td id="td_ported"></td>
                                            </tr>
                                            <tr>
                                                <td>Charged</td>
                                                <td id="td_charged"></td>
                                            </tr>
                                            <tr>
                                                <td>Error</td>
                                                <td id="td_error"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                       
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                 
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
