<script type="text/javascript">
    function getTopupPlan(provider, mobile_number) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider=' + provider + '&_token=' + token + "&mobile_number=" + mobile_number;
        $.ajax({
            type: "POST",
            url: "{{ url('agent/recharge/v1/get-plans') }}",
            data: dataString,
            dataType: 'json',
            success: function(data) {
                $(".loader").hide();
                $('#tablesContainer').html("");
                $('#myTab').html("");
                if (data.Status == 0) {
                    const plansByCategory = data.Plans.reduce((acc, plan) => {
                        if (!acc[plan.plan_category_name]) {
                            acc[plan.plan_category_name] = [];
                        }
                        acc[plan.plan_category_name].push(plan);
                        return acc;
                    }, {});
                    // Create tables for each category
                    var i = 0;
                    $.each(plansByCategory, function(categoryName, plans) {
                        createTable(categoryName, plans, i);
                        i++;
                        $("#prepaid_plan_model").modal('show');
                    });
                } else {
                    swal("Failed", data.FailureReason, "error");
                }


            }
        });
    }

    function pickamt(id) {
        $("#prepaid_plan_model").modal('hide');
        $("#amount").val(id);
    }

    function showHidePlanLink() {
        var provider_id = $('#provider_id').val();
        var provider_text = $("#provider_id option:selected").text();
        provider_text = provider_text.trim();
        var is_post_paid = $('#is_post_paid').val();
        if (provider_id != '' && is_post_paid == "N" && (provider_text == 'Airtel' || provider_text == 'Vi')) {
            $('#idShowPlans').show();
        } else {
            $('#idShowPlans').hide();
        }
    }
    $(document).ready(function() {

        $('#provider_id').change(function() {
            showHidePlanLink();
        });
        $('#is_post_paid').change(function() {
            showHidePlanLink();
        });

        $('#idShowPlans').click(function() {
            var provider_id = $('#provider_id').val();
            var provider_text = $("#provider_id option:selected").text();
            provider_text = provider_text.trim();
            var is_post_paid = $('#is_post_paid').val();
            var mobile_number = $('#mobile_number').val();
            if (provider_id != '' && is_post_paid == "N" && (provider_text == 'Airtel' ||
                    provider_text == 'Vi')) {
                getTopupPlan(provider_text, mobile_number);
            }
        });


        // $("#myTab a").click(function(e) {
        //     e.preventDefault();
        //     $(this).tab("show");
        // });
    });

    function createTable(categoryName, plans, i) {
        var slug = createSlug(categoryName);
        var active = "active";
        var show = "show active";
        if (i > 0) {
            active = "";
            show = "";
        }
        var head = '<li class="nav-item">';
        head += '<a id="pills-home-tab' + i + '" data-toggle="pill" data-target="#pills-home' + i +
            '" type="button" role="tab" aria-controls="pills-home' + i + '"  class="nav-link ' + active + '">' +
            categoryName + '</a>';
        head += '</li>';
        $('#myTab').append(head);
        var html = '<div class="tab-pane fade ' + show + '" id="pills-home' + i +
            '" role="tabpanel" aria-labelledby="pills-home-tab' + i + '">';
        html += '<table class="table table-bordered mb-0">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Talktime</th>';
        html += '<th>Amount</th>';
        html += '<th>Plan Category Name</th>';
        html += '<th>Description</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        $.each(plans, function(index, plan) {
            html += "<tr><td>" + plan.talktime + "</td>";
            html +=
                "<td><span class='btn btn-primary btn-sm' style='width:80px;cursor:pointer;' onclick='pickamt(" +
                plan.amount + ")'>Rs. " + plan.amount + " </span></td>";
            html += "<td>" + plan.plan_category_name + "</td>";
            html += "<td>" + plan.plan_description + "</td></tr>";
        });
        html += '<tbody></table></div>';
        $('#tablesContainer').append(html);
        // console.log(table)
    }

    function createSlug(text) {
        return text
            .toString() // Convert to string
            .toLowerCase() // Convert to lowercase
            .trim() // Trim whitespace from both ends
            .replace(/[\s\W-]+/g, '-') // Replace spaces and non-alphanumeric characters with hyphens
            .replace(/^-+|-+$/g, ''); // Remove leading and trailing hyphens
    }
</script>

<div class="modal  show" id="prepaid_plan_model" data-toggle="modal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Browse Plans</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">

                {{-- Start Tab --}}
                <div class="">
                    <ul class="nav nav-pills" id="myTab">

                    </ul>
                    <hr>
                    <div class="tab-content" id="tablesContainer">

                    </div>
                </div>
                {{-- End Tab --}}


            </div>
        </div>
    </div>
</div>
