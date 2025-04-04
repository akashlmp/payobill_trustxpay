<div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content ">
        <div class="modal-header">
            <h6 class="modal-title">Voucher Details</h6>
            <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                    aria-hidden="true">×</span></button>
        </div>
        <div class="modal-body p-0">
            <div class="form-body">
                <table class="table table-bordered responsive">
                    <tbody>
                        <tr >
                            <td>Code</td>
                            <td>{{ $record['code']}}</td>
                        </tr>
                        <tr>
                            <td>Pin</td>
                            <td>{{ $record['pin'] ? $record['pin'] : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Validity Date</td>
                            <td>{{ $record['validity_date'] }}</td>
                        </tr>
                        <tr>
                            <td>Amount</td>
                            <td>{{ $record['amount'] ? $record['amount'] : '-' }}</td>
                        </tr>
                        @if($record->data)
                        <?php
                        $p = json_decode($record->data, true);
                        ?>
                        <tr>
                            <td>Category</td>
                            <td>{{ $p['categories'] ? $p['categories'] : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Product Name</td>
                            <td>{!! $p['productName'] !!}</td>
                        </tr>
                        <tr>
                            <td>Validity</td>
                            <td>{!! $p['validity'] ? $p['validity'] : '-' !!}</td>
                        </tr>
                        <tr>
                            <td>Min Value</td>
                            <td>{{ $p['minValue'] ? $p['minValue'] : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Max Value</td>
                            <td>{{ $p['maxValue'] ? $p['maxValue'] : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Usage Type</td>
                            <td>{{ $p['usageType'] }}</td>
                        </tr>
                        <tr>
                            <td>Delivery Type</td>
                            <td>{{ $p['deliveryType'] }}</td>
                        </tr>
                        <tr>
                            <td>Value Type</td>
                            <td>{{ $p['valueType'] }}</td>
                        </tr>
                        <tr>
                            <td>Denomination</td>
                            <td>{{ $p['denomination'] }}</td>
                        </tr>
                        <tr>
                            <td>Description</td>
                            <td>{!! $p['description'] !!}</td>
                        </tr>
                        <tr>
                            <td>How to Use</td>
                            <td>{!! $p['howToUse'] !!}</td>
                        </tr>
                        <tr>
                            <td>Tat In Days</td>
                            <td>{{ $p['tatInDays'] }}</td>
                        </tr>
                        <tr>
                            <td>Terms Condition</td>
                            <td>{!! $p['termsCondition'] !!}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
