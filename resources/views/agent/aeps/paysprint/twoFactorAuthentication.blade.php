@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">

        $(document).ready(function () {
            getLocation();
            $(".single_select2").select2();

        });

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                x.innerHTML = "Geolocation is not supported by this browser.";
            }
        }

        function showPosition(position) {
            $("#inputLatitude").val(position.coords.latitude);
            $("#inputLongitude").val(position.coords.longitude);
        }


        function scanBiometric() {
            $(".loader").show();
            var device = $("#device:checked").val();
            if (device == 'MORPHO_PROTOBUF') {
                morpho_RDService();
            } else if (device == 'MANTRA_PROTOBUF') {
                matra_RDService();
            }
        }

        function morpho_RDService() {
            var url = "http://127.0.0.1:11100";
            var xhr;
            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            } else {
                xhr = new XMLHttpRequest();
            }
            xhr.open('RDSERVICE', url, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    var status = xhr.status;
                    if (status == 200) {
                        var url = "http://127.0.0.1:11100/capture";
                        var PIDOPTS = '<\?xml version="1.0"?><PidOptions ver=\"1.0\">' + '<Opts fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>' + '</PidOptions>';
                        morpho_Capture(url, PIDOPTS);
                    } else {
                        console.log(xhr.response);
                    }
                }
            };
            xhr.send();
        }

        function matra_RDService() {
            var url = "http://127.0.0.1:11100";
            // var url=  $('#method').val();
            var xhr;
            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            } else {
                xhr = new XMLHttpRequest();
            }

            xhr.open('RDSERVICE', url, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    var status = xhr.status;
                    if (status == 200) {
                        var url = "http://127.0.0.1:11100/rd/capture";
                        var PIDOPTS = '<\?xml version="1.0"?><PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="10000" posh="UNKNOWN" env="P" wadh="" /> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
                        Device_Capture(url, PIDOPTS);
                    } else {
                        console.log(xhr.response);
                    }
                }
            };
            xhr.send();
        }

        function Device_Capture(url, PIDOPTS) {
            var xhr;
            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");
            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
                //IE browser
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            } else {
                //other browser
                xhr = new XMLHttpRequest();
            }
            xhr.open('CAPTURE', url, true);
            xhr.setRequestHeader("Content-Type", "text/xml");
            xhr.setRequestHeader("Accept", "text/xml");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    var status = xhr.status;
                    $(".loader").hide();
                    if (status == 200) {
                        xmlDoc = $.parseXML(xhr.responseText),
                            $xml = $(xmlDoc),
                            $errCode = $xml.find("Resp")[0].attributes.getNamedItem("errCode").nodeValue;
                        var errCode = $errCode;
                        if (errCode == 0) {
                            $ci = $xml.find("Skey")[0].attributes.getNamedItem("ci").nodeValue;
                            $type = $xml.find("Data")[0].attributes.getNamedItem("type").nodeValue;
                            $("#BiometricData").val(xhr.responseText);
                            $('#scanBtns').attr('disabled', true);
                            $('#submitBtns').attr('disabled', false);
                            $("#success_alert").show();
                            $("#success_alert").html('Finger fetch successfully..');
                        } else {
                            $errInfo = $xml.find("Resp")[0].attributes.getNamedItem("errInfo").nodeValue;
                            var errInfo = $errInfo;
                            $("#failure_alert").show();
                            $("#failure_alert").html(errInfo);
                        }
                    }
                }
            };
            xhr.send(PIDOPTS);
        }

        function sendData() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var mobile_number = $("#mobile_number").val();
                var aadhar_number = $("#aadhar_number").val();
                var pipe = $("#pipe").val();
                var BiometricData = $("#BiometricData").val();
                var dataString = 'mobile_number=' + mobile_number + '&aadhar_number=' + aadhar_number + '&pipe=' + pipe + '&BiometricData=' + encodeURIComponent(BiometricData) + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/aeps/v2/two-factor-authentication')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            // setTimeout(function () { location.reload(1); }, 3000);
                        } else {
                            swal("Failed", msg.message, "error");
                            $('#scanBtn').attr('disabled', false);
                            $('#submitBtn').attr('disabled', true);
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

            @include('agent.aeps.paysprint.leftSideMenu')

            <div class="col-lg-4 col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>

                        <div class="alert alert-danger" role="alert" id="failure_alert" style="display: none;"></div>
                        <div class="alert alert-success" role="alert" id="success_alert" style="display: none;"></div>
                        <input type="hidden" id="BiometricData">
                        <div class="mb-4">
                            <label>Mobile Number</label>
                            <input type="text" class="form-control" placeholder="Mobile Number" id="mobile_number"
                                   value="{{ Auth::User()->mobile }}">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="mobile_number_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Aadhar Number</label>
                            <input type="text" class="form-control" placeholder="Aadhar Number" id="aadhar_number"
                                   value="{{ Auth::User()->member->aadhar_number }}">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="aadhar_number_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Pipe</label>
                            <select class="form-control single_select2" id="pipe">
                                <option value="" disabled selected hidden>-- Select Pipe --</option>
                                @if(env('AEPS_MODE') == 'TEST')
                                    <option value="bank1">BANK1</option>
                                @else
                                    <option value="bank2">BANK2</option>
                                    <option value="bank3">BANK3</option>
                                @endif
                            </select>
                        </div>

                        <div class="mb-4">
                            <label>Device</label>
                            <label class="rdiobox"><input checked name="device" id="device" value="MANTRA_PROTOBUF"
                                                          type="radio"> <span>Mantra/ Morpho</span></label>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-danger" type="button" id="scanBtns" onclick="scanBiometric()">
                            Scan
                        </button>
                        <button class="btn ripple btn-success" type="button" id="submitBtns" onclick="sendData()"
                                disabled>Submit
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
    </div>

@endsection
