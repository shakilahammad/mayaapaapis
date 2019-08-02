@extends('payment.bkash.bkash-layout')

@section('bkashContent')

    <div class="jumbotron">
        <img src="{{ asset('images/maya_apa_plus.png') }}" class="img-responsive" alt="Maya apa plus">
        <h2>পেমেন্ট হয়নি!</h2>
        <p>
            আপনি অলরেডি মায়া আপা প্লাসে সাবস্ক্রাইব করেছেন। যেকোনো সমস্যাই কল করুন মায়া আপা কাস্টমার কেয়ার <b>+০১৮৮৪৫৫২৩৭০</b> নাম্বারে।
        </p>

        <img src="{{ asset('images/error.png') }}" class="img-responsive center" style="width: 19%;margin: 0 auto;" alt="success">
    </div>

@endsection

