@extends('agent.layout.header')
@section('content')

    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Service List</h6>
                            <hr>
                        </div>
                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            @foreach(config('bbps.services') as $key => $value)
                                @if($loop->first)
                                    <li class="nav-item mb-3">
                                        <a class="nav-link active" data-service="{{$key}}" id="pills-{{$key}}-tab"
                                           data-toggle="pill"
                                           href="javascript:void(0);" onclick="getServiceDetail('{{$key}}')"
                                           role="tab" aria-controls="pills-{{$key}}" aria-selected="true">{{$value}}</a>
                                    </li>
                                @else
                                    <li class="nav-item mb-3">
                                        <a class="nav-link" data-service="{{$key}}" id="pills-{{$key}}-tab"
                                           data-toggle="pill"
                                           href="javascript:void(0);" onclick="getServiceDetail('{{$key}}')"
                                           role="tab" aria-controls="pills-{{$key}}"
                                           aria-selected="false">{{$value}}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                        </div>
                    </div>
                    <div class="card-body" id="displayInput">
                        <div class="mb-4">
                            <label>Mobile Number</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">+91</span>
                                </div>
                                <input type="text" class="form-control" placeholder="Mobile Number" id="mobile_number">
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input rechargeType" name="type"
                                           value="prepaid" checked>Prepaid
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input rechargeType" name="type"
                                           value="postpaid">Postpaid
                                </label>
                            </div>
                        </div>
                        <div class="mb-2" id="prepaidDiv">
                            <div class="form-group">
                                <label for="service_provider_prepaid">Service Provider</label>
                                <select class="form-control single_select2" id="service_provider_prepaid">
                                    <option value="" disabled selected hidden>-- Select Provider --</option>
                                    @foreach($prepaid_provider as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2" id="postpaidDiv" style="display: none">
                            <div class="form-group">
                                <label for="service_provider_postpaid">Service Provider</label>
                                <select class="form-control single_select2" id="service_provider_postpaid">
                                    <option value="" disabled selected hidden>-- Select Provider --</option>
                                    @foreach($postpaid_provider as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-group">
                                <label for="circle">Circle</label>
                                <select class="form-control single_select2" id="circle">
                                    <option value="" disabled selected hidden>-- Select Circle --</option>
                                    @foreach($circles as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>Amount</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">&#8377;</span>
                                </div>
                                <input type="text" class="form-control" placeholder="Amount"
                                       id="amount" aria-label="Amount" aria-describedby="basic-addon1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="submitRecharge()">Submit</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
@section('customScript')
    <script type="text/javascript">

        $(document).ready(function () {
            $(".single_select2").select2({
                width: "100%",
            });
        });

        $(document).on('click', '.rechargeType', function () {
            let type = $(this).val();
            if (type === 'prepaid') {
                $('#prepaidDiv').show();
                $('#postpaidDiv').hide();
            } else {
                $('#postpaidDiv').show();
                $('#prepaidDiv').hide();
            }
        });

    </script>
    <script type="text/javascript">


        $(document).ready(function () {

            $('#payoutLatitude').val($("#inputLatitude").val());
            $('#payoutLongitude').val($("#inputLongitude").val());


            $('#addAccountForm').validate({
                errorPlacement: function (error, element) {
                    $(element).parents('.form-error').append(error);
                },
            });

            $('#payoutRequestForm').validate({
                errorPlacement: function (error, element) {
                    $(element).parents('.form-error').append(error);
                },
            });

            $('#uploadDocumentForm').validate({
                errorPlacement: function (error, element) {
                    $(element).parents('.form-error').append(error);
                },
            });

            $("#bankdetail_id").select2();
            $("#payment_mode").select2();


            $(".document_type_select2").select2({
                width: "100%",
                dropdownParent: $("#uploadDocumentModal")
            });

        });

        function getServiceDetail(service) {
            let active_Service = $('.nav-link.active').data('service');
            if (active_Service !== service) {
                $.get("{{url('agent/bbps/v1/get-service-detail')}}", {service: service}, function (res) {

                })
            }
        }

        function updateBankMaster() {
            $.get("{{url('agent/update-bank-master')}}", function (res) {
                console.log(res)
            })
        }

        function updatePayoutAccount() {
            $.get("{{url('agent/update-payout-account')}}", function (res) {
                console.log(res)
            })
        }

        $(document).on('click', '#addAccount', function () {
            $('label.error').hide();
            $('#addAccountForm').trigger("reset");
            $('.single_select2').val("").trigger("change");
            $('#addAccountModal').modal('show');
        });

        $(document).on('click', '.uploadDocument', function () {
            let bene_id = $(this).attr('data-id');
            $('label.error').hide();
            $('#uploadDocumentForm').trigger("reset");
            $('.single_select2').val("").trigger("change");
            $('#uploadDocumentModal').modal('show');
            $('#beneId').val(bene_id);
        });

        $(document).on('change', '.document_type_select2', function () {
            let type = $(this).val();
            if (type === 'PAN') {
                $('.aadharSection').hide();
                $('.panSection').show();
            } else {
                $('.panSection').hide();
                $('.aadharSection').show();
            }

        });

        function addAccount() {
            if ($('#addAccountForm').valid()) {
                $('#addAccountModal').modal('hide');
                $(".loader").show();
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/add-account')}}",
                    data: $('#addAccountForm').serialize(),
                    success: function (res) {
                        $(".loader").hide();
                        if (res.status === 'success') {
                            swal("Success", res.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else {
                            swal("Failed", res.message, "error");
                        }
                    }
                });
            }
        }

        function sendPayoutRequest() {
            if ($('#payoutRequestForm').valid()) {
                $(".loader").show();
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/send-payout-request')}}",
                    data: $('#payoutRequestForm').serialize(),
                    success: function (res) {
                        $(".loader").hide();
                        if (res.status === 'success') {
                            swal("Success", res.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else {
                            swal("Failed", res.message, "error");
                        }
                    }
                });
            }
        }

        function uploadDocument() {
            if ($('#uploadDocumentForm').valid()) {
                $('#uploadDocumentModal').modal('hide');
                $(".loader").show();
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/upload-document')}}",
                    data: new FormData($('#uploadDocumentForm')[0]),
                    dataType: 'JSON',
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (res) {
                        $(".loader").hide();
                        if (res.status === 'success') {
                            swal("Success", res.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else {
                            swal("Failed", res.message, "error");
                        }
                    }
                });
            }
        }
    </script>


@endsection
