@extends('merchant.layouts.main')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#payment_date").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });

            $("#bankdetail_id").select2();
            $("#paymentmethod_id").select2();
        });
        function addWhitelist() {
            $(".loader").show();
            $.ajax({
                type: "GET",
                url: "{{url('merchant/white-label-add')}}",
                success: function (response) {
                    $(".loader").hide();
                    $("#whiteLabelModal").html(response);
                    $("#whiteLabelModal").modal('show');
                }
            });
        }

        function editBankDetails(id) {
            $(".loader").show();
            $.ajax({
                type: "GET",
                url: "{{url('merchant/white-label-edit')}}" + '/' + id,
                success: function (response) {
                    $(".loader").hide();
                    $("#whiteLabelModal").html(response);
                    $("#whiteLabelModal").modal('show');
                }
            });
        }

        function deleteBankDetails(id) {
            $(".loader").show();
            $.ajax({
                type: "GET",
                url: "{{url('merchant/white-label-delete')}}" + '/' + id,
                success: function (response) {
                    $(".loader").hide();
                    $("#whiteLabelModal").html(response);
                    $("#whiteLabelModal").modal('show');
                }
            });
        }
    </script>

    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Bank Details</h6>
                            <hr>
                        </div>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Bank Name</th>
                                    <th class="wd-25p border-bottom-0">Account Name</th>
                                    <th class="wd-25p border-bottom-0">Account Number</th>
                                    <th class="wd-25p border-bottom-0">IFsc code</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{ $bank_name }}</td>
                                    <td>{{ $bank_account_name }}</td>
                                    <td>{{ $usersData->account_number }}</td>
                                    <td>{{ $bank_ifsc }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">White list bank Details</h6>
                            <button type="button" onclick="addWhitelist()" class="btn ripple btn-primary">Add bank
                            </button>
                            <hr>
                        </div>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="">Bank Name</th>
                                    <th class="">Payee Name</th>
                                    <th class="">Account Number</th>
                                    <th class="">IFsc code</th>
                                    <th class="">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($whiteListBanks)>0)
                                    @foreach($whiteListBanks as $value)
                                        <tr>
                                            <td>{{ $value->bank_name }}</td>
                                            <td>{{ $value->payee_name }}</td>
                                            <td>{{ $value->account_number }}</td>
                                            <td>{{ $value->ifsc_code }}</td>
                                            <td>
                                                @if($value->status == 0)
                                                    <a class="btn btn-sm btn-success text-white"
                                                       onclick="editBankDetails({{ $value->id }})"><i
                                                            class="fas fa-edit"></i>Edit</a>
                                                @else
                                                    <span class="badge badge-{{($value->status ==1)?'success':'danger'}}">{{($value->status ==1)?'Approved':'Rejected'}}</span>
                                                @endif
                                                <a class="btn btn-sm btn-danger"
                                                   onclick="deleteBankDetails({{ $value->id }})">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="5">No Data Available.</td>
                                    </tr>
                                @endif
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
                            <h6 class="card-title mb-1">Payment Request</h6>
                            <hr>
                        </div>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example2" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Id</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Request Date</th>
                                    <th class="wd-15p border-bottom-0">Payment Date</th>
                                    <th class="wd-15p border-bottom-0">Bank</th>
                                    <th class="wd-15p border-bottom-0">Method</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">UTR</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($loadcash as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td><span
                                                class="{{ $value->status->class }}">{{ $value->status->status }}</span>
                                        </td>
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{ $value->payment_date }}</td>
                                        <td>{{ $value->bankdetail->bank_name }}</td>
                                        <td>{{ $value->paymentmethod->payment_type }}</td>
                                        <td>{{ number_format($value->amount,2)}}</td>
                                        <td>{{ $value->bankref }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>


                    </div>
                </div>
            </div>

        </div>
        <div class="modal fade" id="whiteLabelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2"
             aria-hidden="true">

        </div>
    </div>
    </div>
    </div>
@endsection
