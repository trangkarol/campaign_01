<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/images/favicon.ico') }}"/>
    {!! Html::script(asset('frontend/js/webfontloader.min.js')) !!}
    <script>
        WebFont.load({
            google: {
                families: ['Roboto:300,400,500,700:latin']
            }
        });
        window.Laravel = {!! json_encode([
            'appName' => config('app.name'),
            'csrfToken' => csrf_token(),
            'locale' => config('app.locale'),
            'fallbackLocale' => config('app.fallback_locale'),
            'url' => url('/'),
            'url_after_login' => null,
            'languages' => config('settings.locale'),
            'settings' => [
                'campaigns' => config('settings.campaigns'),
                'events' => config('settings.events'),
                'actions' => config('settings.actions'),
                'value' => config('settings.value_of_settings'),
                'imageEventDefault' => config('settings.image_event_default'),
            ],
            'pagination' => [
                'follow' => config('settings.pagination.follow'),
                'friend' => config('settings.pagination.friend'),
            ],
            'key_google_map' => env('KEY_GOOGLE_MAP'),
            'port_connect_server' => env('PORT_SERVER_NODE')
        ]) !!};
        @if (session('access_token'))
            localStorage.setItem('access_token', '{{ session('access_token') }}')
        @endif
    </script>
    {!! Html::style(asset('frontend/css/bootstrap.css')) !!}
    {!! Html::style(mix('frontend/css/theme.css')) !!}
    {!! Html::style(mix('frontend/css/blocks.css')) !!}
    {!! Html::style(mix('frontend/css/font.css')) !!}
    {!! Html::style(asset('frontend/css/daterangepicker.css')) !!}
    {!! Html::style(asset('frontend/css/bootstrap-select.css')) !!}
    {!! Html::style(mix('frontend/css/app.css')) !!}
    {!! Html::style(asset('frontend/css/swiper.min.css')) !!}
    {!! Html::style(asset('frontend/css/post-new.css')) !!}
</head>
</head>

<body>
    <div id="app">
        <router-view></router-view>
    </div>
    {!! Html::script(mix('frontend/js/bootstrap.js')) !!}
    {!! Html::script(asset('frontend/js/material.min.js')) !!}
    {!! Html::script(asset('frontend/js/theme-plugins.js')) !!}
    {!! Html::script(asset('frontend/js/selectize.min.js')) !!}
    {!! Html::script(asset('frontend/js/daterangepicker.min.js')) !!}
    {!! Html::script(asset('frontend/js/swiper.jquery.min.js')) !!}
    {!! Html::script(mix('frontend/js/app.js')) !!}
</body>

</html>
