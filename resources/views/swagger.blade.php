<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
        <meta name="description" content="" />

        <title>API Documentation</title>
        <link rel="icon" type="image/png" href="{{asset('/assets/mifik_logo_launch.png')}}"/>
    </head>

    <body>
        <div id="swagger-api"></div>
        <script src="{{ mix('js/swagger.js') }}"></script>
        @vite('resources/js/swagger.js')
    </body>
</html>
