
<nav class="navbar navbar-toggleable-md fixed-top navbar-transparent" color-on-scroll="500">
    <div class="container">
        <div class="navbar-translate">
            <button class="navbar-toggler navbar-toggler-right navbar-burger" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-bar"></span>
                <span class="navbar-toggler-bar"></span>
                <span class="navbar-toggler-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/') }}"><img id="brand-trigger" src="{{ asset('images/logo-main.png') }}" width="120px"></a>
        </div>

        <div class="collapse navbar-collapse" id="navbarToggler">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" rel="tooltip" title="Like us on Facebook" data-placement="bottom" href="https://www.facebook.com/MayaApaApp" target="_blank">
                        <i class="fa fa-facebook-square"></i>
                        <p class="hidden-lg-up">Facebook</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

