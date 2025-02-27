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
                    <a class="dropdown-toggle">{{ $dataType }}</a>
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

            <div class="file-manager-option-container">
                @auth
                    {{-- Upload File Form --}}
                    <form action="{{ route('repository.upload_file', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}" method="POST" enctype="multipart/form-data" id="upload-form" style="display: none;">
                        @csrf
                        <input type="file" name="files[]" id="file-input" multiple>
                    </form>                  
                    <button id="upload-button" class="upload-button">Upload File</button>

                    {{-- Upload Folder Form --}}
                    <form action="{{ route('repository.upload_folder', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}" method="POST" enctype="multipart/form-data" id="upload-form">
                        @csrf
                        <input type="file" name="files[]" id="folder-input" webkitdirectory multiple style="display: none;">
                    </form>                   
                    <button id="upload-folder-button" class="upload-button">Upload Folder</button>

                    {{-- Add Folder Form --}}
                    <form action="{{ route('repository.add_folder', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}" method="POST" style="display: flex">
                        @csrf
                        <input type="text" name="folder_name" id="folder_name" class="name-folder-input" placeholder="Ketik nama folder yang ingin ditambahkan">
                        <button type="submit" class="add-folder-button">Add Folder</button>
                    </form>
                @endauth
                
                {{-- Download all file and folder in current directory --}}
                <a href="#" class="download-all-file-folder-button" onclick="openPasswordModal()">Download All Files & Folders</a>
                <div id="passwordModal" class="modal-overlay">
                    <div class="modal-content">
                        <h3 class="modal-title">Enter Password to Download</h3>
                        <form id="downloadForm" action="{{ route('repository.download_all', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}" method="GET">
                            <input id="passwordInput" class="modal-input" type="password" name="password" required placeholder="Enter download password">
                            <div class="modal-option">
                                <button class="modal-button" type="submit">Download</button>
                                <button type="button" class="modal-close-button" onclick="closeModal()">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="breadcrumbs">
                <a href="{{ route('repository.file_manager', ['type' => $type, 'program' => $program]) }}">Root</a>
                @foreach ($breadcrumbs as $index => $crumb)
                    / <a href="{{ route('repository.file_manager', ['type' => $type, 'program' => $program, 'subfolder' => implode('/', array_slice($breadcrumbs, 0, $index + 1))]) }}">{{ $crumb }}</a>
                @endforeach
            </div>

            {{-- File and Folder List --}}
            <table class="file-folder-list-container">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Terakhir Dimodifikasi</th>
                        <th>Ukuran</th>
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($folders as $folder)
                        <tr class="separator-row">
                            <td colspan="4"></td>
                        </tr>
                        <tr class="folder_file_container">
                            <td class="folder_file_name">
                                <img src="{{ asset('assets/folder_icon.png') }}" class="icon">
                                <a href="{{ route('repository.file_manager', ['type' => $type, 'program' => $program, 'subfolder' => isset($subfolder) ? "$subfolder/$folder" : $folder]) }}">
                                    {{ $folder }}
                                </a>
                            </td>
                            <td>—</td>
                            <td>—</td>
                            <td class="options">
                                <a href="#" class="download-file">Download</a>
                                <div id="passwordModal" class="modal-overlay">
                                    <div class="modal-content">
                                        <h3 class="modal-title">Enter Password to Download</h3>
                                            <form action="{{ route('repository.download_folder', ['type' => $type, 'program' => $program, 'subfolder' => isset($subfolder) ? "$subfolder/$folder" : $folder]) }}" method="POST">
                                                @csrf
                                                <input id="passwordInput" class="modal-input" type="password" name="password" required placeholder="Enter download password">
                                                <div class="modal-option">
                                                    <button class="modal-button" type="submit">Download</button>
                                                    <button type="button" class="modal-close-button" onclick="closeModal()">Close</button>
                                                </div>
                                            </form>                                                
                                        </div>
                                    </div>
                                @auth
                                    <a href="#" onclick="renameItem('{{ $folder }}', '{{ route('repository.rename', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder]) }}')">Rename</a>
                                    <a href="{{ route('repository.delete_folder', ['type' => $type, 'program' => $program, 'folder' => isset($subfolder) ? "$subfolder/$folder" : $folder]) }}" onclick="return confirm('Are you sure? (This is action cannot be undone)')">Delete</a>
                                @endauth
                            </td>
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
                            <td class="folder_file_name">
                                <img src="{{ asset('assets/document_icon.png') }}" class="icon">
                                <a href="#" class="file-folder-name" onclick="showFilePasswordModal('{{ route('repository.open_file', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder, 'file' => $file['name']]) }}')">
                                    {{ $file['name'] }}
                                </a>
                                
                                <div id="filePasswordModal" class="file-modal-overlay">
                                    <div class="file-modal-content">
                                        <h3 class="file-modal-title">Enter Password to View File</h3>
                                        <input id="filePasswordInput" class="file-modal-input" type="password" required placeholder="Enter file password">
                                        <div class="file-modal-option">
                                            <button class="file-modal-button" onclick="submitFilePassword()">View File</button>
                                            <button type="button" class="file-modal-close-button" onclick="closeFileModal()">Close</button>
                                        </div>
                                    </div>
                                </div>                                                             
                            </td>
                            <td>{{ $file['modified'] }}</td>
                            <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                            <td class="options">
                                <a href="#" class="download-file">Download</a>
                                    <div id="passwordModal" class="modal-overlay">
                                        <div class="modal-content">
                                            <h3 class="modal-title">Enter Password to Download</h3>
                                            <form action="{{ route('repository.download_file', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder, 'file' => $file['name']]) }}" method="GET">
                                                <input id="passwordInput" class="modal-input" type="password" name="password" required placeholder="Enter download password">
                                                <div class="modal-option">
                                                    <button class="modal-button" type="submit">Download</button>
                                                    <button type="button" class="modal-close-button" onclick="closeModal()">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @auth
                                    <a href="#" onclick="renameItem('{{ $file['name'] }}', '{{ route('repository.rename', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder]) }}')">Rename</a>
                                    <a href="{{ route('repository.delete_file', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder, 'file' => $file['name']]) }}" onclick="return confirm('Are you sure? (This is action cannot be undone)')">Delete</a>
                                @endauth
                            </td>
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

        // Close popup after a few seconds
        setTimeout(() => {
            const popup = document.getElementsByClassName('popup-container')[0];
            if (popup) {
                popup.style.display = 'none';
            }
        }, 3000);

        // Download JS function for showing enter password to download modal
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

            // Download JS function when close modal clear password field
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

        // Rename folder and file logic
        function renameItem(oldName, renameUrl) {
            const newName = prompt("Enter the new name:", oldName);
            if (newName && newName !== oldName) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = renameUrl;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const oldNameInput = document.createElement('input');
                oldNameInput.type = 'hidden';
                oldNameInput.name = 'old_name';
                oldNameInput.value = oldName;

                const newNameInput = document.createElement('input');
                newNameInput.type = 'hidden';
                newNameInput.name = 'new_name';
                newNameInput.value = newName;

                form.appendChild(csrfToken);
                form.appendChild(oldNameInput);
                form.appendChild(newNameInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Upload folder button
        document.getElementById('upload-folder-button').addEventListener('click', function() {
            document.getElementById('folder-input').click();
        });

        // Upload folder logic
        document.getElementById('folder-input').addEventListener('change', function(event) {
            let formData = new FormData();
            
            for (let file of event.target.files) {
                formData.append('files[]', file);
                formData.append('paths[]', file.webkitRelativePath); // Send full relative path
            }

            fetch("{{ route('repository.upload_folder', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            }).then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error); // Show error if folder exists
                } else {
                    alert(data.message);
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Insert password first to view file JS function
        function showFilePasswordModal(fileUrl) {
            window.fileUrl = fileUrl; // Store file URL for later use
            document.getElementById('filePasswordModal').style.display = 'flex';
        }

        function closeFileModal() {
            let modal = document.getElementById('filePasswordModal');
            let passwordInput = document.getElementById('filePasswordInput');

            // Hide the modal
            modal.style.display = "none";

            // Clear the password field
            passwordInput.value = "";
        }

        function submitFilePassword() {
            let passwordInput = document.getElementById('filePasswordInput');
            let password = passwordInput.value.trim();

            if (password === "") {
                alert("Please enter a password");
                return;
            }
            
            let url = window.fileUrl + "?password=" + encodeURIComponent(password);
            
            // Open the file in a new tab
            window.open(url, '_blank');

            // Clear the password field
            passwordInput.value = "";

            // Close the modal
            closeFileModal();
        }

        // Open and close password modal for download file and folder in current directory
        function openPasswordModal() {
            document.getElementById('passwordModal').style.display = 'flex ';
        }

        function closeModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
    </script>
</body>
</html>