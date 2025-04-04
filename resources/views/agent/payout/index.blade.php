@extends('agent.layout.header')
@section('content')
    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <form id="payoutRequestForm">
                        @csrf
                        <input type="hidden" name="latitude" id="payoutLatitude">
                        <input type="hidden" name="longitude" id="payoutLongitude">
                        <div class="card-body">
                            <div>
                                <h6 class="card-title mb-1">{{ $page_title }}</h6>
                                <hr>
                            </div>
                            <div class="mb-4 form-error">
                                <label>Bank Name</label>
                                <select class="form-control required" name="bank_id" id="bankdetail_id"
                                    title="The bank field is required">
                                    <option value="" disabled selected hidden>-- Select Bank --</option>
                                    @foreach ($verified_accounts as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4 form-error">
                                <label>Payment Method</label>
                                <select class="form-control required" name="payment_mode" id="payment_mode"
                                    title="The payment method field is required">
                                    <option value="IMPS">IMPS</option>
                                    <option value="NEFT">NEFT</option>
                                    {{-- <option value="RTGS">RTGS</option> --}}
                                </select>
                            </div>

                            <div class="mb-4 form-error">
                                <label>Amount</label>
                                <input type="text" name="amount" class="form-control required" placeholder="Amount"
                                    id="amount" onkeyup="amountToWords();" title="The amount field is required">
                                <strong style="color: red;" id="amountToWordsText"></strong>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button class="btn ripple btn-primary" type="button" onclick="sendPayoutRequest()"
                                id="payoutBtn">SEND NOW
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="card-title float-left mb-1">Bank Details</h6>
                            <button id="addAccount" class="btn btn-success float-right">ADD ACCOUNT</button>
                        </div>
                        <hr>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                    <tr>
                                        <th class="wd-50p border-bottom-0">Bank Name</th>
                                        <th class="wd-15p border-bottom-0">Account Name</th>
                                        <th class="wd-15p border-bottom-0">Account Number</th>
                                        <th class="wd-25p border-bottom-0">IFsc code</th>
                                        <th class="wd-10p border-bottom-0">Verified</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accounts as $value)
                                        <tr>
                                            <td>{{ $value->bank_name }}</td>
                                            <td>{{ $value->name }}</td>
                                            <td>{{ $value->account_no }}</td>
                                            <td>{{ $value->ifsc }}</td>
                                            <td>
                                                @if ($value->status == 1)
                                                    <span class="badge badge-success">Verified</span>
                                                @else
                                                    <button class="btn btn-danger btn-sm uploadDocument m-2 mr-2"
                                                        data-id="{{ $value->bene_id }}">Upload
                                                    </button>
                                                    <button class="btn btn-info btn-sm checkDocumentStatus" style="width: 90px"
                                                        data-id="{{ $value->bene_id }}">Check Status
                                                    </button>
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
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Payout Request</h6>
                            <hr>
                        </div>
                        <div class="table-responsive">
                            <table class="display responsive nowrap"  id="my_table">
                                <thead>
                                    <tr>
                                        <th class="wd-10p border-bottom-0">Request Date</th>
                                        <th class="wd-15p border-bottom-0">Bank</th>
                                        <th class="wd-15p border-bottom-0">Account Number</th>
                                        <th class="wd-15p border-bottom-0">Transation ID</th>
                                        <th class="wd-5p border-bottom-0">Amount</th>
                                        <th class="wd-10p border-bottom-0">Status</th>
                                        <th class="wd-10p border-bottom-0">Method</th>
                                    </tr>
                                </thead>
                               
                            </table>
                            <script type="text/javascript">
                                $(document).ready(function(){

                                                // DataTable
                                    $('#my_table').DataTable({

                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $urls }}",
                                        columns: [
                                            { data: 'created_at' },
                                            { data: 'bank_name' },
                                            { data: 'account_no' },
                                            { data: 'transaction_id' },
                                            { data: 'amount' },
                                            { data: 'status' },
                                            { data: 'mode' },
                                        ]
                                    });

                                    $("input[type='search']").wrap("<form>");
                                    $("input[type='search']").closest("form").attr("autocomplete","off");
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal  show" id="addAccountModal" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Add Account</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <form method="post" action="" id="addAccountForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-4 form-error">
                            <label>Bank Name</label>
                            <select class="form-control required single_select2" name="bank_id"
                                title="The bank field is required">
                                <option value="" disabled selected hidden>-- Select Bank --</option>
                                @foreach ($aeps_banks as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 form-error">
                            <label>Account Number</label>
                            <input type="text" class="form-control required" name="account_no"
                                placeholder="Account Number" title="The account number field is required">
                        </div>
                        <div class="mb-4 form-error">
                            <label>IFSC</label>
                            <input type="text" class="form-control required" name="ifsc" placeholder="IFSC"
                                title="The ifsc field is required">
                        </div>
                        <div class="mb-4 form-error">
                            <label>Account Holder Name</label>
                            <input type="text" class="form-control required" name="account_holder_name"
                                placeholder="Account Holder Name" title="The account holder name field is required">
                        </div>
                        <div class="mb-4 form-error">
                            <label>Account Type</label>
                            <select class="form-control required single_select2" name="account_type"
                                title="The account type field is required">
                                <option value="" disabled selected hidden>-- Select Account Type --</option>
                                <option value="PRIMARY">PRIMARY</option>
                                <option value="RELAT2IVE">RELATIVE</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="addAccount()" id="saveBtn">Add
                            Now
                        </button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal  show" id="uploadDocumentModal" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Upload Document</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <form method="post" action="" id="uploadDocumentForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="bene_id" id="beneId">
                    <div class="modal-body">
                        <div class="mb-4 form-error">
                            <label>Document Type</label>
                            <select class="form-control required document_type_select2" name="document_type"
                                title="The document type field is required">
                                <option value="PAN">PAN</option>
                                <option value="AADHAAR">AADHAAR</option>
                            </select>
                        </div>
                        <div class="mb-4 form-error">
                            <label>Passbook</label>
                            <input type="file" class="form-control required" name="passbook"
                                title="Please upload passbook document">
                        </div>

                        <div class="mb-4 form-error panSection">
                            <label>PAN</label>
                            <input type="file" class="form-control required" name="pan"
                                title="Please upload passbook document">
                        </div>
                        <div class="mb-4 form-error aadharSection" style="display: none">
                            <label>Front Aadhar</label>
                            <input type="file" class="form-control required" name="front_aadhar"
                                title="Please upload aadhar front document">
                        </div>
                        <div class="mb-4 form-error aadharSection" style="display: none">
                            <label>Back Aadhar</label>
                            <input type="file" class="form-control required" name="back_aadhar"
                                title="Please upload aadhar back document">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="uploadDocument()">Upload
                        </button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('customScript')
    <script type="text/javascript">
        //updateBankMaster();
        updatePayoutAccount();

        $(document).ready(function() {


            $('#payoutLatitude').val($("#inputLatitude").val());
            $('#payoutLongitude').val($("#inputLongitude").val());


            $('#addAccountForm').validate({
                errorPlacement: function(error, element) {
                    $(element).parents('.form-error').append(error);
                },
            });

            $('#payoutRequestForm').validate({
                errorPlacement: function(error, element) {
                    $(element).parents('.form-error').append(error);
                },
            });

            $('#uploadDocumentForm').validate({
                errorPlacement: function(error, element) {
                    $(element).parents('.form-error').append(error);
                },
            });

            $("#bankdetail_id").select2();
            $("#payment_mode").select2();
            $(".single_select2").select2({
                width: "100%",
                dropdownParent: $("#addAccountModal")
            });

            $(".document_type_select2").select2({
                width: "100%",
                dropdownParent: $("#uploadDocumentModal")
            });

        });

        function amountToWords() {
            var a = ['', 'one ', 'two ', 'three ', 'four ', 'five ', 'six ', 'seven ', 'eight ', 'nine ', 'ten ', 'eleven ',
                'twelve ', 'thirteen ', 'fourteen ', 'fifteen ', 'sixteen ', 'seventeen ', 'eighteen ', 'nineteen '
            ];
            var b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

            var num = $("#amount").val();
            if ((num = num.toString()).length > 9) return 'overflow';
            n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
            if (!n) return;
            var str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'hundred ' : '';
            str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) +
                'only ' : '';
            $("#amountToWordsText").text(str);
        }

        function updateBankMaster() {
            /*   $.get("{{ url('agent/update-bank-master') }}", function (res) {
                   console.log(res)
               })*/
        }

        function updatePayoutAccount() {
            $.get("{{ url('agent/update-payout-account') }}", function(res) {
                console.log(res)
            })
        }

        $(document).on('click', '#addAccount', function() {
            $('label.error').hide();
            $('#addAccountForm').trigger("reset");
            $('.single_select2').val("").trigger("change");
            $('#addAccountModal').modal('show');
        });

        $(document).on('click', '.uploadDocument', function() {
            let bene_id = $(this).attr('data-id');
            $('label.error').hide();
            $('#uploadDocumentForm').trigger("reset");
            $('.single_select2').val("").trigger("change");
            $('#uploadDocumentModal').modal('show');
            $('#beneId').val(bene_id);
        });

        $(document).on('change', '.document_type_select2', function() {
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
                    url: "{{ url('agent/add-account') }}",
                    data: $('#addAccountForm').serialize(),
                    success: function(res) {
                        $(".loader").hide();
                        if (res.status === 'success') {
                            swal("Success", res.message, "success");
                            setTimeout(function() {
                                location.reload(1);
                            }, 3000);
                        } else {
                            swal("Failed", res.message, "error");
                        }
                    }
                });
            }
        }


        $(document).on('click', '.checkDocumentStatus', function() {
            let bene_id = $(this).attr('data-id');
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'bene_id=' + bene_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{ url('agent/account-status-check') }}",
                data: dataString,
                success: function(res) {
                    $(".loader").hide();
                    if (res.status === 'success') {
                        swal("Success", res.message, "success");
                    } else {
                        swal("Failed", res.message, "error");
                    }
                }
            });
        });

        function sendPayoutRequest() {
            if ($('#payoutRequestForm').valid()) {
                $(".loader").show();
                $.ajax({
                    type: "POST",
                    url: "{{ url('agent/send-payout-request') }}",
                    data: $('#payoutRequestForm').serialize(),
                    success: function(res) {
                        $(".loader").hide();
                        if (res.status === 'success') {
                            swal("Success", res.message, "success");
                            setTimeout(function() {
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
                    url: "{{ url('agent/upload-document') }}",
                    data: new FormData($('#uploadDocumentForm')[0]),
                    dataType: 'JSON',
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(res) {
                        $(".loader").hide();
                        if (res.status === 'success') {
                            swal("Success", res.message, "success");
                            setTimeout(function() {
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
