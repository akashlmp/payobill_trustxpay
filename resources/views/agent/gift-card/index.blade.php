@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function() {
            // $('#get_voucher_model').modal('show')

            $('.clsGetVoucher').click(function(e) {
                var no = $(this).data('no');
                var denomination = $(this).data('denomination');
                var body = $(this).data('body');
                body = JSON.stringify(body);
                // console.log(JSON.stringify(body))
                $('#product_no').val(no);
                $('#product_data').val(body);
                $('#get_voucher_model').modal('show')
            });
            $('#idCheckBalance').click(function(e) {
                e.preventDefault();
                $(".loader").show();
                $.ajax({
                    type: "POST",
                    url: "{{ url('agent/gift-card/v1/check_balance') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(msg) {
                        $(".loader").hide();
                        if (msg.Status == 1) {
                            var text = "Your balance is " + msg.Balance;
                            swal("Success", text, "success");
                        } else {
                            swal("Failed", msg.Message, "error");
                        }
                    }
                });
            });

            $('#getVoucherForm').submit(function(e) {
                e.preventDefault();
                $(".loader").show().attr('style',"z-index : 99999 !important");
                $("#denomination_errors").text("");
                $("#quantity_errors").text("");
                $("#transaction_password_errors").text("");
                $('#idBodyAppend').html("");
                $.ajax({
                    type: "POST",
                    url: "{{ url('agent/gift-card/v1/get-vouchers') }}",
                    data: $('#getVoucherForm').serialize(),
                    dataType: "json",
                    success: function(msg) {
                        $(".loader").hide();
                        // alert(msg.Status)
                        if (msg.Status == '1') {
                            getWalletBal();
                            var Vouchers = msg.Vouchers;
                            var html = "";
                            $.each(Vouchers, function(index, data) {
                                html += "<tr><td>" + data.Code + "</td>";
                                html += "<td>" + data.Pin + "</td>";
                                html += "<td>" + data.Validity_Date + "</td>";
                                html += "<td>" + data.Amount + "</td></tr>";
                            });
                            $('#idBodyAppend').html(html);
                            $('#get_voucher_model').modal('hide');
                            $('#show_voucher_model').modal('show');
                            $('#getVoucherForm').trigger("reset");
                        } else if (msg.Status == 'validation_error') {
                            $("#denomination_errors").text(msg.errors.denomination);
                            $("#quantity_errors").text(msg.errors.quantity);
                            $("#transaction_password_errors").text(msg.errors.transaction_password);

                        } else {
                            swal("Failed", msg.Message, "error");
                        }
                    }
                });


            });

        });
    </script>

    <div class="main-content-body">

        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                            {{-- <button class="btn btn-primary btn-sm" id="idCheckBalance">Check Balance</button> --}}
                            <a href="{{url('agent/gift-card/v1/voucher-history')}}"  class="btn btn-primary btn-sm">Voucher History</a>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap display responsive" id="my_table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Product Image</th>
                                        <th class="wd-15p border-bottom-0">Get Vouchers</th>
                                        <th class="wd-15p border-bottom-0">Category</th>
                                        <th class="wd-15p border-bottom-0">Product Name</th>
                                        <th class="wd-15p border-bottom-0">Validity</th>
                                        <th class="wd-15p border-bottom-0">Min Value</th>
                                        <th class="wd-15p border-bottom-0">Max Value</th>
                                        <th class="wd-15p border-bottom-0">Usage Type</th>
                                        <th class="wd-15p border-bottom-0">Delivery Type</th>
                                        <th class="wd-15p border-bottom-0">Value Type</th>
                                        <th class="wd-15p border-bottom-0">Denomination</th>
                                        <th class="wd-15p border-bottom-0">Description</th>
                                        <th class="wd-15p border-bottom-0">How to Use: </th>
                                        <th class="wd-15p border-bottom-0">Tat In Days: </th>
                                        <th class="wd-15p border-bottom-0">Terms Condition: </th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $p)
                                        <tr>
                                            <td><img src="{{ $p['productImage'] }}" height="50px" alt="Product Image" />
                                            </td>
                                            <td style="width: 100px !important">
                                                <a href="javascript:;" class="btn btn-primary btn-sm clsGetVoucher"
                                                    data-denomination="{{ $p['denomination'] }}"
                                                    data-no="{{ $p['productNo'] }}" data-body="{{json_encode($p)}}">Get Vouchers</a>
                                            </td>
                                            <td>{{ $p['categories'] ? $p['categories'] : '-' }}</td>
                                            <td>{!! $p['productName'] !!}</td>
                                            <td>{!! $p['validity'] ? $p['validity'] : '-' !!}</td>
                                            <td>{{ $p['minValue'] ? $p['minValue'] : '-' }}</td>
                                            <td>{{ $p['maxValue'] ? $p['maxValue'] : '-' }}</td>
                                            <td>{{ $p['usageType'] }}</td>
                                            <td>{{ $p['deliveryType'] }}</td>
                                            <td>{{ $p['valueType'] }}</td>
                                            <td>{{ $p['denomination'] }}</td>
                                            <td>{!! $p['description'] !!}</td>
                                            <td>{!! $p['howToUse'] !!}</td>
                                            <td>{{ $p['tatInDays'] }}</td>
                                            <td>{!! $p['termsCondition'] !!}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <script type="text/javascript">
                                $(document).ready(function() {
                                    $('#my_table').DataTable();
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>
    </div>
    </div>
    </div>

    <div class="modal  show" id="get_voucher_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <form action="" id="getVoucherForm">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title">Get Voucher</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <input type="hidden" id="product_no" name="product_no">
                            <input type="hidden" id="product_data" name="product_data">

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Denomination Value:</label>
                                        <input type="text" id="denomination" name="denomination" class="form-control"
                                            placeholder="Ex. 50">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="denomination_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Quantity:</label>
                                        <input type="text" id="quantity" name="quantity" class="form-control"
                                            placeholder="Ex. 1">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="quantity_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Your Transaction Password</label>
                                        <input type="password" name="transaction_password" id="transaction_password" class="form-control"
                                               placeholder="Transaction Password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="transaction_password_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                <p class="text-danger">Note: Make sure when you submit, the amount will be deducted from your wallet balance.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn ripple btn-primary">Submit</button>
                        <a href="javascript:;" class="btn ripple btn-secondary" aria-label="Close" class="close"
                            data-dismiss="modal">Close</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal  show" id="show_voucher_model" data-toggle="modal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Voucher List
                    </h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table text-md-nowrap display responsive">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Code</th>
                                    <th class="wd-15p border-bottom-0">Pin</th>
                                    <th class="wd-15p border-bottom-0">Validity Date</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="idBodyAppend">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="javascript:;" class="btn ripple btn-secondary" aria-label="Close" class="close"
                        data-dismiss="modal">Close</a>
                </div>
            </div>
        </div>
    </div>
@endsection
