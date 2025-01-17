<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Repositori FKIP UMS | Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- Navbar --}}
    @vite(['resources/css/navbar.css', 'resources/js/app.js'])
    <nav class="navbar">
        <img src="{{ asset('assets/logo_ums.png') }}" class="logo">
        <div class="menu">
            {{-- Loop through data types and their study programs --}}
            @foreach ($data as $type => $programs)
                <div class="dropdown">
                    <p class="dropdown-toggle">{{ $type }}</p>
                    <div class="dropdown-menu">
                        @foreach ($programs as $program)
                            <a href="{{ route('repository.file_manager', ['type' => $type, 'program' => basename($program)]) }}" class="dropdown-item">
                                {{ basename($program) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </nav>

    {{-- Content --}}
    <div class="content">
        <div class="background" style="background-image: url('{{ asset('assets/pattern.png') }}');"></div>
        <div class="opening_sentence_container">
            <h1 class="opening_sentence">
                WEBSITE REPOSITORI FAKULTAS KEGURUAN DAN ILMU PENDIDIKAN UMS
            </h1>
            <div class="line-container">
                <div class="line-1"></div>
                <div class="line-2"></div>
            </div>
        </div>
    </div>
</body>
</html>