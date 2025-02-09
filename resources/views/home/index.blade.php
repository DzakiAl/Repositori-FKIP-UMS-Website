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
            <a href="{{route('repository.index')}}" class="home-link">Home</a>
            {{-- Loop through data types and their study programs --}}
            @foreach ($data as $type => $programs)
                <div class="dropdown">
                    <p class="dropdown-toggle">{{ $type }}</p>
                    <div class="dropdown-menu">
                        @foreach ($programs as $programPath) 
                            @php $program = basename($programPath); @endphp 
                            <a href="{{ route('repository.file_manager', ['type' => $type, 'program' => $program]) }}" class="dropdown-item">
                                {{ $program }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="hamburger-container" onclick="toggleSidebar()">
            <div class="hamburger"></div>
            <div class="hamburger"></div>
            <div class="hamburger"></div>
        </div>
    </nav>

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebar">
        <a href="{{route('repository.index')}}" class="home-link">Home</a>
        @foreach ($data as $type => $programs)
            <div class="sidebar-dropdown">
                <p class="sidebar-toggle">{{ $type }}</p>
                <div class="sidebar-dropdown-menu">
                    @foreach ($programs as $programPath) 
                        @php $program = basename($programPath); @endphp 
                        <a href="{{ route('repository.file_manager', ['type' => $type, 'program' => $program]) }}" class="sidebar-item">
                            {{ $program }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

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

    {{-- JavaScript --}}
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Toggle dropdowns in sidebar
        document.querySelectorAll('.sidebar-toggle').forEach(toggle => {
            toggle.addEventListener('click', () => {
                let dropdownMenu = toggle.nextElementSibling;
                dropdownMenu.classList.toggle('show');
            });
        });
    </script>

</body>
</html>