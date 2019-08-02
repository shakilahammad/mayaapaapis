<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet" />

<style>
    body {
        background-color: #2d003a;
    }

    .timer {
        font-size: 21px;
        font-weight: bold;
        opacity: 0.67;
        color: #FFFFFF;
        font-family: 'Montserrat';
    }

    .timer_title {
        font-size: 5px;
        font-weight: lighter;
        vertical-align: top;
        font-family: 'Montserrat';
    }
</style>
</head>

<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-2">
            {{--<div style="background-color: #3ea1ec; height: 300px"></div>--}}
            <img src="{{asset('images/ramadan-light-left.png')}}" style="width: 300%;
  max-width: 400px;
  height: auto;">
        </div>
        <div class="col-8">

            <div class="center" style=" height: 100px; margin-top: 30px">
                <div style="height: 80px;
    width: 80px;
    background-color: #EBB24B;
    border-radius: 66px;
    margin: 0 auto;
color: #311336;">
                    <div style="padding-top: 8px;
    font-size: 22px;
    font-weight: bold;
    font-family: 'Montserrat';">{{$daily_ramadan->day}}<sub>তম</sub></div>
                        <p>রমজান</p>


                </div>

                <div style="color: white;
    font-weight: 500;
    font-size: 18px;margin-top: 10px;font-family: 'Montserrat';">{{ \Carbon\Carbon::now()->format('d M Y')}}</div>
            </div>
            <div class="center" style="margin-top: 30px">
                <div style="font-family: 'Montserrat';
    color: white;
    opacity: 0.67;
    ">ঢাকা ও পার্শ্ববর্তী এলাকা</div>
                <div style="color: white;
    font-size: 13px;font-family: 'Montserrat';">আজকের ইফতার শুরু: <b class="iftar_time" style="color: #b17f0f;
    font-size: 18px;">{{\Carbon\Carbon::parse($daily_ramadan->iftar_time )->format('h:i A')}}</b> </div>
                <div style="font-size: 13px;
    color: white;font-family: 'Montserrat';">সাহরির শেষ সময়: <b class="sehri_time" style="color: #b17f0f;
    font-size: 18px;">{{\Carbon\Carbon::parse($daily_ramadan->sehri_time )->format('h:i A')}}</b></div>
            </div>
            <div class="center" style="height: 100px; margin-top: 30px">
                <div style="color: white;
    font-size: 13px;font-family: 'Montserrat';">ইফতারের সময় বাকি</div>
                <div style="display: flex;
    justify-content: space-around;">
                    <div class="timer hour">00<span class="timer_title"><br>HOURS</span></div>
                    <div class="timer min">00<span class="timer_title"><br>MINUTES</span></div>
                    <div class="timer sec">00<span class="timer_title"><br>SECONDS</span></div>
                </div>
            </div>
        </div>

        <div class="col-2">
            {{--<div style="background-color: #3ea1ec; height: 300px"></div>--}}
            <img src="{{asset('images/ramadan-light-right.png')}}" style="width: 100%;
    max-width: 400px;
    height: auto;
    margin-top: 50px;
    /* margin-right: 19px; */
    position: absolute;
    right: 0;">
        </div>
    </div>


    <div class="row">
        <div class="col-10 center" style="background-color: #fbffff;
        height: 100px;
         float: none;
         margin: 0 auto;
         margin-top: 30px;
         margin-bottom: 30px;
         border-radius: 10px; box-shadow: 5px 5px #0b1011">
            <div style="color: #401C48;padding: 10px;">চেক ইন দিয়ে দেখে নিন প্রতিদিনের এক্সক্লুসিভ <b style="font-size: 15px;">টিপস</b> ও জিতে নিন আকর্ষণীয় <b style="font-size: 15px;">পুরস্কার</b></div>
            {{--রোজার সময়ে আমরা অনেকেই কম ঘুমাই যার জন্যে দেখা যায় অনেকেই অসুস্থ হয়ে পড়ি। সুস্থ থাকতে রোজার সময়েও পর্যাপ্ত ঘুমানো উচিত।--}}
            <div id="check_in" style="background-color: #37D1C5;
             border-radius: 10px;
             box-shadow: 1px 1px 5px #0b1011;
             width: 100px;
             height: 30px;
             color: white; float: none; margin: 0 auto; margin-top: 25px;
             display: flex;
             flex-direction: column;
             justify-content: center;font-weight: bold;
             position: absolute;
             left: 100px;
             bottom: 9px;">
                CHECK IN
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 center-block text-center">
            <img src="{{asset('images/ramadan-light-middle.png')}}" style="width: 100%;
    height: 100%;max-height: 200px;">
        </div>
    </div>
</div>

{{--<input type="button" value="check in" id="check_in"/>--}}

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script type="text/javascript">

    var user_data = {!! json_encode($data) !!}

    var day = {!!json_encode( \Carbon\Carbon::now()->day)  !!}
    var date = {!! json_encode( \Carbon\Carbon::now()->format('d M Y') )!!}

    var iftar_time = {!! json_encode( \Carbon\Carbon::parse($daily_ramadan->iftar_time )->format('h:i A') ) !!}
    var sehri_time


    console.log({!! json_encode($data) !!})

    $('#check_in').on('click', function (event) {
        // alert(event);
        // console.log(event)

        // $.ajax({
        //     url: 'http://127.0.0.1:8001/api/v1/ramadan_checkin/auth',
        //     beforeSend: function(xhr) {
        //         xhr.setRequestHeader("access-token", "EMAWcV1AswZBgr9ZBAnjjVZANZCkPvC8c9sapRJPzWm90IyBWa7P17WWsfXLsoaiGL4q108FUUceAMwC5lGFm6CV2N1te2g059zkoTCjb8y9wZBpFUBKXyuQSLvEZCG7KzLWWg8nQnI3mXbLHldBh7K0TDHMLUQegk4ZD")
        //     }, success: function(data){
        //         alert(data);
        //         //process the JSON data etc
        //     }
        // })

        // http://127.0.0.1:8001/
        //https://maya-apa.com/mayaapi/maya/daily_ramadan
        fetch('https://maya-apa.com/mayaapi/ramadan_checkin/auth?user_id='+ user_data['user_id'] +'&device_id=' + user_data['device_id'], {
            headers: { "Content-Type": "application/json; charset=utf-8" ,
                "device_id" : user_data['device_id'],
                "user_id" : user_data['user_id'],
                "access-token" : user_data['access_token']
            },
            method: 'GET'
        })
            .then(response => response.json())
            .then( data => {

                if(data.status === 'success')
                    window.location.href='https://maya-apa.com/mayaapi/maya/ramadan_checkins/' + user_data['user_id']
                else
                    window.location.href='https://maya.com.bd/auth/login'
            })
            .catch(error => console.log(error))


    })
    
    function setIftarTime(str) {
        $('.iftar_time').html(str)
    }
    
    function setSehriTime(str) {
        $('.sehri_time').html(str)
    }

    function setHour(str) {
        $('.hour').html(str + '<span class="timer_title"><br>HOURS</span>')
    }

    function setMin(str) {
        $('.min').html(str + '<span class="timer_title"><br>MINUTES</span>')
    }

    function setSec(str) {
        $('.sec').html(str + '<span class="timer_title"><br>SECONDS</span>')
    }

    function tConvert (time) {
        // Check correct time format and split into components
        time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

        if (time.length > 1) { // If time format correct
            time = time.slice (1);  // Remove full string match value
            time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
            time[0] = +time[0] % 12 || 12; // Adjust hours
        }
        return time.join (''); // return adjusted time or original string
    }


    // console.log(response.data[day-1])
    // console.log(response.data[day-1].timings.Maghrib.split("("))
    // iftar_time = tConvert( response.data[day-1].timings.Maghrib.split("(")[0].trim() )
    // sehri_time = tConvert(response.data[day-1].timings.Fajr.split("(")[0].trim() )

    // console.log(tConvert(iftar_time))
    // console.log(tConvert(sehri_time))

    // console.log(new Date( date + " " + iftar_time))
    // setIftarTime(iftar_time)
    // setIftarTime('6:34 PM')
    // setSehriTime(sehri_time)
    // setSehriTime('3:51 AM')

    var countDownDate = new Date(date + " " + iftar_time).getTime();
    // console.log(date + " " + iftar_time)
    // Update the count down every 1 second
    var x = setInterval(function() {

        // Get todays date and time
        var now = new Date().getTime();

        // Find the distance between now and the count down date
        var distance = countDownDate - now;

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        setHour(hours)
        setMin(minutes)
        setSec(seconds)
        // console.log(minutes + " " + seconds)
        // Display the result in the element with id="demo"
        // document.getElementById("demo").innerHTML = days + "d " + hours + "h "
        //     + minutes + "m " + seconds + "s ";

        // console.log(days + "d " + hours + "h "
        //     + minutes + "m " + seconds + "s ")

        // If the count down is finished, write some text
        if (distance < 0) {
            clearInterval(x);
            setHour('00')
            setMin('00')
            setSec('00')
        }
    }, 1000);



    // fetch('https://api.aladhan.com/v1/calendar?latitude=23.77010000&longitude=90.36280000&method=1&month=5&year=2019')
    // .then( response => response.json() )
    // .then( response => {
    //     // console.log(date)
    //
    //     // console.log(response.data[day-1])
    //     // console.log(response.data[day-1].timings.Maghrib.split("("))
    //     iftar_time = tConvert( response.data[day-1].timings.Maghrib.split("(")[0].trim() )
    //     sehri_time = tConvert(response.data[day-1].timings.Fajr.split("(")[0].trim() )
    //
    //     // console.log(tConvert(iftar_time))
    //     // console.log(tConvert(sehri_time))
    //
    //     // console.log(new Date( date + " " + iftar_time))
    //     // setIftarTime(iftar_time)
    //     // setIftarTime('6:34 PM')
    //     // setSehriTime(sehri_time)
    //     // setSehriTime('3:51 AM')
    //
    //     var countDownDate = new Date(date + " " + iftar_time).getTime();
    //     // console.log(date + " " + iftar_time)
    //     // Update the count down every 1 second
    //     var x = setInterval(function() {
    //
    //         // Get todays date and time
    //         var now = new Date().getTime();
    //
    //         // Find the distance between now and the count down date
    //         var distance = countDownDate - now;
    //
    //         // Time calculations for days, hours, minutes and seconds
    //         var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    //         var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    //         var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    //         var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    //
    //         setHour(hours)
    //         setMin(minutes)
    //         setSec(seconds)
    //         // console.log(minutes + " " + seconds)
    //         // Display the result in the element with id="demo"
    //         // document.getElementById("demo").innerHTML = days + "d " + hours + "h "
    //         //     + minutes + "m " + seconds + "s ";
    //
    //         // console.log(days + "d " + hours + "h "
    //         //     + minutes + "m " + seconds + "s ")
    //
    //         // If the count down is finished, write some text
    //         if (distance < 0) {
    //             clearInterval(x);
    //             setHour('00')
    //             setMin('00')
    //             setSec('00')
    //         }
    //     }, 1000);
    //
    //
    //
    // })
    // .catch( err => console.log(err))


    // Set the date we're counting down to



</script>

</body>
</html>