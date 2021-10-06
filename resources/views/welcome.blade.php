<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @livewireStyles
    </head>
    <body class="antialiased" x-data="componentData()">
        <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
            <pre x-text="value"></pre>
        </div>

        <script>
            function componentData() {
                return {
                    value: null,
                    init() {
                        console.log('sdfsdfsdfsdf');
                        window.Echo.channel('example').listen('.example', (e) => {
                            console.log(e);
                            this.value = e.message;
                        })
                    }
                }
            }
        </script>

        @livewireScripts
        <script src="{{ asset('js/app.js') }}" defer></script>
    </body>
</html>
