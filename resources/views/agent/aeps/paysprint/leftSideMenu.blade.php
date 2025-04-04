
<div class="col-lg-4 col-xl-3">
    <div class="card mg-b-20 compose-mail">
        <div class="card-header pb-0">
            <div class="d-flex justify-content-between">
                <h4 class="card-title mg-b-2 mt-2">Service List</h4>
                <i class="mdi mdi-dots-horizontal text-gray"></i>
            </div>
            <hr>
        </div>
        <div class="main-content-left main-content-left-mail card-body">
            <div class="main-mail-menu">
                <nav class="nav main-nav-column mg-b-20">
                    @if(Auth::User()->role_id == 8)
                    <a class="nav-link {{(Request::is('agent/aeps/v2/two-factor-authentication') ? 'active' : '')}}" href="{{url('agent/aeps/v2/two-factor-authentication')}}">Agent Authenticate</a>
                    <a class="nav-link {{(Request::is('agent/aeps/v2/welcome') ? 'active' : '')}}" href="{{url('agent/aeps/v2/welcome')}}">AEPS Transaction</a>
                    <a class="nav-link {{(Request::is('agent/aeps/v2/agent-onboarding') ? 'active' : '')}}" href="{{url('agent/aeps/v2/agent-onboarding')}}">Agent Onboarding</a>
                    @endif
                </nav>
            </div><!-- main-mail-menu -->
        </div>
    </div>
</div>

