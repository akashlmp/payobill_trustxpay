<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#bank_id").select2();
    });

    function device_scans() {
        var mobile_number = $("#mobile_number").val();
        var aadhar_number = $("#aadhar_number").val();
        if (mobile_number.length == 10 && $.isNumeric(mobile_number)) {
            $("#mobile_number_errors").text('');
            if (aadhar_number.length == 12) {
                $("#aadhar_number_errors").text('');
                $(".loader").show();
                var device = $("#device:checked").val();
                if(device == 'MORPHO_PROTOBUF'){
                    morpho_RDServices ();
                }else if (device == 'MANTRA_PROTOBUF'){
                    matra_RDServices ();
                }
            }else{
                $("#aadhar_number_errors").text('Aadhar number should be 12 digit');
            }
        }else{
            $("#mobile_number_errors").text('Mobile number should be 10 digit');
        }
    }

    function morpho_RDServices() {
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
                    morpho_Capture (url, PIDOPTS);
                } else {
                    console.log(xhr.response);
                }
            }
        };
        xhr.send();

    }

    function matra_RDServices() {
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
                    Device_Captures (url, PIDOPTS);
                } else {
                    console.log(xhr.response);
                }
            }
        };
        xhr.send();
    }

    function Device_Captures(url, PIDOPTS) {
        // var url = "http://127.0.0.1:11100/capture";
        // var PIDOPTS='<PidOptions ver=\"1.0\">'+'<Opts fCount=\"1\" fType=\"0\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>'+'</PidOptions>';
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
                        $("#ci" ).val($ci);
                        $("#pidtype" ).val($type);
                        $("#BiometricData").val(xhr.responseText);
                        $('#scan_btn').attr('disabled', true);
                        $('#submit_btn').attr('disabled', false);
                        $('#mini_submit_btn').attr('disabled', false);
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

</script>


<input id="method" type="hidden" value="">
<input id="info" type="hidden" value="">
<input id="BiometricData" type="hidden" value="">
<input id="pidtype" type="hidden" value="">
<input id="ci" type="hidden" value="">



<div class="modal  show" id="condtion_for_retailer"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Terms And Conditions For User</h6>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <p style="text-align: justify;">I hereby confirm that the Customer has been explained (in a language know to the Customer) about the nature of information, sharing of such information upon authentication, aspects of the transaction and the terms and conditions applicable to this transaction and the Customer has understood and authorized BANK to fetch applicable data from UIDAI; <br>2. I hereby confirm that the Customer has provided all the information voluntarily to the Bank and are true, correct and complete.
</p>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal  show" id="condtion_for_customer"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Terms And Conditions For Customer</h6>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <p style="text-align: justify;">1. I, the Customer hereby authorizes the Bank store my AADHAR number and to fetch, authenticate and store all such necessary details retrieved or to be retrieved from UIDAI through AADHAR Number and IRIS/biometric authentication for the purpose of this transaction. In case of any discrepancies, the Bank reserves the sole right to block my account/ relation and transaction without any further notice or intimation</p>
                        <p style="text-align: justify;">2. Customer hereby states and undertake that it has no objection, if the information disclosed to the Bank is authenticated vide Aadhaar based system. Customer hereby voluntarily agree and confirm to provide or disclose (as required under the Aadhaar Act 2016 and Regulations framed there under) my identification information (Aadhaar number, biometric information & demographic information) for Aadhaar based authentication system and/or such similar authentication mechanism as provided or stipulated by the Government, from time to time, for the purpose of availing banking services, including operations of account or any other facility relating to banking operations. </p>
                        <p style="text-align: justify;">3. The Customer hereby agree and confirm that the Bank will not be obliged to process any request made by me, if: a. Aadhaar number provided by me is incorrect b. Details in account does not match with details available with UIDAI </p>
                        <p style="text-align: justify;">4. The Customer hereby agree and confirm that the Aadhaar details may be updated for all my banking services, including but not limited to the operation of bank account, for the purposes of authentication.</p>
                        <p style="text-align: justify;">5. The Customer hereby agree and confirm that my account may be used for receiving any government payments across schemes that the Customer is eligible and/ or any other payment using the Aadhaar based information. The Customer hereby acknowledge and agree that NPCI may map the Customer\92s account in the Aadhaar Mapper of NPCI.</p>
                        <p style="text-align: justify;">6. The Customer hereby confirms that the Aadhaar detail provided by the Customer is true, correct and complete in all aspect</p>
                        <p style="text-align: justify;">7. The Customer hereby acknowledges that the Bank will not have any liability or responsibility, if the details provided by the Customer is or subsequently becomes false, incorrect or incomplete, either in part or as a whole.</p>
                        <p style="text-align: justify;">8. Before agreeing to the above-mentioned terms and conditions, the Customer have been explained (in a language know to the Customer) about the nature of information that may be shared upon authentication and have understood the nature and implication of the same, as stipulated under the applicable law. The Customer declares that all the information has been voluntarily furnished by them to the Bank, without any duress or coercion.</p>
                        <p style="text-align: justify;">9. The Customer hereby gives consent to use/exchange or share their Aadhaar number, Aadhaar information for registration of client information with Exchange, KRA, CERSAI and with any other regulatory or statutory authorities or as the Bank deems fit or as per requirements of law.</p>
                        <p style="text-align: justify;">10. The Customer hereby understands and agrees that all identity information provided by the Customer will only be used for on boarding me for the purpose of the transaction. </p>
                        <p style="text-align: justify;">11. The Customer hereby understands and agrees that Bank may disclose the identity information provided by the Customer to only CIDR for the purpose of authentication or authorization </p>
                        <p style="text-align: justify;">12. The Customer hereby understands and agrees that the biometric authentication may be treated as my signature </p>
                        <p style="text-align: justify;">13. The Customer hereby declare that the above information has been provided voluntarily out of his/her own discretion and volition. All information provided by the Customer or information/date retrieved from UIDAI in respect of the Customer is true, correct, updated and complete.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
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

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Shop Address: </div>
                            </div>
                            <span class="float-right ml-auto shop_address"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-info" id="aeps_receipt_anchor" target="_blank"><i class="fas fa-print"></i> Print</a>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal  show" id="mini_aeps_receipt_model" data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> <img src="{{$cdnLink}}{{$company_logo}}" alt="Logo" style="height: 40px;"></h6>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="alert alert-success" role="alert" id="ms_success_message" style="text-align: center; font-size: 16px; padding: 5px 20px;"></div>
                    <div class="task-stat pb-0">
                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Bank Name: </div>
                            </div>
                            <span class="float-right ml-auto bank_name"></span>
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

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Shop Address: </div>
                            </div>
                            <span class="float-right ml-auto shop_address"></span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="table-responsive mb-0">
                        <table class="table table-striped mg-b-0 text-md-nowrap">
                            <thead>
                            <tr>
                                <th>Ministatement</th>
                            </tr>
                            </thead>
                            <tbody class="ministatement_list">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-info" id="ms_aeps_receipt_anchor" target="_blank"><i class="fas fa-print"></i> Print</a>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
