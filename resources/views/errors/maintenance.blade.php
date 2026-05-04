<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>
    @vite('resources/css/app.css')
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }
    </style>
</head>

<body class="">
    <section class="min-h-screen grid place-items-center">
        <div class="container mx-auto">
            <div class="text-center">

                <div class="my-2 max-w-md mx-auto text-balance">
                    <img src="{{ env('VITE_CPANELPATH') }}img/logo/maintenance.png" alt="maintenance">
                </div>

                <div class="my-6 max-w-xl mx-auto text-balance text-3xl font-bold">
                    SAKU Sedang dalam Maintenance
                </div>
                <div class="text-foreground max-w-xl mx-auto text-balance text-xl my-4">
                    Saat ini aplikasi SAKU (Satu Aplikasi untuk Kinerja Unggul) sedang dalam proses pemeliharaan rutin
                    untuk
                    meningkatkan kualitas layanan dan keamanan. Terima kasih atas pengertian dan kesabaran Anda 🙏
                </div>

                <div class="text-foreground max-w-xl mx-auto text-balance text-xl">
                    - Dengan ❤️, Tim Pengembang SAKU
                </div>


            </div>
        </div>
    </section>
</body>

</html>
