<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Repositori FKIP UMS | {{ $type }} - {{ $program }}</title>
    @vite(['resources/css/file_manager.css', 'resources/js/app.js'])
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
        @foreach ($data as $dataType => $studyPrograms)
            <div class="sidebar-dropdown">
                <p class="sidebar-toggle">{{ $dataType }}</p>
                <div class="sidebar-dropdown-menu">
                    @foreach ($studyPrograms as $studyProgram)
                            <a href="{{ route('repository.file_manager', ['type' => $dataType, 'program' => basename($studyProgram)]) }}" class="dropdown-item">
                                {{ basename($studyProgram) }}
                            </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Content --}}
    <div class="content">
        <div class="title-container">
            <h1 class="title">Repositori {{ $type }} Prodi {{ $program }}</h1>
        </div>
        <div class="file-manager-container">
            <form action="{{ route('repository.file_manager', ['type' => $type, 'program' => $program]) }}" method="GET" class="search-form">
                <input type="text" class="search-input" name="search" placeholder="Cari file atau folder" value="{{ request('search') }}">
                <button type="submit" class="search-button">Search</button>
            </form>

            @auth
                <div class="file-manager-option-container">
                    {{-- Upload File Form --}}
                    <form action="{{ route('repository.upload_file', ['type' => $type, 'program' => $program]) }}" method="POST" enctype="multipart/form-data" id="upload-form" style="display: none;">
                        @csrf
                        <input type="file" name="files[]" id="file-input" multiple>
                    </form>
                    <button id="upload-button" class="upload-button">Upload</button>

                    {{-- Add Folder Form --}}
                    <form action="{{ route('repository.add_folder', ['type' => $type, 'program' => $program]) }}" method="POST" style="display: flex">
                        @csrf
                        <input type="text" name="folder_name" id="folder_name" class="name-folder-input" placeholder="Ketik nama folder yang ingin ditambahkan">
                        <button type="submit" class="add-folder-button">Add Folder</button>
                    </form>
                </div>
            @endauth

            {{-- File and Folder List --}}
            <table class="file-folder-list-container">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nama</th>
                        <th>Terakhir Dimodifikasi</th>
                        <th>Ukuran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($folders as $folder)
                        <tr class="separator-row">
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td class="options">
                                <div class="options-container">
                                    <span class="options-menu">⋮</span>
                                    <div class="context-menu">
                                        <a href="">Open</a>
                                        <a href="{{ route('repository.compress_folder', ['type' => $type, 'program' => $program, 'folder' => $folder]) }}">
                                            Compress to Zip
                                        </a>
                                        @auth
                                            <a href="{{ route('repository.delete_folder', ['type' => $type, 'program' => $program, 'folder' => $folder]) }}" onclick="return confirm('Are you sure?')">
                                                Delete
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </td>
                            <td class="folder_file_name">
                                <img src="{{ asset('assets/folder_icon.png') }}" class="icon">
                                <strong>{{ $folder }}</strong>
                            </td>
                            <td>—</td>
                            <td>—</td>
                        </tr>
                        <tr class="separator-row">
                            <td colspan="4"></td>
                        </tr>
                    @endforeach

                    @foreach ($files as $file)
                        <tr class="separator-row">
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td class="options">
                                <div class="options-container">
                                    <span class="options-menu">⋮</span>
                                    <div class="context-menu">
                                        @if (Str::endsWith($file['name'], '.zip'))
                                            <!-- Options for ZIP files -->
                                            <a href="#" class="download-file">Download</a>
                                            @auth
                                                <a href="{{ route('repository.delete_file', ['type' => $type, 'program' => $program, 'file' => $file['name']]) }}" onclick="return confirm('Are you sure?')">Delete</a>
                                                <a aria-autocomplete=""href="{{ route('repository.extract_zip', ['type' => $type, 'program' => $program, 'file' => $file['name']]) }}">Extract</a>
                                            @endauth
                                        @else
                                            <!-- Options for other files -->
                                            <a href="">Open</a>
                                            <a href="#" class="download-file">Download</a>
                                            <div id="passwordModal" class="modal-overlay">
                                                <div class="modal-content">
                                                    <h3 class="modal-title">Enter Password to Download</h3>
                                                    <form action="{{ route('repository.download_file', ['type' => $type, 'program' => $program, 'file' => $file['name']]) }}" method="GET">
                                                        <input id="passwordInput" class="modal-input" type="password" name="password" required placeholder="Enter download password">
                                                        <div class="modal-option">
                                                            <button class="modal-button" type="submit">Download</button>
                                                            <button type="button" class="modal-close-button" onclick="closeModal()">Close</button> <!-- Close button -->
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            @auth
                                                <a href="{{ route('repository.delete_file', ['type' => $type, 'program' => $program, 'file' => $file['name']]) }}" onclick="return confirm('Are you sure?')">Delete</a>
                                                <a href="{{ route('repository.compress_file', ['type' => $type, 'program' => $program, 'file' => $file['name']]) }}">Compress to Zip</a>
                                            @endauth
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="folder_file_name">
                                <img src="{{ asset('assets/document_icon.png') }}" class="icon"> {{ $file['name'] }}
                            </td>
                            <td>{{ $file['modified'] }}</td>
                            <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                        </tr>
                        <tr class="separator-row">
                            <td colspan="4"></td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
        // Upload Files
        document.addEventListener("DOMContentLoaded", function() {
            let uploadButton = document.getElementById('upload-button');
            let fileInput = document.getElementById('file-input');

            if (uploadButton && fileInput) {
                uploadButton.addEventListener('click', function() {
                    fileInput.click();
                });

                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        document.getElementById('upload-form').submit();
                    }
                });
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            let uploadButton = document.getElementById('upload-button');
            let fileInput = document.getElementById('file-input');
            let uploadForm = document.getElementById('upload-form');

            if (uploadButton && fileInput && uploadForm) {
                uploadButton.addEventListener('click', function() {
                    fileInput.click();
                });

                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        uploadForm.submit();
                    }
                });
            }
        });

        // Close popup after a few seconds
        setTimeout(() => {
            const popup = document.getElementsByClassName('popup-container')[0];
            if (popup) {
                popup.style.display = 'none';
            }
        }, 3000);

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

        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.download-file').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Find the closest modal related to this button
                    let modal = this.closest('tr').querySelector('.modal-overlay');
                    if (modal) {
                        modal.style.display = 'flex';
                    }
                });
            });

            document.querySelectorAll('.modal-close-button').forEach(function (closeButton) {
                closeButton.addEventListener('click', function () {
                    let modal = this.closest('.modal-overlay');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.querySelector('.modal-input').value = ''; // Clear input field
                    }
                });
            });

            window.addEventListener('click', function (e) {
                document.querySelectorAll('.modal-overlay').forEach(function (modal) {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                        modal.querySelector('.modal-input').value = ''; // Clear input field
                    }
                });
            });
        });

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