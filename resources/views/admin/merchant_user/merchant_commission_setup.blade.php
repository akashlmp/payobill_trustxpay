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

                            <h4 class="card-title mg-b-2 mt-2">Merchant Commission Setup</h4>
                            <a href="{{url()->previous()}}">Back</a>
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
                                            
                                            <form method="POST" action="{{ url('admin/merchant-set-operator-commission') }}" class="pull-right">
                                                @csrf
                                                <input type="hidden" name="merchant_id" value="{{ $merchant_id }}">
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


@endsection
