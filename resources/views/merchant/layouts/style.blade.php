<style>

    .horizontalMenucontainer .main-header.hor-header {
        position: fixed;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}}) !important; }

    .main-content:after {
        content: "";
        height: 220px;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}});
        position: absolute;
        z-index: -1;
        width: 100%;
        top: 0;
        left: 0; }

    .main-sidebar-body .nav-item:hover .nav-link {
        border-radius: 0 100px 100px 0;
        box-shadow: 0 6px 14px 2px rgba(0, 0, 0, 0.2);
        margin-right: 14px;
        color: #fff;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}});
        box-shadow: 0 6px 14px 2px rgba(0, 0, 0, 0.2); }

    .main-sidebar-body .nav-item.active .nav-link {
        color: #fff;
        font-weight: 500;
        border-top: 0;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}});
        border-radius: 0 6px 6px 0;
        box-shadow: 0 6px 14px 2px rgba(0, 0, 0, 0.2); }

    .main-sidebar-body .nav-item.active .nav-link {
        color: #fff;
        font-weight: 500;
        border-top: 0;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}});
        border-radius: 0 6px 6px 0;
        box-shadow: 0 6px 14px 2px rgba(0, 0, 0, 0.2); }

    .sticky-pin .horizontalMenucontainer .main-header.hor-header {
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}}); }

</style>

