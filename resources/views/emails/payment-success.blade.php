<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Maya Apa - Payment Success Mail!</title>
    <style>
        .templateFooter {
            background-color: #333333;
            background-image: none;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            border-top: 0;
            border-bottom: 0;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .dot {
            height: 10px;
            width: 10px;
            background-color: #1ca671;
            border-radius: 50%;
            display: inline-block;
        }

        .text2 {
            text-align: left;
            padding: 0 200px 0 120px;
            font-size: 18px
        }

        .text3 {
            line-height: 1.6;
            text-align: left;
            padding: 0 200px 0 120px;
        }

        .text4 {
            background-color: #edede9;
            margin: 0 30% 0 30%;
        }

        .text5 {
            text-align: left;
            padding: 0 200px 0 120px;
            font-size: 18px
        }

        .img3 {
            width: 48%;
            margin: 0 0 0 25%;
        }

        .social-icon{
            list-style: none;
            margin: 0;
            padding: 0;
            display: table;
            width: 100%;
            text-align: center;
        }
        .social-icon li{
            margin: 0 auto;
            display: inline-block;
            float: none;
            padding-right: 15px;
        }

        .footer{
            text-align: center;
        }

        @media screen and (max-width: 360px) {
            .text2 {
                padding: 0 10px 0 10px;
                font-size: 16px;
            }
            .text3 {
                padding: 0 15px 0 15px;
            }
            .text4 {
                background-color: #edede9;
                margin: 0;
            }
            .text5 {
                padding: 0 10px 0 10px;
            }
            .img3 {
                width: 100%;
                margin: 0;
            }
        }

        @media screen and (max-width: 400px) {
            .text2 {
                padding: 0 10px 0 10px;
                font-size: 16px;
            }
            .text3 {
                padding: 0 15px 0 15px;
                font-size: 13px;
            }
            .text4 {
                background-color: #edede9;
                margin: 0;
            }
            .text5 {
                padding: 0 10px 0 10px;
            }
            .img3 {
                width: 100%;
                margin: 0;
            }
        }

        @media screen and (max-width: 450px) {
            .text2 {
                padding: 0 10px 0 10px;
                font-size: 16px;
            }
            .text3 {
                padding: 0 15px 0 15px;
                font-size: 13px;
            }
            .text4 {
                background-color: #edede9;
                margin: 0;
            }
            .text5 {
                padding: 0 10px 0 10px;
            }
            .img3 {
                width: 100%;
                margin: 0;
            }
        }

        @media screen and (max-width: 600px) {
            .text2 {
                padding: 0 10px 0 10px;
                font-size: 16px;
            }
            .text3 {
                padding: 0 15px 0 15px;
            }
            .text4 {
                background-color: #edede9;
                margin: 0;
            }
            .text5 {
                padding: 0 10px 0 10px;
            }
            .img3 {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>

<body>
<div style="background-color: #ffffff">
    <img src="https://preview.ibb.co/ckF4m8/Top_artwork.png" style="width: 100%">
    <img src="https://image.ibb.co/krYwio/logo.png" style="margin: 0 0 0 45%;width: 10%;">
    <p style="text-align: center; font-size: 24px">WELCOME TO MAYA APA PLUS</p>
    <img src="https://preview.ibb.co/cLNBG8/Group.png" class="img3">

    <p class="text2">
        <b>Dear {{ ucfirst($userInfo->name) }}!</b>
        <br>Greetings from
        <b>Maya Apa!</b>
        <br>Congratulations! You have successfully subscribed to the {{ $package->name_en }} plan. Now, get all your queries answered by our pool of experienced experts along with a host of features including:
    </p>

    <p class="text3">
        <b><span class="dot"></span>&nbsp;&nbsp;Daily Health TIPS</b>
        <br>
        <b><span class="dot"></span>&nbsp;&nbsp;Exclusive discount & offers from Maya Apa partners</b>
        <br>
        <b><span class="dot"></span>&nbsp;&nbsp;Exclusive content from celebrities and reputable experts</b>
        <br>
        <b><span class="dot"></span>&nbsp;&nbsp;Access to chat groups</b>
    </p>
    <p style="text-align: center;font-size: 20px">Your Payment Information:</p>

    <div class="text4">
        <p style="line-height: 1.6;padding: 4%">
            <b>Invoice ID : </b>{{ $payment->invoice_id }}
            <br>
            <b>Package Name : </b> {{ $package->name_en }}
            <br>
            <b>Amount : </b>{{ $payment->amount }}
            <br>
            <b>Effective Time : </b>{{ $payment->effective_time }}
            <br>
            <b> Expiry Time :</b> {{ $payment->expiry_time }}
        </p>
    </div>

    <p class="text5">We look forward to assist you 24//7! For any question please send email to
        <a href="mailto:info@maya.com.bd" style="color: #1ca671">info@maya.com.bd</a>
    </p>

    <div class="templateFooter">
        <div class="footer">
            <ul class="social-icon">
                <li>
                    <a href="https://www.facebook.com/MayaApaApp/" target="_blank" rel="noopener">
                        <img src="https://cdn-images.mailchimp.com/icons/social-block-v2/outline-light-facebook-48.png">
                    </a>
                </li>
                <li>
                    <a href="https://twitter.com/MayaApaApp" target="_blank" rel="noopener">
                        <img src="https://cdn-images.mailchimp.com/icons/social-block-v2/outline-light-twitter-48.png">
                    </a>
                </li>
                <li>
                    <a href="https://www.instagram.com/mayaapaapp/" target="_blank" rel="noopener">
                        <img src="https://cdn-images.mailchimp.com/icons/social-block-v2/outline-light-instagram-48.png">
                    </a>
                </li>
                <li>
                    <a href="https://www.linkedin.com/company/3792485/" target="_blank" rel="noopener">
                        <img src="https://cdn-images.mailchimp.com/icons/social-block-v2/outline-light-linkedin-48.png">
                    </a>
                </li>
            </ul>
            <br>
            <p style="color: #fff;text-align: -webkit-center;">Copyright © 2017. Mayalogy Ltd , All rights reserved.</p>
        </div>
    </div>
</div>

</body>
</html>
