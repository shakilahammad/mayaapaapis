@extends('payment.bkash.bkash-layout')

@section('bkashContent')
    <div class="jumbotron">
        <img src="{{ asset('images/maya_apa_plus.png') }}" class="img-responsive" alt="Maya apa plus">
        <h2>পেমেন্ট হয়নি!</h2>
        <p>
            পেমেন্ট সংক্রান্ত যেকোনো সমস্যাই কল করুন মায়া আপার কাস্টমার কেয়ার <b>+০১৮৮৪৫৫২৩৭০</b> নাম্বারে।
        </p>

        @if(!empty($errorCode) && $errorCode !== "null" && !empty($errorMessage) && $errorMessage !== "null")
            <code style="overflow-wrap: break-word;">
                ErrorCode: {{ $errorCode }}<br>
                ErrorMessage: {{ $errorMessage }}
            </code>
            <br>
            <br>
            <div class="clear"></div>
        @endif

        <img src="{{ asset('images/error.png') }}" class="img-responsive center" style="width: 19%;margin: 0 auto;" alt="success">
    </div>
@endsection

