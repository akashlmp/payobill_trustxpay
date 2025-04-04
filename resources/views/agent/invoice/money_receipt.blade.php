<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company_name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="{{url('assets/plugins/bootstrap/css/bootstrap.min.css')}}"></script>
    <link href="{{url('assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet">

    <style>
        @media print {
            #inv {
                width: 99% !important;
                margin: 0px !important;

            }

            .logsec {
                padding: 2px 20px !important;
            }

            .sd {
                margin-top: 0px !important;
            }

            .st {
                margin: 0px !important;
            }

            .btnss {
                display: none !important;
            }

            .spac {
                padding: 4px 8px !important;
            }

            #logo {
                width: 18% !important;
            }

            #invh h1 {
                font-size: 18px !important;
            }

            #invh p {
                font-size: 12px !important;
            }

            #width {
                width: 32.5% !important;
            }

            #width h3 {
                padding: 5px 10px !important;
                font-size: 16px !important;
            }

            #ptag {
                font-size: 12px !important;
                margin: 0px !important;
                margin-bottom: 2px !important;
            }

            #font {
                font-size: 18px !important;
            }
        }

        /* Add WA floating button CSS */
        /* .floating {
            position: fixed;
            width: 40px;
            height: 40px;
            margin: -8px 0 8px 8px;
            background-color: #25d366;
            color: #fff;
            border-radius: 50px;
            text-align: center;
            font-size: 30px;
            z-index: 100;
        }

        .fab-icon {
            padding: 5px;
        } */

        .floating {
            position: fixed !important;
            width: 40px !important;
            height: 40px !important;
            background-color: #25d366 !important;
            color: #fff !important;
            border-radius: 50% !important; /* Make it a perfect circle */
            text-align: center !important;
            font-size: 30px !important; /* Adjust font size for better fit */
            z-index: 100 !important;
            display: flex; /* Use flexbox for centering */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
        }

        .model {
            display: none !important;
        }
        html{
            font-family: unset !important;
        }
    </style>
</head>

<body style="margin: 0px; padding: 0px; box-sizing: border-box;font-family: unset !important;">
<div style="border: 1px solid #333; margin: 50px auto; width: 80%;" id="inv">
    <div style=" display:flex;
                        justify-content:space-between;
                        align-items: center;
                        padding: 10px 20px;
                        border-bottom: 1px solid #333;" class="logsec">
        <div style="width: 33%;text-align: left;">
            <img src="{{$cdnLink}}{{$company_logo}}" alt="" style="width: 24%;" id="logo">
            <div style="display: flex">
                <small style="font-size: 10px;margin: 17px 0 0 0px;">Powered by</small>
                <img src="{{asset('assets/img/trustxpay.png')}}" alt="" style="width: 30%;" id="logo">
            </div>
          {{--  <div>
                <small style="font-size: 10px;">Powered by</small>
                <img src="{{asset('assets/img/trustxpay.png')}}" alt="" style="width: 20%;" id="logo">
            </div>--}}
        </div>
        <div style="width: 33%;text-align: center;" id="invh">
            <h1 style="margin: 0px; padding: 0px 20px;">Invoice</h1>
            <p style="margin: 0px; padding: 0px 20px; margin-bottom: 2px;">{{$created_at}}</p>
        </div>
        <div style="width: 33%;text-align: right;">
            <a href="" style="color:#333; display: block; text-decoration: none;">GSTIN- 09AALCP9498B1ZS</a>
            <a href=""
               style="color:#333; display: block; text-decoration: none;">Email- {{env('SUPPORT_EMAIL','info@trustxpay.org')}}</a>
            {{--<a href="" style="color:#333; display: block; text-decoration: none;">{{ $company_email }}</a>--}}

        </div>
    </div>


    <div
        style="display: flex; justify-content: space-between; align-items: flex-start; padding: 10px 20px;margin-top: 5px;"
        class="sd spac">
        <div style="border:1px solid #ddd; border-radius:4px; width: 31%;" id="width">
            <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;"
                class="spac">
                agent details</h3>
            <div style="padding:20px; text-transform: capitalize;" class="spac">
                <p style="margin: 0px; margin-bottom: 8px; white-space: nowrap;" id="ptag"><b>Shop name
                        : </b>{{$agent_name}}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>Mobile No : </b>{{$agent_number}}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>agent address : </b>{{ $office_address }}</p>

            </div>
        </div>
        <div style="border:1px solid #ddd; border-radius:4px; width: 31%;" id="width">
            <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;"
                class="spac">
                customer details</h3>
            <div style="padding:20px; text-transform: capitalize;" class="spac">
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>Remitter name : </b>{{$remiter_name}}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>mode : </b>{{$channel}}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>Remitter number : </b>{{$remiter_number}}</p>

            </div>
        </div>
        <div style="border:1px solid #ddd; border-radius:4px; width: 31%;" id="width">
            <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;"
                class="spac">
                beneficiary details</h3>
            <div style="padding:20px; text-transform: capitalize;" class="spac">
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>bank name : </b>{{ $bank_name }}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>name : </b>{{ $beneficiary_name }}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>account number : </b>{{ $account_number }}</p>

            </div>
        </div>
    </div>
    <div style="padding: 0px 20px;">
        <table style="width: 100%; border: 1px solid #ddd; border-collapse: collapse;">
            <tr>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize;width: 20%;"
                    class="spac">order id
                </th>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">UTR number
                </th>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">amount
                </th>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">charge
                </th>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">status
                </th>
            </tr>
            @foreach($reports as $value)
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                        class="spac">{{ $value->id }}</td>
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                        class="spac">{{ $value->txnid }}</td>
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                        class="spac">₹{{ number_format($value->amount, 2) }}</td>
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                        class="spac">{{env('CHARGE_PERCENTAGE',1.2)}}%
                    </td>
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                        class="spac">
                        <span
                            style="padding:5px 10px; background-color:rgb(122, 122, 122); color:#fff; border-radius:8px;">{{ $value->status->status }}</span>
                    </td>

                </tr>
            @endforeach

        </table>
    </div>
    <div
        style="display: flex; justify-content: space-between; padding: 0px 20px; margin-top: 0px; margin-bottom: 0px; align-items: flex-start;"
        class="st">
        <div style="width: 60%; padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; margin-top: 10px;"
             class="spac">
            <p style="margin: 0px;" id="ptag"><b>Notes: </b>This is a Computer Generated Receipt. Signature Not
                Required. Amount is inclusive of GST.
                <br>Website: https://trustxpay.org.
               {{-- <br>Transaction charges are inclusive of GST--}}
            </p>
        </div>
        <div style="border: 1px solid #ddd; width: 33%; margin-bottom: 10px; margin-top: 10px;" id="width">
            <h3 style="padding: 6px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px; text-align: center;"
                class="spac">Total Amount</h3>
            <h2 style="text-align: center; padding: 10px; margin: 0px;" class="spac" id="font">
                Rs. {{ $full_amount }}/-
            </h2>
            <h2 style="text-align: center; padding: 10px; margin: 0px;" class="spac" id="font">
                {{Helpers::convertNumber($full_amount) .' Only'}}
            </h2>
        </div>
    </div>

    <center>
        <div style="display: inline-block; text-align: center; margin-bottom: 20px;">
            <button onclick="window.print();" style="margin-right: 10px;margin-top:6px;">
                Print
            </button>
            <a href="javascript:void(0);" type="button" class="floating" onclick="downloadReceipt()" style="display: inline-block;">
                <i class="fa fa-whatsapp fab-icon"></i>
            </a>
        </div>
    </center>

</div>

<div class="modal show" id="member_download_model" data-toggle="modal" style="display: none !important;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"><b>Download Receipt<b></h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Mobile Number</label>
                                <input type="text" id="mobile_number" class="form-control" placeholder="Enter Mobile Number">

                                <span id="mobile_number_errors" class="text-danger"> </span>
                            </div>
                        </div>



                    </div>

                </div>


            </div>

            <div class="modal-footer">
                <input type="hidden" id="reportid" name="reportid" value="{{$id}}">
                <button class="btn ripple btn-primary" type="button" id="download_btn" onclick="sendWhatsappMsg()"><b>Send Whatsapp Message</b></button>
                <button class="btn btn-primary" type="button"  id="download_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button"><b>Close</b></button>
            </div>
        </div>
    </div>
</div>
{!! csrf_field() !!}
    <!--- JQuery min js --->
    <script src="{{url('assets/plugins/jquery/jquery.min.js')}}"></script>
    <!--- Bootstrap Bundle js --->
    <script src="{{url('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
    <script src="{{url('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
    <script src="{{url('assets/plugins/sweet-alert/jquery.sweet-alert.js')}}"></script>

  <script type="text/javascript">
    function downloadReceipt() {

        $('#member_download_model').modal('show');
    }

    function sendWhatsappMsg() {
        $("#download_btn").hide();
        $("#download_btn_loader").show();
        var token = $("input[name=_token]").val();
        console.log(token);
        var mobile_number = $("#mobile_number").val();
        var id = $("#reportid").val();

        var dataString = 'id=' +id+'&mobile_number=' + mobile_number + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('money-receipt-whatsapp-msg')}}",
            data: dataString,
            success: function (msg) {
                $("#download_btn").show();
                $("#download_btn_loader").hide();
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 1000);
                } else if (msg.status == 'validation_error') {
                    $("#mobile_number_errors").text(msg.errors.mobile_number);
                } else {
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }
  </script>
</body>

</html>
