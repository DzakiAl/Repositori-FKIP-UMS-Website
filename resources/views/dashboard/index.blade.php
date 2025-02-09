<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard</title>
    @vite(['resources/css/dashboard.css', 'resources/js/app.js'])
</head>
<body>
    {{-- Pop up for success or error message --}}
    @if (session('success') || session('error'))
        <div class="popup-container {{ session('error') ? 'error' : 'success' }}" id="popup">
            <p class="popup">{{ session('success') ?? session('error') }}</p>
        </div>
    @endif

    {{-- Navbar --}}
    @vite(['resources/css/navbar.css', 'resources/js/app.js'])
    <nav class="navbar">
        <img src="{{ asset('assets/logo_ums.png') }}" class="logo" alt="UMS Logo">
        <div class="menu">
            <a href="{{ route('repository.index') }}" class="home-link">Home</a>
            @foreach ($data as $dataType => $studyPrograms)
                <div class="dropdown">
                    <p class="dropdown-toggle">{{ $dataType }}</p>
                    <div class="dropdown-menu">
                        @foreach ($studyPrograms as $studyProgram)
                            <a href="{{ route('repository.file_manager', ['type' => $dataType, 'program' => basename($studyProgram)]) }}" class="dropdown-item">
                                {{ basename($studyProgram) }}
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
                    @foreach ($programs as $program)
                        <a href="{{ route('repository.file_manager', ['type' => $type, 'program' => basename($program)]) }}" class="sidebar-item">
                            {{ basename($program) }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="title-container">
        <h1 class="title">Dashboard</h1>
    </div>

    {{-- content --}}
    <div class="content">
        <div class="form-container">
            {{-- Form for changing username --}}
            <form action="{{ route('update.username') }}" method="POST" class="form">
                @csrf
                <p class="label">Username:</p>
                <input type="text" class="input" name="username" value="{{ auth()->user()->name }}" required>
                <button type="submit" class="button">Simpan</button>
            </form>

            {{-- Form for changing password --}}
            <form action="{{ route('update.password') }}" method="POST" class="form">
                @csrf
                <p class="label">Password Baru:</p>
                <input type="password" class="input" name="new_password" required placeholder="Masukkan Password Baru">
                
                <p class="label">Password Lama:</p>
                <input type="password" class="input" name="old_password" required placeholder="Masukkan Password Lama">

                <p class="label">Konfirmasi Password:</p>
                <input type="password" class="input" name="new_password_confirmation" required placeholder="Masukkan Password Baru Lagi Untuk Konfirmasi">
                
                <button type="submit" class="button">Simpan</button>
            </form>

            {{-- form for change file and folder password --}}
            <form action="{{ route('update_download_password') }}" method="POST" class="form">
                @csrf
                <p class="label">Password File dan Folder Baru:</p>
                <input type="password" class="input" name="new_password_file_folder" placeholder="Masukkan password file dan folder baru" required>
            
                <p class="label">Password File dan Folder Lama:</p>
                <input type="password" class="input" name="old_password_file_folder" placeholder="Masukkan password file dan folder lama" required>
            
                <p class="label">Konfirmasi Password File dan Folder:</p>
                <input type="password" class="input" name="confirm_password_file_folder" placeholder="Masukkan password file dan folder yang baru lagi untuk konfirmasi" required>
            
                <button class="button">Simpan</button>
            </form>

            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-button">Log Out</button>
            </form>            
        </div>
    </div>
    <script>
        document.querySelector('.logout-button').addEventListener('click', function (event) {
            event.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                document.getElementById('logout-form').submit();
            }
        });

        // Close popup after a few seconds
        setTimeout(() => {
            const popup = document.getElementsByClassName('popup-container')[0];
            if (popup) {
                popup.style.display = 'none';
            }
        }, 3000);

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

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".options-menu").forEach(menu => {
                menu.addEventListener("click", function(event) {
                    event.stopPropagation();
                    let dropdown = this.nextElementSibling;
                    document.querySelectorAll(".dropdown-menu").forEach(menu => menu.style.display =
                        "none");
                    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
                });
            });

            document.addEventListener("click", function() {
                document.querySelectorAll(".dropdown-menu").forEach(menu => menu.style.display = "none");
            });
        });
    </script>
</body>
</html>