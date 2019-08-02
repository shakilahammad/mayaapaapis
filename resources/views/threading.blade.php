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

{{--<input type="button" value="check in" id="check_in"/>--}}

<script type="text/javascript">

    {{--var user_data = {!! json_encode($data) !!}--}}

    {{--var day = {!!json_encode( \Carbon\Carbon::now()->day)  !!}--}}
    {{--var date = {!! json_encode( \Carbon\Carbon::now()->format('d M Y') )!!}--}}

    {{--var iftar_time = {!! json_encode( \Carbon\Carbon::parse($daily_ramadan->iftar_time )->format('h:i A') ) !!}--}}
    {{--var sehri_time--}}


    {{--console.log({!! json_encode($data) !!})--}}

    {{--$('#check_in').on('click', function (event) {--}}
        {{--// alert(event);--}}
        {{--// console.log(event)--}}

        {{--// $.ajax({--}}
        {{--//     url: 'http://127.0.0.1:8001/api/v1/ramadan_checkin/auth',--}}
        {{--//     beforeSend: function(xhr) {--}}
        {{--//         xhr.setRequestHeader("access-token", "EMAWcV1AswZBgr9ZBAnjjVZANZCkPvC8c9sapRJPzWm90IyBWa7P17WWsfXLsoaiGL4q108FUUceAMwC5lGFm6CV2N1te2g059zkoTCjb8y9wZBpFUBKXyuQSLvEZCG7KzLWWg8nQnI3mXbLHldBh7K0TDHMLUQegk4ZD")--}}
        {{--//     }, success: function(data){--}}
        {{--//         alert(data);--}}
        {{--//         //process the JSON data etc--}}
        {{--//     }--}}
        {{--// })--}}

        {{--// http://127.0.0.1:8001/--}}
        {{--//https://maya-apa.com/mayaapi/maya/daily_ramadan--}}
        {{--fetch('https://maya-apa.com/mayaapi/ramadan_checkin/auth?user_id='+ user_data['user_id'] +'&device_id=' + user_data['device_id'], {--}}
            {{--headers: { "Content-Type": "application/json; charset=utf-8" ,--}}
                {{--"device_id" : user_data['device_id'],--}}
                {{--"user_id" : user_data['user_id'],--}}
                {{--"access-token" : user_data['access_token']--}}
            {{--},--}}
            {{--method: 'GET'--}}
        {{--})--}}
            {{--.then(response => response.json())--}}
            {{--.then( data => {--}}

                {{--if(data.status === 'success')--}}
                    {{--window.location.href='https://maya-apa.com/mayaapi/maya/ramadan_checkins/' + user_data['user_id']--}}
                {{--else--}}
                    {{--window.location.href='https://maya.com.bd/auth/login'--}}
            {{--})--}}
            {{--.catch(error => console.log(error))--}}


    {{--})--}}

    {{--function setIftarTime(str) {--}}
        {{--$('.iftar_time').html(str)--}}
    {{--}--}}

    {{--function setSehriTime(str) {--}}
        {{--$('.sehri_time').html(str)--}}
    {{--}--}}

    {{--function setHour(str) {--}}
        {{--$('.hour').html(str + '<span class="timer_title"><br>HOURS</span>')--}}
    {{--}--}}

    {{--function setMin(str) {--}}
        {{--$('.min').html(str + '<span class="timer_title"><br>MINUTES</span>')--}}
    {{--}--}}

    {{--function setSec(str) {--}}
        {{--$('.sec').html(str + '<span class="timer_title"><br>SECONDS</span>')--}}
    {{--}--}}

    {{--function tConvert (time) {--}}
        {{--// Check correct time format and split into components--}}
        {{--time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];--}}

        {{--if (time.length > 1) { // If time format correct--}}
            {{--time = time.slice (1);  // Remove full string match value--}}
            {{--time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM--}}
            {{--time[0] = +time[0] % 12 || 12; // Adjust hours--}}
        {{--}--}}
        {{--return time.join (''); // return adjusted time or original string--}}
    {{--}--}}


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

    // var countDownDate = new Date(date + " " + iftar_time).getTime();
    // // console.log(date + " " + iftar_time)
    // // Update the count down every 1 second
    // var x = setInterval(function() {
    //
    //     // Get todays date and time
    //     var now = new Date().getTime();
    //
    //     // Find the distance between now and the count down date
    //     var distance = countDownDate - now;
    //
    //     // Time calculations for days, hours, minutes and seconds
    //     var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    //     var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    //     var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    //     var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    //
    //     setHour(hours)
    //     setMin(minutes)
    //     setSec(seconds)
    //     // console.log(minutes + " " + seconds)
    //     // Display the result in the element with id="demo"
    //     // document.getElementById("demo").innerHTML = days + "d " + hours + "h "
    //     //     + minutes + "m " + seconds + "s ";
    //
    //     // console.log(days + "d " + hours + "h "
    //     //     + minutes + "m " + seconds + "s ")
    //
    //     // If the count down is finished, write some text
    //     if (distance < 0) {
    //         clearInterval(x);
    //         setHour('00')
    //         setMin('00')
    //         setSec('00')
    //     }
    // }, 1000);



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

    var from = 253001
    var to = from + 2000 - 1
    var thread = setInterval(function () {
        console.log('from', from, 'to', to)
        // do some work
        fetch('/mayaapi/test/' + from + '/' + to, {
            method: 'GET'
        })
            .then( response => response.json())
            .then( data => console.log(data))
            .catch( err => console.log(err))
        // then
        from = to + 1
        to = from + 2000 - 1
    }, 50000)



</script>

</body>
</html>