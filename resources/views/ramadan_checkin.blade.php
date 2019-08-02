<html>

<head>

    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet" />
    <style>

        .outer-box {
            /*background-color: #66512c;*/
            /* The image used */
            background-image: url("https://cdn-images-1.medium.com/max/1600/1*_kOjjFFIGq-G8x1YhdzKWg.jpeg");

            /* Add the blur effect */
            filter: blur(8px);
            -webkit-filter: blur(8px);

            /* Full height */
            height: 100%;

            /* Center and scale the image nicely */
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .box {
            background-color: #2d003a;
            width: 350px;
            height: auto;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }

        .day {
            background-color: white;
            width: 24px;
            height: 24px;
            border-radius: 15px;
            text-align: center;
            margin: 4px;
            padding: 3px;
        }

        .stepper {
            background-color: #e2c80fcc;
            display: inline-block;
            width: 325px;
            padding-top: 28px;
            padding-bottom: 15px;
            /* position: absolute; */
            margin-bottom: 30px;
            height: auto;
            margin-top: 28px;
        }

        .container {
            width: 600px;
            margin: 100px auto;
        }
        .progressbar {
            counter-reset: step;
        }
        .progressbar li {
            list-style-type: none;
            width: 20%;
            float: left;
            font-size: 12px;
            position: relative;
            text-align: center;
            text-transform: uppercase;
            color: #7d7d7d;
            z-index: 999999;
        }
        .progressbar li:before {
            width: 30px;
            height: 30px;
            /*content: counter(step);*/
            content: attr(data-content);
            counter-increment: step;
            line-height: 30px;
            border: 2px solid #ffffff;
            display: block;
            text-align: center;
            margin: 0 auto 10px auto;
            border-radius: 50%;
            background-color: white;
            color: #928a8a;
            font-weight: bold;
            font-size: 16px;
        }
        .progressbar li:after {
            width: 100%;
            height: 2px;
            content: '';
            position: absolute;
            background-color: #dcd8d8;
            top: 15px;
            left: -27%;
            z-index: -1;
        }
        .progressbar li:first-child:after {
            content: none;
        }
        .progressbar li.active {
            color: green;
        }
        .progressbar li.active:before {
            border-color: #2d003a;
            background-color: #14071f;
            color: white;
            font-weight: bolder;
            font-size: large;
        }
        .progressbar li.active + li:after {
            background-color: #2d003a;
        }

        dl, ol, ul {
            /* margin-top: -60px; */
            /* margin-bottom: 1rem; */
            margin-left: -50px;
        }

        .day_active {
            background-color: #ffc800ed;
            color: white;
        }

    </style>
</head>

<body>

    {{--{{dd($check_ins[0])}}--}}

    <div class="outer-box">

    </div>

    <div class="box">
        <div class="inner-box">
            <div style="text-align: center;color: white">
                <h3 style="font-weight: bold;">অভিনন্দন</h3>
            </div>
            <div style="text-align: center;color: white">
                <h5 style="border-bottom: 1px solid #ecc812eb;
    width: 120px;
    margin: 0 auto;margin-bottom: 10px;
    margin-top: 10px;font-weight: bold;">আজকের টিপস</h5>

                <p class="col-10" style="margin: 0 auto;">{{$tips}}</p>
            </div>

            <div class="row">
                <div class="col-10 center" style="background-color: #fbffff;
    height: 90px;
    float: none;
    margin: 0 auto;
    margin-top: 30px;
    margin-bottom: 30px;
    border-radius: 10px;
    box-shadow: 5px 5px #0b1011;
    padding: 5px;">
                    <div style="color: #401C48;">আপনি মোট চেক ইন দিয়েছেন</div>
                    <div style="color: #401C48;font-size: 31px;font-weight: bold;">{{$total_checkin}}</div>
                    <div style="color: #401C48;">দিন</div>
                    {{--রোজার সময়ে আমরা অনেকেই কম ঘুমাই যার জন্যে দেখা যায় অনেকেই অসুস্থ হয়ে পড়ি। সুস্থ থাকতে রোজার সময়েও পর্যাপ্ত ঘুমানো উচিত।--}}
                    {{--<div id="check_in" style="background-color: #37D1C5;--}}
             {{--border-radius: 10px;--}}
             {{--box-shadow: 1px 1px 5px #0b1011;--}}
             {{--width: 100px;--}}
             {{--height: 30px;--}}
             {{--color: white; float: none; margin: 0 auto; margin-top: 25px;--}}
             {{--display: flex;--}}
             {{--flex-direction: column;--}}
             {{--justify-content: center;font-weight: bold;--}}
             {{--position: absolute;--}}
             {{--left: 100px;--}}
             {{--bottom: 9px;">--}}
                        {{--CHECK IN--}}
                    {{--</div>--}}
                </div>
            </div>

            <div class="row">
                <div style="margin: 0 auto;
    font-size: 20px;
    color: white;">প্রতি ৫ দিন চেক ইনে আকর্ষণীয় পুরষ্কার</div>
            </div>

            <div style="margin-left: 10px;
    margin-top: 25px;">
                <div class="col-10 center" style="
        /*height: 100px; */
    float: none;
    margin: 0 auto;
    /* margin-top: 30px; */
    /* margin-bottom: 30px; */
    color: #51cbce;
    font-size: 13px;
    font-weight: bold;
         ">চেক ইন স্ট্যাটাস</div>
                <div>
                    <div style="color: #ffd277;    text-align: center;">রহমত</div>
                    <div style="display: flex;    margin-top: 10px;
    margin-bottom: 10px;">
                        <div class="day {{$check_ins[0][7] == true ? 'day_active' : ''}}">1</div>
                        <div class="day {{$check_ins[0][8] == true ? 'day_active' : ''}}">2</div>
                        <div class="day {{$check_ins[0][9] == true ? 'day_active' : ''}}">3</div>
                        <div class="day {{$check_ins[0][10] == true ? 'day_active' : ''}}">4</div>
                        <div class="day {{$check_ins[0][11] == true ? 'day_active' : ''}}">5</div>
                        <div class="day {{$check_ins[0][12] == true ? 'day_active' : ''}}">6</div>
                        <div class="day {{$check_ins[0][13] == true ? 'day_active' : ''}}">7</div>
                        <div class="day {{$check_ins[0][14] == true ? 'day_active' : ''}}">8</div>
                        <div class="day {{$check_ins[0][15] == true ? 'day_active' : ''}}">9</div>
                        <div class="day {{$check_ins[0][16] == true ? 'day_active' : ''}}">10</div>
                    </div>

                </div>
                <div>
                    <div style="color: #ffd277;    text-align: center;">মাগফিরাত</div>
                    <div style="display: flex;    margin-top: 10px;
    margin-bottom: 10px;">
                        <div class="day {{$check_ins[1][17] == true ? 'day_active' : ''}}">1</div>
                        <div class="day {{$check_ins[1][18] == true ? 'day_active' : ''}}">2</div>
                        <div class="day {{$check_ins[1][19] == true ? 'day_active' : ''}}">3</div>
                        <div class="day {{$check_ins[1][20] == true ? 'day_active' : ''}}">4</div>
                        <div class="day {{$check_ins[1][21] == true ? 'day_active' : ''}}">5</div>
                        <div class="day {{$check_ins[1][22] == true ? 'day_active' : ''}}">6</div>
                        <div class="day {{$check_ins[1][23] == true ? 'day_active' : ''}}">7</div>
                        <div class="day {{$check_ins[1][24] == true ? 'day_active' : ''}}">8</div>
                        <div class="day {{$check_ins[1][25] == true ? 'day_active' : ''}}">9</div>
                        <div class="day {{$check_ins[1][26] == true ? 'day_active' : ''}}">10</div>
                    </div>
                </div>
                <div>
                    <div style="color: #ffd277;text-align: center;">নাজাত</div>
                    <div style="display: flex;
                        margin-top: 10px;
                        margin-bottom: 10px;">
                        <div class="day {{$check_ins[2][27] == true ? 'day_active' : ''}}">1</div>
                        <div class="day {{$check_ins[2][28] == true ? 'day_active' : ''}}">2</div>
                        <div class="day {{$check_ins[2][29] == true ? 'day_active' : ''}}">3</div>
                        <div class="day {{$check_ins[2][30] == true ? 'day_active' : ''}}">4</div>
                        <div class="day {{$check_ins[2][31] == true ? 'day_active' : ''}}">5</div>
                        <div class="day {{$check_ins[2][1] == true ? 'day_active' : ''}}">6</div>
                        <div class="day {{$check_ins[2][2] == true ? 'day_active' : ''}}">7</div>
                        <div class="day {{$check_ins[2][3] == true ? 'day_active' : ''}}">8</div>
                        <div class="day {{$check_ins[2][4] == true ? 'day_active' : ''}}">9</div>
                        <div class="day {{$check_ins[2][5] == true ? 'day_active' : ''}}">10</div>
                    </div>
                </div>

                {{--<div class="stepper">--}}
                    {{--<div style="display: flex">--}}
                        {{--<div>--}}
                            {{--<div style="color: #1b6d85; height: 5px" ></div>--}}
                            {{--<div class="day">1</div>--}}
                        {{--</div>--}}

                        {{--<div class="day">2</div>--}}
                        {{--<div class="day">3</div>--}}
                        {{--<div class="day">4</div>--}}
                        {{--<div class="day">5</div>--}}
                        {{--<div class="day">6</div>--}}
                        {{--<div class="day">7</div>--}}
                        {{--<div class="day">8</div>--}}
                        {{--<div class="day">9</div>--}}
                        {{--<div class="day">10</div>--}}
                    {{--</div>--}}

                    {{--<ul class="progressbar">--}}
                        {{--<li class="active" data-content="3"></li>--}}
                        {{--<li class="active" data-content="4"></li>--}}
                        {{--<li data-content="3"></li>--}}
                        {{--<li data-content="3"></li>--}}
                        {{--<li data-content="3"></li>--}}
                    {{--</ul>--}}

                {{--</div>--}}
            </div>

        </div>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script>

    var i = [1,2,3,4,5]
    var content = []

    i.forEach(function (item) {

        if(item%2 === 0)
            content.push(`<li  data-content='${item}'></li>`)
        else
            content.push(`<li class='active' data-content='${item}'></li>`)
    })


   // $('.stepper').append(`
   //     <ul class="progressbar">
   //                     ${content.join("")}
   //                 </ul>
   // `)
</script>
</body>
</html>