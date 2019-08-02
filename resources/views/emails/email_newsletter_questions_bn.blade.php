<style type="text/css">
    @import url(//fonts.googleapis.com/earlyaccess/notosansbengali.css);
</style>
<?php //print_r($questions[0]); ?>
{{--@foreach($questions as $key => $question)--}}
    {{--<p>--{{ $question->answeredBy }}</p>--}}
{{--@endforeach--}}
<link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,700" rel="stylesheet">
<div style="margin:0 auto;padding:0;max-width:600px;width:90%;color:#4e4e4e;font-size:16px;font-weight:normal;background:#f4f4f4;color:#333; font-family: 'Noto Sans Bengali', sans-serif;">
    <img style="max-width: 100%; display:block; height: auto;" src="https://i.ibb.co/P9BXFtR/jouno-jibon-banner.jpg" />
    {{--<div style='width: 90%; background: url("https://i.ibb.co/P9BXFtR/jouno-jibon-banner.jpg") no-repeat; height: 13%; padding: 16% 5%'>--}}
        {{--<p style="font-size: 35px; line-height: 40px; text-align: center; padding: 0; margin: 0; color: #ffffff;">মায়া আপাতে</p>--}}
        {{--<p style="font-size: 30px; line-height: 30px; text-align: center; padding: 0; margin: 0; color: #ffffff;">এ সপ্তাহের সেরা প্রশ্ন </p>--}}
    {{--</div>--}}
    <!-- Main div  -->
    <div style="width: 90%; padding: 5%">
        <div style="float: left; max-width: 50%; text-align: center; padding-top: 2%">
            <p>ডাক্তারের প্রেস্ক্রিপশন এবং পরামর্শ পেতে এখনি Maya অ্যাপটি ডাউনলোড করে নিন।</p>
        </div>
        <div style="max-width: 30%; float: right">
            <a target="_blank" href="https://mx9wm.app.goo.gl/news_letter_sex_education">
                <img style="max-width: 100%; display:block; height: auto;" src="https://i.ibb.co/QMYgxxX/download-button.png">
            </a>
        </div>
    </div>
    {{--<img src="/img/newsletter/questions-header.png" width="600px" alt="Get Expert Advice, When You Need It.">--}}
    <div style="width: 90%; padding: 5%; overflow: hidden;">
        @foreach($questions as $key => $question)
            <div style="width: 96%; padding: 2%; background: #ffffff; border-radius: 5px; overflow: hidden; box-shadow: 2px 2px lightgrey;">
                <div style="width: 100%; overflow: hidden;">
                    <p style="width: 25%; float: left; text-align: left; margin: 0; color: #1e98ff;">প্রশ্নঃ </p>
                    <p style="width: 20%; float: right; text-align: right; margin: 0; font-size: 14px; color: #9b9b9b;">&nbsp;</p>
                </div>
                <p style="margin: 0; width: 100%; display: block; line-height: 20px;">{!! $question['body'] !!}</p>
                <div style="width: 100%; overflow: hidden; padding-top: 10px;">
                    <div style="width: 80%; float: right">
                        <div style="width: 100%;">
                            <p style="width: 100%; display: block; text-align: left; margin: 0; font-size: 14px; color: #50a8ff;">উত্তর করেছেন, {{ $question['answeredBy'] }}</p>
                            {{--<p style="width: 20%; float: right; text-align: right; margin: 0; font-size: 14px; color: #9b9b9b;">&nbsp;</p>--}}
                        </div>
                        <div style="width: 100%; padding-top: 2%; display: block">
                            <p style="width: 100%; margin: 0;">{!! $question['answer_body'] !!} .. <a href="http://maya.com.bd/question/{{ $question['id'] }}">Read More</a> </p>
                        </div>
                    </div>
                    <div style="width: 20%;">
                        <img src="https://i.ibb.co/3cFKBXs/launcher-icon2.png" style="width: 32px">
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{--<div style="width: 100%; padding-bottom: 30px;">--}}
        {{--<p style="color: #ff005e; text-align: center; font-size: 23px; font-weight: bold; margin: 0;">মায়া আপাকে প্রশ্ন করুন </p>--}}
        {{--<p style="color: #5ea4f1; text-align: center; font-size: 17px; margin: 0; width: 40%; height: 80px; float: left;">--}}
            {{--<a href="https://goo.gl/RfYM5W">--}}
                {{--<img src="https://image.ibb.co/ntRdGT/Maya_Email_Newsletter_Questions_App_Icon.png" style="display: block; margin: 0 44% 8px;">--}}
            {{--</a>--}}
            {{--<a href="https://goo.gl/RfYM5W" style="text-decoration: none; color: #000000;">--}}
                {{--<span>App</span>--}}
            {{--</a>--}}
        {{--</p>--}}
        {{--<p style="color: #5ea4f1; text-align: center; font-size: 17px; margin: 0; width: 20%; height: 80px; float: left;">--}}
            {{--<a href="https://goo.gl/58KCdS">--}}
                {{--<img src="https://image.ibb.co/feZhbT/Maya_Email_Newsletter_Questions_Web_Icon.png" style="display: block; margin: 0 31% 13px;">--}}
            {{--</a>--}}
            {{--<a href="https://goo.gl/58KCdS" style="text-decoration: none; color: #000000;">--}}
                {{--<span>Web</span>--}}
            {{--</a>--}}
        {{--</p>--}}
        {{--<p  style="color: #5ea4f1; text-align: center; font-size: 17px; margin: 0; width: 40%; height: 80px; float: left;">--}}
            {{--<a href="https://goo.gl/oCzTgm">--}}
                {{--<img src="https://image.ibb.co/jG2eO8/Maya_Email_Newsletter_Questions_Fbs_Icon.png" style="display: block; margin: 0 44%;">--}}
            {{--</a>--}}
            {{--<a href="https://goo.gl/oCzTgm" style="text-decoration: none; color: #000000;">--}}
                {{--<span>Free basics</span>--}}
            {{--</a>--}}
        {{--</p>--}}
        {{--<p style="color: #ff005e; text-align: center; font-size: 23px; font-weight: bold;">পাশে আছি সব সময়, মায়া আপা</p>--}}
    {{--</div>--}}
</div>