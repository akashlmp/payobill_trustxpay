@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
       
      
    </script>


<div class="main-content-body">
    <div class="row row-sm">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-2 mt-2">Api Commission</h4>
                        
                        <i class="mdi mdi-dots-horizontal text-gray"></i>
                    </div>
                    <hr>
                </div>
                <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Provider id</th>
                                    <th class="wd-25p border-bottom-0">Provider Name</th>
                                    <th class="wd-25p border-bottom-0">Service</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($providers as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->provider_name }}</td>
                                        <td>{{ $value->service->service_name }}</td>
                                        <td>
                                            
                                            <form method="POST" action="{{ url('admin/setup-api-commission-master') }}" class="pull-right">
                                                @csrf
                                                <input type="hidden" name="provider_id" value="{{ $value->id }}">
                                                <input type="hidden" name="service_id" value="{{ $value->service_id }}">
                                                <button type="submit" class="btn btn-danger btn-sm">Update Commission</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
        <!--/div-->

    </div>

</div>
</div>
</div>



{{--Add new api Model--}}

<div class="modal  show" id="add_new_api_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Api</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Api Name</label>
                                <input type="text" id="api_name" class="form-control" placeholder="Api Name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="api_name_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Api Method</label>
                                <select class="form-control" id="method">
                                    <option value="1">Get</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="method_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Response Type</label>
                                <select class="form-control" id="response_type">
                                    <option value="1">Json</option>
                                    <option value="2">XML</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="response_type_errors"></li>
                                </ul>

                            </div>
                        </div>



                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Api URL</label>
                                <textarea type="text" id="base_url" class="form-control" placeholder="Api URL" rows="4"></textarea>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="base_url_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th class="wd-40p">Attribute</th>
                                <th class="wd-60p">Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>number</td>
                                <td>[number]</td>
                            </tr>

                            <tr>
                                <td>amount</td>
                                <td>[amount]</td>
                            </tr>

                            <tr>
                                <td>operator_code</td>
                                <td>[opcode]</td>
                            </tr>

                            <tr>
                                <td>uniq_id</td>
                                <td>[txnid]</td>
                            </tr>

                            <tr>
                                <td>optional1</td>
                                <td>[optional1]</td>
                            </tr>

                            <tr>
                                <td>optional2</td>
                                <td>[optional2]</td>
                            </tr>
                            </tbody>
                        </table>

                    </div>



                </div>
            </div>

            <pre>Api URL Exmple : {{url('')}}/api/telecom/v1/payment?api_token=xyz&number=[number]&amount=[amount]&provider_id=[opcode]&client_id=[txnid]&optional1=[optional1]&optional2=[optional2]</pre>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="create_btn" onclick="create_new_api()">Create Now</button>
                <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
{{--Add new api Model close--}}

{{--update model--}}

<div class="modal  show" id="view_api_update_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> Update Api</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>

            <input type="hidden" id="view_id">
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Api Name</label>
                                <input type="text" id="view_api_name" class="form-control" placeholder="Api Name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_api_name_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Support Number</label>
                                <input type="text" id="view_support_number" class="form-control" placeholder="Support Number">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_support_number_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Api Method</label>
                                <select class="form-control" id="view_method">
                                    <option value="1">Get</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_method_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Response Type</label>
                                <select class="form-control" id="view_response_type">
                                    <option value="1">Json</option>
                                    <option value="2">XML</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_response_type_errors"></li>
                                </ul>

                            </div>
                        </div>



                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Api URL</label>
                                <textarea type="text" id="view_base_url" class="form-control" placeholder="Api URL" rows="4"></textarea>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_base_url_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Speed Status</label>
                                <select class="form-control" id="view_speed_status">
                                    <option value="0">Disable</option>
                                    <option value="1">Enable</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_speed_status_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Speed Limit</label>
                                <input type="number" class="form-control" id="view_speed_limit" placeholder="Speed Limit">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_speed_limit_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th class="wd-40p">Attribute</th>
                                <th class="wd-60p">Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>number</td>
                                <td>[number]</td>
                            </tr>

                            <tr>
                                <td>amount</td>
                                <td>[amount]</td>
                            </tr>

                            <tr>
                                <td>operator_code</td>
                                <td>[opcode]</td>
                            </tr>

                            <tr>
                                <td>uniq_id</td>
                                <td>[txnid]</td>
                            </tr>

                            <tr>
                                <td>optional1</td>
                                <td>[optional1]</td>
                            </tr>

                            <tr>
                                <td>optional2</td>
                                <td>[optional2]</td>
                            </tr>
                            </tbody>
                        </table>

                    </div>



                </div>
            </div>

            <pre>Api URL Exmple : {{url('')}}/api/telecom/v1/payment?api_token=xyz&number=[number]&amount=[amount]&provider_id=[opcode]&client_id=[txnid]&optional1=[optional1]&optional2=[optional2]</pre>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="update_btn" onclick="update_api()">Update Now</button>
                <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>




    {{--check balance api setting--}}
<div class="modal  show" id="view_check_balance_api_model" data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> Check Balance Api Setting</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>

            <input type="hidden" id="check_balance_id">
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Api Method</label>
                                <select class="form-control" id="check_balance_method">
                                    <option value="1">Get</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_method_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Response Type</label>
                                <select class="form-control" id="check_balance_response_type">
                                    <option value="1">Json</option>
                                    <option value="2">XML</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_response_type_errors"></li>
                                </ul>

                            </div>
                        </div>



                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Api URL</label>
                                <textarea type="text" id="check_balance_base_url" class="form-control" placeholder="Api URL" rows="4"></textarea>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_base_url_errors"></li>
                                </ul>
                            </div>
                        </div>



                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Status Available</label>
                                <select class="form-control" id="check_balance_status_type">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_status_type_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Status Parameter Name</label>
                                <input type="text" class="form-control" id="check_balance_status_parameter" placeholder="Status Parameter">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_status_parameter_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Success Value</label>
                                <input type="text" class="form-control" id="check_balance_status_value" placeholder="Status Value">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_status_value_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Balance Parameter Name</label>
                                <input type="text" class="form-control" id="check_balance_balance_parameter" placeholder="Status Parameter">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_balance_parameter_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Status</label>
                                <select class="form-control" id="check_balance_status_id">
                                    <option value="1">Enable</option>
                                    <option value="0">Disable</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_status_id_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Under value</label>
                                <input type="text" class="form-control" id="check_balance_under_value" placeholder="Under value">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="check_balance_under_value_errors"></li>
                                    <li class="parsley-required">If the data is inside a parameter</li>
                                </ul>
                            </div>
                        </div>




                    </div>



                </div>
            </div>


            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="check_balance_update_btn" onclick="update_check_balance_api()">Update Now</button>
                <button class="btn btn-primary" type="button"  id="check_balance_update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
{{--End check balance api setting--}}

    <div class="modal  show" id="view-api-credentials" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Api Credentials</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="credentials_api_id">
                            <div id="formContainer" class="row">
                                <!-- Input form will be generated here -->
                            </div>


                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="credentialsBtn" onclick="updateCredentials()">Update Credentials</button>
                    <button class="btn btn-primary" type="button"  id="credentialsBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>


@endsection