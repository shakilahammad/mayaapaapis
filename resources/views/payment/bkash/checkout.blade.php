@extends('payment.bkash.bkash-layout')

@section('bkashContent')
    <div id="loader">
        <img class="img-responsive align-content-center" src="{{ asset('images/loader.svg') }}" alt="Maya Loader">
    </div>

    <button class="btn btn-primary" style="display: none;" id="bKash_button">Pay With bKash</button>
@endsection

@push('scripts')
    <script type="text/javascript">
        const amount = "{{ $amount }}";
        const intent = "{{ $intent }}";

        const url_string = window.location.href,
            url = new URL(url_string);

        const userId = url.searchParams.get("userId");
        const packageId = url.searchParams.get("packageId");

        let paymentConfig;
        if (window.location.hostname === 'maya-apa.com') {
            paymentConfig = {
                createCheckoutURL: "https://maya-apa.com/mayaapi/payment/bkash/checkout/" + userId + "/" + packageId,
                executeCheckoutURL: "https://maya-apa.com/mayaapi/payment/bkash/execute/" + userId + "/" + packageId,
            };
        } else {
            paymentConfig = {
                createCheckoutURL: "http://mayaapaapi.local/payment/bkash/checkout/" + userId + "/" + packageId,
                executeCheckoutURL: "http://mayaapaapi.local/payment/bkash/execute/" + userId + "/" + packageId
            };
        }

        function redirectToErrorPage(status, message) {
            let errorCode = null;
            let errorMessage = null;
            if (message !== 'null'){
                let newMessage = JSON.parse(message);
                errorCode = newMessage.errorCode;
                errorMessage = newMessage.errorMessage;
            }

            if (window.location.hostname === 'maya-apa.com') {
                window.location.href = "/mayaapi/payment/bkash/success?status="+ status + "&errorCode="+errorCode + "&errorMessage="+errorMessage;
            } else {
                window.location.href = "/payment/bkash/success?status="+ status + "&errorCode="+errorCode + "&errorMessage="+errorMessage;
            }
        }

        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: {amount: amount, intent: intent},

            createRequest: function (request) {
                $.ajax({
                    url: paymentConfig.createCheckoutURL,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(request),
                    success: function (response) {
                        let data = response.data;
                        if (response.status === 'success' && data.paymentID != null) {
                            paymentID = data.paymentID;
                            bKash.create().onSuccess(data);
                        }else if (response.status === 'freemium'){
                            redirectToErrorPage('ACCEPTED', 'null');
                        } else if(response.status === 'already'){
                            redirectToErrorPage('ALREADY', 'null');
                        } else if (response.status === 'bkash-error') {
                            redirectToErrorPage('ERROR', JSON.stringify(data));
                        }else{
                            bKash.create().onError();
                            redirectToErrorPage('ERROR', JSON.stringify(data));
                        }
                    },
                    error: function () {
                        bKash.create().onError();
                    }
                });
            },
            executeRequestOnAuthorization: function () {
                $.ajax({
                    url: paymentConfig.executeCheckoutURL,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({"paymentID": paymentID}),
                    success: function (response) {
                        let data = response.data;
                        if (response.status === 'success' && data.paymentID != null) {
                            redirectToErrorPage('ACCEPTED', 'null');
                        } else if (response.status === 'bkash-error') {
                            // console.log(response, 'bkash');
                            redirectToErrorPage('ERROR', JSON.stringify(data));
                        } else {
                            // console.log(data, 'error');
                            redirectToErrorPage('DECLINED', 'null');
                            bKash.execute().onError();
                        }
                    },
                    error: function () {
                        bKash.execute().onError();
                    }
                });
            }
        });

        setTimeout(function(){
            document.getElementById('bKash_button').click();
            document.getElementById('loader').style.display = "none";
        }, 2000);

    </script>
@endpush
