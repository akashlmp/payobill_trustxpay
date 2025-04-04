@extends('agent.layout.header')
@section('content')


    <script type="text/javascript">
        function generateUrl() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var mobile_number = $("#mobile_number").val();
                var dataString = 'mobile_number=' + mobile_number + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/airtel-cms/v1/generate-url')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            window.open(msg.data.redirectionUrl, "_blank");
                        } else {
                            swal("Faild", msg.message, "error");
                        }
                    }
                });
            }
        }
    </script>

    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-4 col-md-12">


                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>


                        <div class="mb-4">
                            <label>Mobile Number</label>
                            <input type="text" class="form-control" placeholder="Mobile Number" id="mobile_number">
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="generateUrl()">Generate Url</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>

                </div>
            </div>




        </div>


    </div>
    </div>
    </div>



@endsection