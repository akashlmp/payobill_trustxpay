<html>
<title>{{ $company_name }}</title>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{url('assets/plugins/bootstrap/css/bootstrap.min.css')}}"></script>
    <link href="{{url('assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet">

</head>
<style>
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

    .fab-icon {
        padding: 5px;
    }
    .model {
            display: none !important;
        }
    html{
            font-family: unset !important;
        }
</style>
<body style="font-family: unset !important;">

<div id="invoice-POS">

    <center id="top">
        <div class="info">
            <img src="{{$cdnLink}}{{$company_logo}}" style="height: 40px;">
            <div style="display: flex">
                <small style="font-size: 10px;margin: 9px 0 0 74px;">Powered by</small>
                <img src="{{asset('assets/img/trustxpay.png')}}" alt="" style="width: 35%;" id="logo">
            </div>
            <h3 style="font-size:medium;margin-top:20px;"><b>Transaction Receipt</b></h3>
            <span style="margin-top:20px;">(Payment Through Cash Collection)</span>
        </div><!--End Info-->
    </center><!--End InvoiceTop-->
    <hr>


    <div id="bot">

        <div id="table">
            <table border="1" style="width:100%;  border: 1px solid black; border-collapse: collapse;">
                <tr>
                    <th>Reference No</th>
                    <td>{{$report_id}}</td>
                </tr>

                <tr>
                    <th>Date & Time</th>
                    <td>{{$created_at}}</td>
                </tr>
                <tr>
                    <th>Provider Name</th>
                    <td>{{$provider_name}}</td>
                </tr>

                <tr>
                    <th>Number</th>
                    <td>{{$number}}</td>
                </tr>

                <tr>
                    <th>Txn Id</th>
                    <td>{{$txnid}}</td>
                </tr>

                <tr>
                    <th>Amount</th>
                    <td>₹{{$amount}}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>{{$status}}</td>
                </tr>

            </table>
        </div><!--End Table-->
        <center id="top">
            <div class="info" style="margin-top:20px">
               <h4 style="font-size: medium;"><b>Shop Name : {{ $agent_name }}</b></h4>
               <h4 style="font-size: medium;margin-top:20px;margin-bottom:20px"><b>Agent Number : {{ $agent_number }}</b></h4>
            </div><!--End Info-->
        </center><!--End InvoiceTop-->



    </div><!--End InvoiceBot-->
</div><!--End Invoice-->

<!-- <div style="text-align: center; margin-top: 2%;">
    <button id="printPageButton" onClick="window.print();">Print</button>
    <a href="javascript:void(0);" type="button" class="floating"  onclick="downloadReceipt()">
        <i class="fa fa-whatsapp fab-icon"></i>
    </a>

</div> -->
<center>
    <div style="display: inline-block; text-align: center; margin-top: 2%;">
        <button onclick="window.print();" style="margin-right: 10px;margin-top:6px;">
            Print
        </button>
        <a href="javascript:void(0);" type="button" class="floating" onclick="downloadReceipt()" style="display: inline-block;">
            <i class="fa fa-whatsapp fab-icon"></i>
        </a>
    </div>
</center>


<style>

    @media print {
        #printPageButton {
            display: none;
        }
    }

    #invoice-POS{
        box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
        padding:2mm;
        margin: 0 auto;
        width: 77mm;
        border: dotted;
        background: #FFF;




    ::selection {background: #f31544; color: #FFF;}
    ::moz-selection {background: #f31544; color: #FFF;}
    h1{
        font-size: 1.5em;
        color: #222;
    }
    h2{font-size: .9em;}
    h3{
        font-size: 1.2em;
        font-weight: 300;
        line-height: 2em;
    }
    p{
        font-size: .7em;
        color: #666;
        line-height: 1.2em;
    }

    #top, #mid,#bot{ /* Targets all id with 'col-' */
        border-bottom: 1px solid #EEE;
    }

    #top{min-height: 100px;}
    #mid{min-height: 80px;}
    #bot{ min-height: 50px;}

    #top .logo{
    //float: left;
        height: 60px;
        width: 60px;
        background: url(http://michaeltruong.ca/images/logo1.png) no-repeat;
        background-size: 60px 60px;
    }
    .clientlogo{
        float: left;
        height: 60px;
        width: 60px;
        background: url(http://michaeltruong.ca/images/client.jpg) no-repeat;
        background-size: 60px 60px;
        border-radius: 50px;
    }
    .info{
        display: block;
    //float:left;
        margin-left: 0;
    }
    .title{
        float: right;
    }
    .title p{text-align: right;}

    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    .tabletitle{
    //padding: 5px;
        font-size: .5em;
        background: #EEE;
    }
    .service{border-bottom: 1px solid #EEE;}
    .item{width: 24mm;}
    .itemtext{font-size: .5em;}

    #legalcopy{
        margin-top: 5mm;
    }



    }
</style>

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
            url: "{{url('mobile-receipt-whatsapp-msg')}}",
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
