<!doctype html>
<html lang="{{ htmlLang() }}" @langrtl dir="rtl" @endlangrtl>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title') | {{ appName() }}</title>
    <meta name="description" content="@yield('meta_description', appName())">
    <meta name="author" content="@yield('meta_author', 'Anthony Rappa')">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/brand/logo.png') }}">
    @yield('meta')
    <script src="../../../js/app.js"></script>
    @stack('before-styles')
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ mix('css/frontend.css') }}" rel="stylesheet">
    {{-- <link rel="stylesheet" href="{{ mix('css/assets/index.scss') }}"> --}}

    <livewire:styles />
    {{-- <link rel="stylesheet" href="owlcarousel/owl.carousel.min.css">
    <link rel="stylesheet" href="owlcarousel/owl.theme.default.min.css"> --}}
    @stack('after-styles')

</head>

<body>
    @include('includes.partials.read-only')
    @include('includes.partials.logged-in-as')
    @include('includes.partials.announcements')

    <div id="app">
        @guest
            @include('frontend.includes.guest.nav')
            @stack('page-messages')
            <main id="main-content" class="pb-3">
                <div id="preloder">
                    <div class="loader"></div>
                </div>
                @yield('content')
            </main>
        @else
            <div class="wrapper">
                @include('frontend.includes.sidebar')
                <!-- Page Content  -->
                <div id="content-container" class="wrapper-content">
                    <div id="content">
                        @include('frontend.includes.nav')
                        @stack('page-messages')
                        <main id="main-content" class="pb-3">
                            <div id="preloder">
                                <div class="loader"></div>
                            </div>
                            @yield('content')
                        </main>
                    </div>
                </div>
            </div>
        @endguest
        <div class="footer">
            @include('frontend.includes.footer')
        </div>
    </div><!--app-->
    @auth
        <div id="chat" style="position: fixed; bottom: 0; right: 5%; z-index: 1"></div>
    @endauth
    @stack('before-scripts')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/3.3.1/mixitup.min.js" integrity="sha512-nKZDK+ztK6Ug+2B6DZx+QtgeyAmo9YThZob8O3xgjqhw2IVQdAITFasl/jqbyDwclMkLXFOZRiytnUrXk/PM6A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/frontend.js') }}"></script>
    {{-- <script src="{{ mix('js/assets/index.js') }}"></script> --}}
    <script src="{{ asset('js/assets/vendor/ckeditor5/build/ckeditor.js') }}"></script>
    {{-- <script src="jquery.min.js"></script> --}}
    {{-- <script src="owlcarousel/owl.carousel.min.js"></script> --}}
    <script>
        $(document).ready(function() {
            if (document.getElementById('ckeditor')) {
                ClassicEditor
                    .create(document.querySelector('#ckeditor'))
                    .catch(error => {
                        console.error(error);
                    });
            }
            $(".owl-carousel").owlCarousel();
        });
    </script>
    @stack('after-scripts')
</body>

</html>
