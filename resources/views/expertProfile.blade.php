
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <!-- This file has been downloaded from Bootsnipp.com. Enjoy! -->
    <title>Maya Expert Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset(mix("css/app.css")) }}">
    <style type="text/css">
        body {
            padding-top: 70px;
        }

        #pie_chart {
            position: absolute;
            top: 0;
            left: 0;
            width:100%;
            height:100%;
        }

        #chart_wrap {
            position: relative;
            padding-bottom: 100%;
            height: 0;
            overflow:hidden;
        }

        .btn-grey{
            background-color:#D8D8D8;
            color:#FFF;
        }
        .rating-block{
            background-color:#FAFAFA;
            border:1px solid #EFEFEF;
            padding:15px 15px 20px 15px;
            border-radius:3px;
        }
        .bold{
            font-weight:700;
        }
        .padding-bottom-7{
            padding-bottom:7px;
        }

        .review-block{
            background-color:#FAFAFA;
            border:1px solid #EFEFEF;
            padding:15px;
            border-radius:3px;
            margin-bottom:15px;
        }
        .review-block-name{
            font-size:12px;
            margin:10px 0;
        }
        .review-block-date{
            font-size:12px;
        }
        .review-block-rate{
            font-size:13px;
            margin-bottom:15px;
        }
        .review-block-title{
            font-size:15px;
            font-weight:700;
            margin-bottom:10px;
        }
        .review-block-description{
            font-size:13px;
        }
    </style>
    <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
</head>

<body class="container-fluid">

<!-- Fixed navbar -->

<div class="container">

    @if($expert)
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4">
                <div class="cardProfile-dr">

                    <div class="medic-box">
                        <span class="top-color-medic"></span>
                        <div class="text-center">
                            <div class="img-doctor text-center" style="background: url('{{ optional($expert->profilePicture)->url ?? 'https://images-maya.s3.ap-southeast-1.amazonaws.com/images/userprofile/'.'1548825893.png' }}') no-repeat center center;"></div>
                        </div>
                        <br>
                        <p style="text-align: center;font-family: monospace;">
                            @if(!empty($expert->specialistProfile->shadow_name))
                                <strong>{{ optional($expert->specialistProfile)->shadow_name }}</strong>
                            @else
                                <strong>Maya Expert</strong>
                            @endif
                        </p>
                        <p style="text-align: center;font-family: monospace;">
                            @if($ratings>= 3)
                                <strong>Ratings:</strong>
                                @if($ratings == 5)
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                @elseif($ratings == 4)
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star"></span>
                                @elseif($ratings == 3)
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                @elseif($ratings == 2)
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                @elseif($ratings == 1)
                                    <span class="fa fa-star checked"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                @endif
                            @endif
                        </p>
                        <br>
                        <div>
                            <p><strong>Qualification:</strong>
                                {{ optional($expert->specialistProfile)->qualification!=""?
                                optional($expert->specialistProfile)->qualification :"NA"}}
                            </p>
                            <hr>
                            <p><strong>Expert in:</strong>
                                {{ optional($expert->specialistProfile)->expertise !=""?
                                optional($expert->specialistProfile)->expertise : 'NA'}}
                            </p>
                            <hr>
                            <p><strong>Specialized in:</strong>
                                {{ optional($expert->specialistProfile)->speciality !="" ?
                                  optional($expert->specialistProfile)->speciality : 'NA'}}
                            </p>
                            <hr>
                            <p><strong>Quote:</strong>
                                {{ optional($expert->specialistProfile)->quote ?? 'NA'}}
                            </p>
                            <hr>
                            <p><b>{{ optional($expert->specialistProfile)->shadow_name ?? 'Maya Expert'}} has answered
                                    total {{ $totalQuestion }} questions</b></p>
                        </div>
                        <br>
                        <div id="chart_wrap">
                            <div id="pie_chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-7">
                <hr/>
                <div class="review-block">
                    <div class="row">
                        <div class="col-sm-3">
                            <img src="{{URL::to('/')}}/images/anonymous.png" class="img-rounded" style="width: 60px; height: 60px;">
                            <div class="review-block-name">Anonymous</div>
                            <div class="review-block-date">December 29, 2018</div>
                        </div>
                        <div class="col-sm-9">
                            <div class="review-block-rate">
                                <button type="button" class="btn btn-warning btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-warning btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-warning btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-grey btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-grey btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="review-block-title">খুবই সুন্দর একটি সেবা</div>
                            <div class="review-block-description">খুবই সুন্দর একটি সেবা, উনি খুব বিস্তারিতভাবে প্রতিটি বিষয় বুজিয়ে বলেন। খুব ভালো লাগলো উনার পরামর্শ পেয়ে। আপুকে অনেক অনেক ধন্যবাদ।</div>
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col-sm-3">
                            <img src="{{URL::to('/')}}/images/anonymous.png" class="img-rounded" style="width: 60px; height: 60px;">
                            <div class="review-block-name">Anonymous</div>
                            <div class="review-block-date">December 29, 2018</div>
                        </div>
                        <div class="col-sm-9">
                            <div class="review-block-rate">
                                <button type="button" class="btn btn-warning btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-warning btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-warning btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-grey btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-grey btn-xs" aria-label="Left Align">
                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="review-block-title">কোন রোগের সমাধানের পাশাপাশি সেই রোগ সম্পর্কে বিস্তারিতভাবে জানা জরুরী।</div>
                            <div class="review-block-description">কোন রোগের সমাধানের পাশাপাশি সেই রোগ সম্পর্কে বিস্তারিতভাবে জানা জরুরী। আপুকে অনেক অনেক ধন্যবাদ যে, উনি সমাধানের পাশাপাশি সমস্যা সম্পর্কে বিস্তারিতভাবে সব বুজিয়ে বলেন এবং কিভাবে সেই সমস্যা থেকে দূরে থাকা যায় টা বলে দেন। যা কিনা আমার কাছে খুব ভালো লেগেছে।</div>
                        </div>
                    </div>
                    <hr/>
                </div>
            </div>
        </div>
    @else
        <p class="error-dr">No Expert Profile Found!</p>
    @endif

    {{--<img class="responsive" src="https://i.ibb.co/5WmF70r/Dr-Profile.jpg" style="width:100%; height:auto" >--}}
    {{--<div class="row">--}}
        {{--<div class="col-sm-3">--}}
            {{--<div class="rating-block">--}}
                {{--<h4>Average user rating</h4>--}}
                {{--<h2 class="bold padding-bottom-7">4.3 <small>/ 5</small></h2>--}}
                {{--<button type="button" class="btn btn-warning btn-sm" aria-label="Left Align">--}}
                    {{--<span class="glyphicon glyphicon-star" aria-hidden="true"></span>--}}
                {{--</button>--}}
                {{--<button type="button" class="btn btn-warning btn-sm" aria-label="Left Align">--}}
                    {{--<span class="glyphicon glyphicon-star" aria-hidden="true"></span>--}}
                {{--</button>--}}
                {{--<button type="button" class="btn btn-warning btn-sm" aria-label="Left Align">--}}
                    {{--<span class="glyphicon glyphicon-star" aria-hidden="true"></span>--}}
                {{--</button>--}}
                {{--<button type="button" class="btn btn-default btn-grey btn-sm" aria-label="Left Align">--}}
                    {{--<span class="glyphicon glyphicon-star" aria-hidden="true"></span>--}}
                {{--</button>--}}
                {{--<button type="button" class="btn btn-default btn-grey btn-sm" aria-label="Left Align">--}}
                    {{--<span class="glyphicon glyphicon-star" aria-hidden="true"></span>--}}
                {{--</button>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--<div class="col-sm-3">--}}
            {{--<h4>Rating breakdown</h4>--}}
            {{--<div class="pull-left">--}}
                {{--<div class="pull-left" style="width:35px; line-height:1;">--}}
                    {{--<div style="height:9px; margin:5px 0;">5 <span class="glyphicon glyphicon-star"></span></div>--}}
                {{--</div>--}}
                {{--<div class="pull-left" style="width:180px;">--}}
                    {{--<div class="progress" style="height:9px; margin:8px 0;">--}}
                        {{--<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="5" style="width: 1000%">--}}
                            {{--<span class="sr-only">80% Complete (danger)</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="pull-right" style="margin-left:10px;">1</div>--}}
            {{--</div>--}}
            {{--<div class="pull-left">--}}
                {{--<div class="pull-left" style="width:35px; line-height:1;">--}}
                    {{--<div style="height:9px; margin:5px 0;">4 <span class="glyphicon glyphicon-star"></span></div>--}}
                {{--</div>--}}
                {{--<div class="pull-left" style="width:180px;">--}}
                    {{--<div class="progress" style="height:9px; margin:8px 0;">--}}
                        {{--<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="4" aria-valuemin="0" aria-valuemax="5" style="width: 80%">--}}
                            {{--<span class="sr-only">80% Complete (danger)</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="pull-right" style="margin-left:10px;">1</div>--}}
            {{--</div>--}}
            {{--<div class="pull-left">--}}
                {{--<div class="pull-left" style="width:35px; line-height:1;">--}}
                    {{--<div style="height:9px; margin:5px 0;">3 <span class="glyphicon glyphicon-star"></span></div>--}}
                {{--</div>--}}
                {{--<div class="pull-left" style="width:180px;">--}}
                    {{--<div class="progress" style="height:9px; margin:8px 0;">--}}
                        {{--<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="3" aria-valuemin="0" aria-valuemax="5" style="width: 60%">--}}
                            {{--<span class="sr-only">80% Complete (danger)</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="pull-right" style="margin-left:10px;">0</div>--}}
            {{--</div>--}}
            {{--<div class="pull-left">--}}
                {{--<div class="pull-left" style="width:35px; line-height:1;">--}}
                    {{--<div style="height:9px; margin:5px 0;">2 <span class="glyphicon glyphicon-star"></span></div>--}}
                {{--</div>--}}
                {{--<div class="pull-left" style="width:180px;">--}}
                    {{--<div class="progress" style="height:9px; margin:8px 0;">--}}
                        {{--<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="5" style="width: 40%">--}}
                            {{--<span class="sr-only">80% Complete (danger)</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="pull-right" style="margin-left:10px;">0</div>--}}
            {{--</div>--}}
            {{--<div class="pull-left">--}}
                {{--<div class="pull-left" style="width:35px; line-height:1;">--}}
                    {{--<div style="height:9px; margin:5px 0;">1 <span class="glyphicon glyphicon-star"></span></div>--}}
                {{--</div>--}}
                {{--<div class="pull-left" style="width:180px;">--}}
                    {{--<div class="progress" style="height:9px; margin:8px 0;">--}}
                        {{--<div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="5" style="width: 20%">--}}
                            {{--<span class="sr-only">80% Complete (danger)</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="pull-right" style="margin-left:10px;">0</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}

</div> <!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="assets/js/vendor/jquery.min.js"><\/script>')</script>
{{--<script src="js/bootstrap.min.js"></script>--}}
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
{{--<script src="js/ie10-viewport-bug-workaround.js"></script>--}}
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    // Load google charts
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    // Draw the chart and set the chart values
    function drawChart() {

            @if($expert)
            var data = google.visualization.arrayToDataTable([
                ['Task', 'Hours per Day'],
                    @foreach($charts as $chart)
                [ "{{$chart->name}}".trim(), {{(int)$chart->number}}],
                @endforeach
            ]);

            // Optional; add a title and set the width and height of the chart
            var options = {'title': 'My Answered Question',
                legend: {
                position: 'top',
                maxLines: 20}
                , 'width': '100%', 'height': '100%'};

            // Display the chart inside the <div> element with id="pie_chart"
            var chart = new google.visualization.PieChart(document.getElementById('pie_chart'));
            chart.draw(data, options);

            @else
                $('#pie_chart').append('Not Enough Data To Show Pie Chart')
            @endif

    }
</script>
</body>
</html>
