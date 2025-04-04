<script type="text/javascript">

    $(document).ready(function () {
        getLocation();
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

    function merchant_auth_device_scans() {
        var aadhar_number = $("#merchant_aadhar_number").val();
        if (aadhar_number){
            $(".loader").show();
            var device = $("#device:checked").val();
            if(device == 'MORPHO_PROTOBUF'){
                merchant_auth_morpho_RDService ();
            }else if (device == 'MANTRA_PROTOBUF'){
                merchant_auth_matra_RDService ();
            }
        }else{
            alert('Please enter merchant aadhar number');
        }
    }

    function merchant_auth_morpho_RDService() {
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
            if (xhr.readyState == 4){
                var status = xhr.status;
                if (status == 200) {
                    var url = "http://127.0.0.1:11100/capture";
                    var PIDOPTS='<\?xml version="1.0"?><PidOptions ver=\"1.0\">'+'<Opts fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>'+'</PidOptions>';
                    merchant_auth_capture (url, PIDOPTS);
                } else {
                    console.log(xhr.response);
                }
            }
        };
        xhr.send();


    }

    function merchant_auth_matra_RDService() {
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
            if (xhr.readyState == 4){
                var status = xhr.status;
                if (status == 200) {
                    var url = "http://127.0.0.1:11100/rd/capture";
                    var PIDOPTS= '<\?xml version="1.0"?><PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="10000" posh="UNKNOWN" env="P" wadh="" /> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
                    merchant_auth_capture (url, PIDOPTS);
                } else {
                    console.log(xhr.response);

                }
            }

        };
        xhr.send();
    }


    function merchant_auth_capture (url, PIDOPTS){
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
        xhr.setRequestHeader("Content-Type","text/xml");
        xhr.setRequestHeader("Accept","text/xml");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4){
                var status = xhr.status;
                $(".loader").hide();
                if (status == 200) {
                    xmlDoc = $.parseXML( xhr.responseText ),
                        $xml = $( xmlDoc ),
                        $errCode = $xml.find("Resp")[0].attributes.getNamedItem("errCode").nodeValue;
                    var errCode = $errCode;
                    if (errCode == 0){
                        $ci = $xml.find("Skey")[0].attributes.getNamedItem("ci").nodeValue;
                        $type = $xml.find("Data")[0].attributes.getNamedItem("type").nodeValue;
                        $("#BiometricData").val(xhr.responseText);
                        merchantAuthInitiate();
                        $("#success_alert").show();
                        $("#success_alert").html('Finger fetch successfully..');
                    }else {
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

    function merchantAuthInitiate() {
        var latitude = $("#inputLatitude").val();
        var longitude = $("#inputLongitude").val();
        if (latitude && longitude) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var BiometricData = $("#BiometricData").val();
            var aadhar_number = $("#merchant_aadhar_number").val();
            var dataString = 'BiometricData=' + encodeURIComponent(BiometricData) + '&aadhar_number=' + aadhar_number +  '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/aeps/v2/merchant-auth-initiate')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#MerAuthTxnId").val(msg.MerAuthTxnId);
                        $(".customer-scan-label").show();
                        $(".merchant-scan-label").hide();
                    }else{
                        swal("Failed", msg.message, "error");
                        $('#merchantAuthScanBtn').attr('disabled', false);
                        $('#merchantAuthSubmitBtn').attr('disabled', true);
                    }
                }
            });
        } else {
            getLocation();
            alert('Please allow this site to access your location');
        }
    }
</script>

<input type="hidden" id="MerAuthTxnId">
