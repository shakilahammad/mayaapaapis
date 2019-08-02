<!DOCTYPE html>
<html>
<head>
    <title>Maya Prescription</title>

    {{--<meta name="viewport" content="width=device-width, initial-scale=1.0"/>--}}
    <meta name="description" content="maya prescription"/>
    <meta charset="UTF-8">

    {{--<link type="text/css" rel="stylesheet" href="style.css">--}}
    <link href='https://fonts.googleapis.com/css?family=Rokkitt:400,700|Lato:400,300' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,300,700' rel='stylesheet' type='text/css'>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet" />

    {{-- template style start --}}

    <style>


        #footer-prescription{
            position: absolute;
            bottom: 0;
            height: 60px;
            background: #6cf;
        }

        .footer-prescription {
            /*position: absolute;*/
            /*bottom: 0;*/
            width: 100%;
            height: 60px;
            background-color: #21b6bc;
            color: #ffffff;
        }

        .header-prescription {
            margin-top: 20px;
            height: 210px;
            border-bottom: 5px solid #21b6bc;
            margin-top: 10px;
        }

        .maincontent-prescription{
            height: 1000px;
        }

        .prescription-left{
            border-right: 2px solid #21b6bc;
            display: flex;
            flex-direction: column;
        }

        p{
            font-family: 'Lato Regular';
            color: #2f2d2de3;
        }
    </style>

    {{-- template style end--}}
</head>
<body style="width: 1000px;
    margin-right: 50px;
    margin-left: 58px;">

    {{--{{dd($data->drugs)}}--}}
    <div
            {{--class="container"--}}
    >
            <div class="row header-prescription">
                <div class="col-5" style="align-self: center;">
                    <img src="{{asset('/images/Maya-logo-HORIZ-3-RGB.jpg')}}" style="width: 200px">
                    <p style="padding-top: 10px;">Your digital well-being Assistant</p>
                </div>
                <div class="col-7" style="    display: flex;
    align-self: flex-end; justify-content: space-between;">
                    <div></div>
                    <p><b>Patient Name: </b>{{$data->name ?? ""}}</p>
                    <p><b>Age: </b>{{$data->age ? $data->age ." yrs" : ""}} </p>
                    <p><b>Date: </b>{{$data->created_at->format('d-m-Y') ?? ""}}</p>
                </div>
            </div>

            <div class="row maincontent-prescription">
                <div class="col-4 prescription-left">
                    <div style="flex: 0.5;margin-top: 50px;">
                        <b>Patient complaints:</b>
                        <p style="white-space: pre-wrap;">{{$data->complain ?? ""}}</p>
                    </div>
                    <div style="flex: 0.5;">
                        <b>Investigation:</b>
                        @isset($data->investigations)
                        @foreach($data->investigations as $investigation)
                        <p>{{$investigation['name'] ?? ""}}</p><span>&nbsp;&nbsp;{{$investigation['instruction'] ?? ""}}</span>
                        @endforeach
                        @endisset
                    </div>

                </div>
                <div class="" style="display: flex;
                flex-direction: column;">
                    <div class="row" style="flex: 0.8;
    margin-top: 50px;
    margin-left: 50px;">
                        <img src="{{asset('/images/RX.png')}}" style="height: 30px;">
                        <div style="    margin-top: 40px;
    margin-left: -20px;">
                            <ol style="font-weight: 500; color: black">
                                @isset($data->drugs)
                                @foreach($data->drugs as $drug)
                                <li style="margin-bottom: 15px;">{{$drug['name'] ?? ""}}<br><span>{{ $drug['dose'] ?? ""}}</span><span style="font-weight: lighter">&nbsp;&nbsp; {{ $drug['duration'] ?? ""}}</span></li>
                                @endforeach
                                @endisset
                                {{--<li style="margin-bottom: 15px;">sdsd - 1tbsp<br><span>1 + 1 + 0</span><span style="font-weight: lighter">&nbsp;&nbsp; 30 days</span></li>--}}
                                {{--<li style="margin-bottom: 15px;">sdsd - 2.5mg<br><span>1 + 1 + 0</span><span style="font-weight: lighter">&nbsp;&nbsp; 30 days</span></li>--}}
                                {{--<li style="margin-bottom: 15px;">sdsd - 2.5mg<br><span>1 + 1 + 0</span><span style="font-weight: lighter">&nbsp;&nbsp; 30 days</span></li>--}}
                            </ol>
                        </div>
                    </div>
                    <div class="row" style="flex: 0.2;margin-left: 20px;white-space: pre-wrap;width: 400px;text-align: left;
    margin-bottom: 100px;">
                        <b>Adv:</b>
                        <p style="margin-left: 25px;"><br>{{$data->advice?? ""}}</p>
                    </div>
                </div>
            </div>
        <div>

        </div>
    </div>

    <footer class="footer-prescription">
        <div class="container">
            <div class="row" style="padding-top: 20px;
    padding-bottom: 20px;
">
                <div class="col-6">
                    <img src="{{asset('/images/followuson_prescription.png')}}" style="width: 200px;">
                </div>
                <div class="col-6" style="
    text-align: right;">maya.com.bd</div>
            </div>
        </div>
    </footer>

</body>
</html>