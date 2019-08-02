<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Merchant</title>
    <meta name="viewport" content="width=device-width" initial-scale="1.0/">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrom=1">

    <link rel="stylesheet" href="{{ asset('bkash-assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bkash-assets/css/style.css') }}">

    <script src="{{ asset('bkash-assets/js/jquery-1.8.3.min.js') }}"></script>

    @if(config('app.env') === 'production')
       <script src="https://scripts.pay.bka.sh/versions/1.0.000-beta/checkout/bKash-checkout.js"></script>
    @else
       <script src="https://scripts.sandbox.bka.sh/versions/1.0.0-beta/checkout/bKash-checkout-sandbox.js"></script>
    @endif


    <script src="file:///android_asset/www/js/jquery-1.8.3.min.js"></script>
    <script src="https://scripts.pay.bka.sh/bKash-checkout.js"></script>

</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            @yield('bkashContent')
        </div>
    </div>
</div>

@stack('scripts')

</body>
</html>
