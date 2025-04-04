@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        (function () {
            $(document).ready(function () {
                getLocation();
                $("#bank_id").select2();
            });

            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition, showError);
                } else {
                    alert("Geolocation is not supported by this browser.");
                }
            }

            function showPosition(position) {
                $("#inputLatitude").val(position.coords.latitude);
                $("#inputLongitude").val(position.coords.longitude);
            }

            function showError(error) {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        alert("User denied the request for Geolocation.");
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert("Location information is unavailable.");
                        break;
                    case error.TIMEOUT:
                        alert("The request to get user location timed out.");
                        break;
                    case error.UNKNOWN_ERROR:
                        alert("An unknown error occurred.");
                        break;
                }
            }

            function scanBiometric() {
                $(".loader").show();
                matra_RDService();
            }

            function matra_RDService() {
                const url = "http://127.0.0.1:11100";
                const xhr = createXHR();

                xhr.open('RDSERVICE', url, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            const captureUrl = "http://127.0.0.1:11100/rd/capture";
                            const PIDOPTS = '<\?xml version="1.0"?><PidOptions ver="1.0"><Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="10000" posh="UNKNOWN" env="P" wadh="" /><CustOpts><Param name="mantrakey" value="" /></CustOpts></PidOptions>';
                            Device_Capture(captureUrl, PIDOPTS);
                        } else {
                            console.error(xhr.response);
                        }
                    }
                };
                xhr.send();
            }

            function createXHR() {
                if (window.XMLHttpRequest) {
                    return new XMLHttpRequest();
                } else {
                    return new ActiveXObject("Microsoft.XMLHTTP");
                }
            }

            function Device_Capture(url, PIDOPTS) {
                const xhr = createXHR();
                xhr.open('CAPTURE', url, true);
                xhr.setRequestHeader("Content-Type", "text/xml");
                xhr.setRequestHeader("Accept", "text/xml");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        $(".loader").hide();
                        if (xhr.status === 200) {
                            const xmlDoc = $.parseXML(xhr.responseText);
                            const $xml = $(xmlDoc);
                            const errCode = $xml.find("Resp")[0].attributes.getNamedItem("errCode").nodeValue;

                            if (errCode == 0) {
                                $("#BiometricData").val(xhr.responseText);
                                $('#scanBtns').attr('disabled', true);
                                $('#submitBtns').attr('disabled', false);
                                $("#success_alert").show().html('Finger fetch successfully..');
                            } else {
                                const errInfo = $xml.find("Resp")[0].attributes.getNamedItem("errInfo").nodeValue;
                                $("#failure_alert").show().html(errInfo);
                            }
                        }
                    }
                };
                xhr.send(PIDOPTS);
            }

            function sendData() {
                const latitude = $("#inputLatitude").val();
                const longitude = $("#inputLongitude").val();
                if (latitude && longitude) {
                    $(".loader").show();
                    const token = $("input[name=_token]").val();
                    const bank_id = $("#bank_id").val();
                    const adhaar_number = $("#adhaar_number").val();
                    const amount = $("#amount").val();
                    const mobile_number = $("#mobile_number").val();
                    const BiometricData = $("#BiometricData").val();
                    const dataString = {
                        bank_id: bank_id,
                        adhaar_number: adhaar_number,
                        amount: amount,
                        BiometricData: encodeURIComponent(BiometricData),
                        mobile_number: mobile_number,
                        latitude: latitude,
                        longitude: longitude,
                        _token: token
                    };
                    $.ajax({
                        type: "POST",
                        url: "{{url('agent/cash-deposit/v1/initiate')}}",
                        data: dataString,
                        success: function (msg) {
                            $(".loader").hide();
                            if (msg.status === 'success') {
                                $(".bank_name").text(msg.data.bank_name);
                                $(".amount").text(msg.data.amount);
                                $(".total_balance").text(msg.data.total_balance);
                                $(".utr").text(msg.data.utr);
                                $(".aadhar_number").text(msg.data.aadhar_number);
                                $(".shop_name").text(msg.data.shop_name);
                                $("#success_message").text(msg.data.message);
                                $("#aeps_receipt_model").modal('show');
                            }else if (msg.status === 'pending'){
                                swal("Pending", msg.message, "warning");
                            }else {
                                swal("Failed", msg.message, "error");
                                $('#scanBtn').attr('disabled', false);
                                $('#submitBtn').attr('disabled', true);
                            }
                        },
                        error: function (xhr, status, error) {
                            $(".loader").hide();
                            swal("Error", "An error occurred: " + error, "error");
                        }
                    });
                } else {
                    getLocation();
                    alert('Please allow this site to access your location');
                }
            }

            window.scanBiometric = scanBiometric;
            window.sendData = sendData;
        })();
    </script>


    <input type="hidden" id="BiometricData">

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
                            <label>Select Bank</label>
                            <select class="form-control select2" id="bank_id" style="width: 100%;">
                                @foreach($banks as $value)
                                    <option value="{{$value->iinno}}">{{ $value->bank_name }}</option>
                                @endforeach
                            </select>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="bank_id_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Mobile Number</label>
                            <input type="text" class="form-control" placeholder="Mobile Number" id="mobile_number">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="mobile_number_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Adhaar Number</label>
                            <input type="text" class="form-control" placeholder="Adhaar Number" id="adhaar_number">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="adhaar_number_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Amount</label>
                            <input type="text" class="form-control" placeholder="Amount" id="amount">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="amount_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Device</label>
                            <select class="form-control" id="device" name="device">
                                <option value="MANTRA_PROTOBUF">Mantra</option>
                                <option value="MORPHO_PROTOBUF">Morpho</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-danger" type="button" id="scanBtns" onclick="scanBiometric()">Scan</button>
                        <button class="btn ripple btn-success" type="button" id="submitBtns" onclick="sendData()" disabled>Submit</button>
                    </div>
                </div>
            </div>


        </div>

    </div>
    </div>
    </div>

    <div class="modal show" id="aeps_receipt_model" data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title"> <img src="{{$cdnLink}}{{$company_logo}}" alt="Logo" style="height: 40px;"></h6>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="alert alert-success" role="alert" id="success_message" style="text-align: center; font-size: 16px; padding: 5px 20px;"></div>
                        <div class="task-stat pb-0">
                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Bank Name: </div>
                                </div>
                                <span class="float-right ml-auto bank_name"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Amount: </div>
                                </div>
                                <span class="float-right ml-auto amount"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Account Balance: </div>
                                </div>
                                <span class="float-right ml-auto total_balance"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">UTR Number: </div>
                                </div>
                                <span class="float-right ml-auto utr"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Aadhar Number: </div>
                                </div>
                                <span class="float-right ml-auto aadhar_number"></span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Shop Name: </div>
                                </div>
                                <span class="float-right ml-auto shop_name"></span>
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