@php
    $currentRouteName = Route::currentRouteName();
@endphp
<div id="docs-sidebar" class="docs-sidebar">
    <nav id="docs-nav" class="docs-nav navbar">
        <ul class="section-items list-unstyled nav flex-column pb-3">
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.paymentApi' ? 'active' : '' }} " href="{{ route('documentation.paymentApi') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Payout API
                </a>
            </li>
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.statusApi' ? 'active' : '' }}" href="{{ route('documentation.statusApi') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Status API
                </a>
            </li>
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.webhooks' ? 'active' : '' }}" href="{{ route('documentation.webhooks') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Webhooks
                </a>
            </li>
        </ul>
    </nav>
</div>