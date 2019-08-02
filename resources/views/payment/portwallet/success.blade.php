<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
<style type="text/css">
    @import url('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
    /*@import url('https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css');*/
    .success-msg {
        padding: 20px 15px;
        /*width: 20%;*/
        margin: 15px 20px;
        color: #270;
        background-color: #DFF2BF;
    }
    .error-msg {
        padding: 20px 15px;
        /*width: 20%;*/
        margin: 15px 20px;
        color: #495057;
        /*background-color: orangered;*/
        border: 3px solid #00000085;
    }
</style>
<div class="row" style="width: 97%; margin: 0;">
@if($status=='ACCEPTED')
    <div class="success-msg col-12">
        <i class="fa fa-check"></i>
        Payment Successful
    </div>
    <div class="success-msg col-12">
        <i class="fa fa-check"></i>
        লেনদেন সফলভাবে সম্পন্ন হয়েছে
    </div>
@elseif($status=='EXPIRED')
    <div class="error-msg col-12">
        <i class="fa fa-times"></i>
        Your Payment has expired. Please select the package again.
    </div>
    <div class="error-msg col-12">
        <i class="fa fa-times"></i>
        আপনার লেনদেনটি মেয়াদোত্তীর্ণ হয়ে গেছে। পুনরায় প‍্যাকেজ নির্বাচন করুন।
    </div>
@elseif($status=='REJECTED')
    <div class="error-msg col-12">
        <i class="fa fa-times"></i>
        Your Payment was rejected.
        <br />To try again
        <br /><a href="{{$try_again_url}}?invoice={{$invoice_id}}" class="btn btn-warning" role="button">Click here</a>
    </div>
    <div class="error-msg col-12">
        <i class="fa fa-times"></i>
        আপনার লেনদেনটি বাতিল হয়ে গেছে।
        <br />পুনরায় চেষ্টা করতে <a href="{{$try_again_url}}?invoice={{$invoice_id}}" class="btn btn-warning" role="button">এখানে চাপুন</a>
    </div>
@else
    <div class="error-msg col-12">
        <i class="fa fa-times"></i>
        Your Payment was cancelled.
        <br />To try again
        <br /><a href="{{$try_again_url}}?invoice={{$invoice_id}}" class="btn btn-warning" role="button">Click here</a>
    </div>
    <div class="error-msg col-12">
        <i class="fa fa-times"></i>
        আপনার লেনদেনটি বিঘ্নিত হয়েছে।
        <br />পুনরায় চেষ্টা করতে <a href="{{$try_again_url}}?invoice={{$invoice_id}}" class="btn btn-warning" role="button">এখানে চাপুন</a>
    </div>
@endif
</div>