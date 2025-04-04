/*Scanner */
function scanBiometric() {
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
                var PIDOPTS = '<\?xml version="1.0"?><PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="10000" posh="UNKNOWN" env="P" wadh="18f4CEiXeXcfGXvgWA/blxD+w2pw7hfQPY45JMytkPw=" /> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
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
                    $("#BiometricDataPid").val(xhr.responseText);
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
