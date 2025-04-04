@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#payment_date").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });

        });

        function approveReject(id, type) {
            var typeText = (type == 1) ? 'approve' : 'reject';
            swal({
                    title: "Are you sure?",
                    text: 'you want to ' + typeText + ' this bank ',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, " + typeText + " it!",
                    cancelButtonText: "No, cancel",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function (isConfirm) {
                    if (isConfirm) {
                        var token = $("input[name=_token]").val();
                        var dataString = 'id=' + id + '&type=' + type + '&_token=' + token;
                        $.ajax({
                            type: "POST",
                            url: "{{url('admin/approve-reject-bank')}}",
                            data: dataString,
                            success: function (msg) {
                                $(".loader").hide();
                                if (msg.status == 'success') {
                                    swal("Success", msg.message, "success");
                                    setTimeout(function () {
                                        location.reload(1);
                                    }, 3000);
                                } else {
                                    swal("Failed", msg.message, "error");
                                }
                            }
                        });
                    } else {
                        swal.close();
                    }
                });
        }

        function payment_request() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var bankdetail_id = $("#bankdetail_id").val();
            var paymentmethod_id = $("#paymentmethod_id").val();
            var payment_date = $("#payment_date").val();
            var amount = $("#amount").val();
            var bankref = $("#bankref").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude) {
                var dataString = 'bankdetail_id=' + bankdetail_id + '&paymentmethod_id=' + paymentmethod_id + '&payment_date=' + payment_date + '&amount=' + amount + '&bankref=' + bankref + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/save-payment-request')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else if (msg.status == 'validation_error') {
                            $("#bankdetail_id_errors").text(msg.errors.bankdetail_id);
                            $("#paymentmethod_id_errors").text(msg.errors.paymentmethod_id);
                            $("#payment_date_errors").text(msg.errors.payment_date);
                            $("#amount_errors").text(msg.errors.amount);
                            $("#bankref_errors").text(msg.errors.bankref);
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            } else {
                getLocation();
                alert('Please allow this site to access your location');
            }
        }

    </script>

    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Bank white list request</h6>
                            <hr>
                        </div>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example2" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Retailer NAME</th>
                                    <th class="wd-15p border-bottom-0">Mobile</th>
                                    <th class="wd-15p border-bottom-0">BANK NAME</th>
                                    <th class="wd-15p border-bottom-0">PAYEE NAME</th>
                                    <th class="wd-15p border-bottom-0">ACCOUNT NUMBER</th>
                                    <th class="wd-15p border-bottom-0">IFSC CODE</th>
                                    <th class="wd-15p border-bottom-0">Bank proof</th>
                                    <th class="wd-15p border-bottom-0">ACTION</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($bankDetails as $value)
                                    <tr>

                                        <td>{{$value->retailer_name}}</td>
                                        <td>{{$value->mobile}}</td>
                                        <td>{{$value->bank_name}}</td>
                                        <td>{{ $value->payee_name }}</td>
                                        <td>{{ $value->account_number }}</td>
                                        <td>{{ $value->ifsc_code }}</td>
                                        <td> @if($value->bank_proof)
                                                <a href="{{asset($value->bank_proof)}}" target="_blank"><i class="fas fa-eye"></i><a/>
                                            @endif</td>
                                        <td>
                                            @if($value->status == 0)
                                                <a class="btn btn-sm btn-success text-white"
                                                   style="cursor: pointer !important;"
                                                   onclick="approveReject({{ $value->id }},1)"><i
                                                        class="fas fa-check"></i> Approve</a>
                                                <a class="btn btn-sm btn-danger"
                                                   onclick="approveReject({{ $value->id }},2)"
                                                   style="cursor: pointer !important;"><i
                                                        class="fas fa-ban"></i> Reject</a>
                                            @endif

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>


                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>
    </div>


    @include('agent.service.recharge_confirm')

@endsection
