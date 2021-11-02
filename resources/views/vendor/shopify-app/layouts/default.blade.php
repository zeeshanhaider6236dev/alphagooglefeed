<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" href="{{ asset('assets/img/fav.png') }}" type="image/gif">
        <title>{{ config('shopify-app.app_name') }}</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Slab:300,400|Roboto:300,400,700">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <link rel="stylesheet" href="{{ asset('assets') }}/css/styles.min.css">
        @yield('styles')
        <style>
            .right_btn a{
                white-space: nowrap;
            }
            .toast{
                font-size: 15px;
            }
            .toast-success{
                background-color: #007bff;
            }
        </style>
        <!-- Facebook Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '609943173266497');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=609943173266497&ev=PageView&noscript=1"/></noscript>
        <!-- End Facebook Pixel Code -->
    </head>

    <body> 
        @yield('content')
        @if(config('shopify-app.appbridge_enabled'))
            <script src="https://unpkg.com/@shopify/app-bridge{{ config('shopify-app.appbridge_version') ? '@'.config('shopify-app.appbridge_version') : '' }}"></script>
            <script>
                var AppBridge = window['app-bridge'];
                var createApp = AppBridge.default;
                var app = createApp({
                    apiKey: '{{ config('shopify-app.api_key') }}',
                    shopOrigin: '{{ Auth::user()->name }}',
                    forceRedirect: true,
                });
            </script>
        @endif
        <!-- Load Facebook SDK for JavaScript -->
        <div id="fb-root"></div>
        <script>
            window.fbAsyncInit = function() {
            FB.init({
                xfbml            : true,
                version          : 'v9.0'
            });
            };

            (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>

        <!-- Your Chat Plugin code -->
        <div class="fb-customerchat"
            attribution=setup_tool
            page_id="108798604412199">
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="{{ asset('assets') }}/js/script.min.js"></script>
        <script>
            toastr.options.closeButton = true;
            toastr.options.positionClass = "toast-bottom-center";
            toastr.options.closeHtml = '<button>&times;</button>';
            function messages(response = undefined) {
                if (response.hasOwnProperty('success')) {
                    toastr.success(response.success,'Success!')
                } else if (response.hasOwnProperty('error')) {
                    toastr.error(response.error,'Error!')
                } else if (response.hasOwnProperty('errors')) {
                    $.each(response.errors, function (index, error) {
                        toastr.error(error,'error!')
                    });
                }
            }
            function addSpinner(element){
                $(element).append(`&nbsp;<span style="margin-bottom:3px;" class="spinner-border spinner-border-sm loaderClass"></span> `);
            }
            function ajaxRequest(url, callfunc = undefined, method = 'GET', data = {}) {
                $.ajax({
                    url: url,
                    method: method,
                    data: data,
                    processData: true,
                }).done(function (response) {
                    messages(response);
                    if(callfunc){
                        callfunc(response);
                    }
                    if(response.hasOwnProperty('reload')){
                        location.reload();
                    }
                    if(response.hasOwnProperty('url')){
                        location.assign(response.url);
                    }
                }).always(function (response) {
                    if(response.hasOwnProperty('redirect')){
                        window.location.href = response.redirect;
                    }
                });
            }
        </script>
        @yield('scripts')
    </body>
</html>