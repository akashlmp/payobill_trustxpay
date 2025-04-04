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
                                        <th class="wd-10p border-bottom-0">Phone Number</th>
                                        <th class="wd-10p border-bottom-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accounts as $value)
                                        <tr>
                                            <td>{{ $value->bank_name }}</td>
                                            <td>{{ $value->name }}</td>
                                            <td>{{ $value->account_no }}</td>
                                            <td>{{ $value->ifsc }}</td>
                                            <td>{{ $value->bene_phone_number }}</td>
                                            <td><a href="javascript:;" class="btn btn-info btn-sm mb-1 clsEditAccount"
                                                    data-id="{{ $value->id }}" data-name="{{ $value->name }}"
                                                    data-account_no="{{ $value->account_no }}"
                                                    data-ifsc="{{ $value->ifsc }}"
                                                    data-bene_phone_number="{{ $value->bene_phone_number }}"
                                                    data-bank_name="{{ $value->bank_name }}">Edit</a>
                                                <a href="javascript:;" class="btn btn-danger btn-sm clsDeleteAccount"
                                                    data-id="{{ $value->id }}">Delete</a>
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
                    <input type="hidden" name="id" id="idPrimary">
                    <div class="modal-body">
                        <div class="mb-4 form-error">
                            <label>Bank Name</label>
                            <select class="form-control required single_select2" name="bank_name" id="bank_name"
                                title="The bank field is required">
                                <option value="" disabled selected hidden>-- Select Bank --</option>
                                @foreach ($aeps_banks as $id => $name)
                                    <option>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 form-error">
                            <label>Account Number</label>
                            <input type="text" class="form-control required" name="account_no" id="account_no"
                                placeholder="Account Number" title="The account number field is required">
                        </div>
                        <div class="mb-4 form-error">
                            <label>IFSC</label>
                            <input type="text" class="form-control required" name="ifsc" id="ifsc"
                                placeholder="IFSC" title="The ifsc field is required">
                        </div>
                        <div class="mb-4 form-error">
                            <label>Account Holder Name</label>
                            <input type="text" class="form-control required" name="account_holder_name"
                                id="account_holder_name" placeholder="Account Holder Name"
                                title="The account holder name field is required">
                        </div>
                        <div class="mb-4 form-error">
                            <label>Account Phone Number</label>
                            <input type="text" class="form-control required onlyNumber" minlength="10" maxlength="10" name="bene_phone_number"
                                id="bene_phone_number" placeholder="Phone Number" data-min-required="dfdf"
                                data-msg-required="The phone number field is required">
                        </div>
                        <input type="hidden" name="account_type" value="PRIMARY" />
                        {{-- <div class="mb-4 form-error">
                            <label>Account Type</label>
                            <select class="form-control required single_select2" name="account_type"
                                title="The account type field is required">
                                <option value="" disabled selected hidden>-- Select Account Type --</option>
                                <option value="PRIMARY">PRIMARY</option>
                                <option value="RELAT2IVE">RELATIVE</option>
                            </select>
                        </div> --}}
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
@endsection
@section('customScript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.onlyNumber').on('input', function (event) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
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


            $("#bankdetail_id").select2();
            $("#payment_mode").select2();
            $(".single_select2").select2({
                width: "100%",
                dropdownParent: $("#addAccountModal")
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

        $(document).on('click', '.clsDeleteAccount', function() {
            var id = $(this).data('id');
            swal({
                    title: "Are you sure?",
                    text: 'you want to delete this account ',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn btn-danger",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $(".loader").show();
                        var token = $("input[name=_token]").val();
                        var dataString = 'id=' + id + '&_token=' + token;
                        $.ajax({
                            type: "POST",
                            url: "{{ url('agent/delete-account-iserveu') }}",
                            data: dataString,
                            success: function(msg) {
                                $(".loader").hide();
                                if (msg.status == 'success') {
                                    swalSuccessReload(msg.message);
                                } else {
                                    swal("Faild", msg.message, "error");
                                }
                            }
                        });
                    }
                }
            );
        });

        $(document).on('click', '.clsEditAccount', function() {
            var id = $(this).data('id');
            var account_no = $(this).data('account_no');
            var name = $(this).data('name');
            var bank_name = $(this).data('bank_name');
            var ifsc = $(this).data('ifsc');
            var bene_phone_number = $(this).data('bene_phone_number');
            $('#addAccountForm').trigger("reset");
            $('.single_select2').val("").trigger("change");
            $('#ifsc').val(ifsc);
            $('#bank_name').val(bank_name).trigger("change");
            $('#bene_phone_number').val(bene_phone_number);
            $('#account_holder_name').val(name);
            $('#account_no').val(account_no);
            $('#idPrimary').val(id);
            $('#addAccountModal').modal('show');
        });

        $(document).on('click', '#addAccount', function() {
            $('label.error').hide();
            $('#addAccountForm').trigger("reset");
            $('.single_select2').val("").trigger("change");
            $('#addAccountModal').modal('show');
        });

        function addAccount() {
            if ($('#addAccountForm').valid()) {
                $(".loader").show();
                $.ajax({
                    type: "POST",
                    url: "{{ url('agent/add-account-iserveu') }}",
                    data: $('#addAccountForm').serialize(),
                    success: function(res) {
                        $(".loader").hide();
                        if (res.status === 'success') {
                            $('#addAccountModal').modal('hide');
                            swalSuccessReload(res.message);
                        } else {
                            swal("Failed", res.message, "error");
                        }
                    }
                });
            }
        }

        function sendPayoutRequest() {
            $('#payoutLatitude').val($("#inputLatitude").val());
            $('#payoutLongitude').val($("#inputLongitude").val());
            if ($('#payoutRequestForm').valid()) {
                $(".loader").show();
                $.ajax({
                    type: "POST",
                    url: "{{ url('agent/send-payout-request-iserveu') }}",
                    data: $('#payoutRequestForm').serialize(),
                    success: function(res) {
                        $(".loader").hide();
                        if (res.status === 'success') {
                            swalSuccessReload(res.message);
                        } else {
                            swal("Failed", res.message, "error");
                        }
                    }
                });
            }
        }
    </script>
@endsection
