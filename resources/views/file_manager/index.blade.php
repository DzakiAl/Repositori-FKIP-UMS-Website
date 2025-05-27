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

    <!-- Custom Uploading Popup -->
    <div id="custom-upload-popup" style="display: none;">
        <div class="popup-overlay">
            <div class="popup-content">
                <div class="spinner"></div>
                <p>Uploading... Please wait.</p>
            </div>
        </div>
    </div>

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
                            <a href="{{ route('repository.file_manager', ['type' => $dataType, 'program' => basename($studyProgram)]) }}"
                                class="dropdown-item">
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
        <a href="{{ route('repository.index') }}" class="home-link">Home</a>
        @foreach ($data as $dataType => $studyPrograms)
            <div class="sidebar-dropdown">
                <p class="sidebar-toggle">{{ $dataType }}</p>
                <div class="sidebar-dropdown-menu">
                    @foreach ($studyPrograms as $studyProgram)
                        <a href="{{ route('repository.file_manager', ['type' => $dataType, 'program' => basename($studyProgram)]) }}"
                            class="dropdown-item">
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
            <form action="{{ route('repository.file_manager', ['type' => $type, 'program' => $program]) }}"
                method="GET" class="search-form">
                <input type="text" class="search-input" name="search" placeholder="Cari file atau folder"
                    value="{{ request('search') }}">
                <button type="submit" class="search-button">Search</button>
            </form>

            <div class="file-manager-option-container">
                @auth
                    {{-- Upload File Form --}}
                    <form
                        action="{{ route('repository.upload_file', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}"
                        method="POST" enctype="multipart/form-data" id="upload-form" style="display: none;">
                        @csrf
                        <input type="file" name="files[]" id="file-input" multiple>
                    </form>
                    <button id="upload-button" class="upload-button">Upload File</button>

                    {{-- Upload Folder Form --}}
                    <form
                        action="{{ route('repository.upload_folder', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}"
                        method="POST" enctype="multipart/form-data" id="upload-form">
                        @csrf
                        <input type="file" name="files[]" id="folder-input" webkitdirectory multiple
                            style="display: none;">
                    </form>
                    <button id="upload-folder-button" class="upload-button">Upload Folder</button>

                    {{-- Add Folder Form --}}
                    <form
                        action="{{ route('repository.add_folder', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}"
                        method="POST" style="display: flex">
                        @csrf
                        <input type="text" name="folder_name" id="folder_name" class="name-folder-input"
                            placeholder="Ketik nama folder yang ingin ditambahkan">
                        <button type="submit" class="add-folder-button">Add Folder</button>
                    </form>
                @endauth

                {{-- Download all file and folder in current directory --}}
                <a href="#" class="download-all-file-folder-button" onclick="openPasswordModal()">Download All
                    Files & Folders</a>
                <div id="passwordModal" class="modal-overlay">
                    <div class="modal-content">
                        <h3 class="modal-title">Enter Password to Download</h3>
                        <form id="downloadForm"
                            action="{{ route('repository.download_all', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}"
                            method="GET">
                            <input id="passwordInput" class="modal-input" type="password" name="password" required
                                placeholder="Enter download password">
                            <div class="modal-option">
                                <button class="modal-button" type="submit">Download</button>
                                <button type="button" class="modal-close-button"
                                    onclick="closeModal()">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="breadcrumbs">
                <a href="{{ route('repository.file_manager', ['type' => $type, 'program' => $program]) }}">Root</a>
                @foreach ($breadcrumbs as $index => $crumb)
                    / <a
                        href="{{ route('repository.file_manager', ['type' => $type, 'program' => $program, 'subfolder' => implode('/', array_slice($breadcrumbs, 0, $index + 1))]) }}">{{ $crumb }}</a>
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
                                <a
                                    href="{{ route('repository.file_manager', ['type' => $type, 'program' => $program, 'subfolder' => isset($subfolder) ? "$subfolder/$folder" : $folder]) }}">
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
                                        <form
                                            action="{{ route('repository.download_folder', ['type' => $type, 'program' => $program, 'subfolder' => isset($subfolder) ? "$subfolder/$folder" : $folder]) }}"
                                            method="POST">
                                            @csrf
                                            <input id="passwordInput" class="modal-input" type="password"
                                                name="password" required placeholder="Enter download password">
                                            <div class="modal-option">
                                                <button class="modal-button" type="submit">Download</button>
                                                <button type="button" class="modal-close-button"
                                                    onclick="closeModal()">Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @auth
                                    <a href="#"
                                        onclick="renameItem('{{ $folder }}', '{{ route('repository.rename', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder]) }}')">Rename</a>
                                    <a href="{{ route('repository.delete_folder', ['type' => $type, 'program' => $program, 'folder' => isset($subfolder) ? "$subfolder/$folder" : $folder]) }}"
                                        onclick="return confirm('Are you sure? (This is action cannot be undone)')">Delete</a>
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
                                <a href="#" class="file-folder-name"
                                    onclick="showFilePasswordModal('{{ route('repository.open_file', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder, 'file' => $file['name']]) }}')">
                                    {{ $file['name'] }}
                                </a>

                                <div id="filePasswordModal" class="file-modal-overlay">
                                    <div class="file-modal-content">
                                        <h3 class="file-modal-title">Enter Password to View File</h3>
                                        <input id="filePasswordInput" class="file-modal-input" type="password"
                                            required placeholder="Enter file password">
                                        <div class="file-modal-option">
                                            <button class="file-modal-button" onclick="submitFilePassword()">View
                                                File</button>
                                            <button type="button" class="file-modal-close-button"
                                                onclick="closeFileModal()">Close</button>
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
                                        <form
                                            action="{{ route('repository.download_file', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder, 'file' => $file['name']]) }}"
                                            method="GET">
                                            <input id="passwordInput" class="modal-input" type="password"
                                                name="password" required placeholder="Enter download password">
                                            <div class="modal-option">
                                                <button class="modal-button" type="submit">Download</button>
                                                <button type="button" class="modal-close-button"
                                                    onclick="closeModal()">Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @auth
                                    <a href="#"
                                        onclick="renameItem('{{ $file['name'] }}', '{{ route('repository.rename', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder]) }}')">Rename</a>
                                    <a href="{{ route('repository.delete_file', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder, 'file' => $file['name']]) }}"
                                        onclick="return confirm('Are you sure? (This is action cannot be undone)')">Delete</a>
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
        document.addEventListener("DOMContentLoaded", function() {
            // === Upload File ===
            const uploadButton = document.getElementById('upload-button');
            const fileInput = document.getElementById('file-input');
            const uploadForm = document.getElementById('upload-form');

            if (uploadButton && fileInput && uploadForm) {
                uploadButton.addEventListener('click', () => fileInput.click());

                fileInput.addEventListener('change', () => {
                    if (fileInput.files.length > 0) {
                        document.getElementById('custom-upload-popup').style.display = "block";
                        uploadForm.submit(); // Regular form submit for file upload
                    }
                });
            }

            // === Upload Folder ===
            const uploadFolderButton = document.getElementById('upload-folder-button');
            const folderInput = document.getElementById('folder-input');

            if (uploadFolderButton && folderInput) {
                uploadFolderButton.addEventListener('click', () => folderInput.click());

                folderInput.addEventListener('change', function(event) {
                    if (folderInput.files.length > 0) {
                        document.getElementById('custom-upload-popup').style.display = "block";

                        let formData = new FormData();
                        for (let file of event.target.files) {
                            formData.append('files[]', file);
                            formData.append('paths[]', file.webkitRelativePath); // Keep relative path
                        }

                        fetch("{{ route('repository.upload_folder', ['type' => $type, 'program' => $program, 'subfolder' => $subfolder ?? '']) }}", {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('custom-upload-popup').style.display = 'none';

                                if (!data.error) {
                                    // Show success popup
                                    const successPopup = document.createElement('div');
                                    successPopup.className = 'popup-container';
                                    successPopup.style.position = 'fixed';
                                    successPopup.style.top = '20px';
                                    successPopup.style.right = '20px';
                                    successPopup.style.padding = '10px 20px';
                                    successPopup.style.backgroundColor = '#4caf50';
                                    successPopup.style.color = 'white';
                                    successPopup.style.borderRadius = '8px';
                                    successPopup.style.zIndex = '1000';
                                    successPopup.innerText = 'Folder uploaded successfully.';

                                    document.body.appendChild(successPopup);

                                    // Wait 2 seconds before removing popup and reloading
                                    setTimeout(() => {
                                        successPopup.remove();
                                        location.reload();
                                    }, 2000);

                                } else {
                                    // Show error popup
                                    const errorPopup = document.createElement('div');
                                    errorPopup.className = 'popup-container';
                                    errorPopup.style.position = 'fixed';
                                    errorPopup.style.top = '20px';
                                    errorPopup.style.right = '20px';
                                    errorPopup.style.padding = '10px 20px';
                                    errorPopup.style.backgroundColor = '#f44336'; // red
                                    errorPopup.style.color = 'white';
                                    errorPopup.style.borderRadius = '8px';
                                    errorPopup.style.zIndex = '1000';
                                    errorPopup.innerText = 'Something went wrong.';

                                    document.body.appendChild(errorPopup);

                                    // Remove error popup after 3 seconds
                                    setTimeout(() => {
                                        errorPopup.remove();
                                        location.reload();
                                    }, 3000);
                                }
                            })
                            .catch(error => {
                                document.getElementById('custom-upload-popup').style.display = 'none';
                                console.error('Upload error:', error);

                                // Show error popup for network or other errors
                                const errorPopup = document.createElement('div');
                                errorPopup.className = 'popup-container';
                                errorPopup.style.position = 'fixed';
                                errorPopup.style.top = '20px';
                                errorPopup.style.right = '20px';
                                errorPopup.style.padding = '10px 20px';
                                errorPopup.style.backgroundColor = '#f44336'; // red
                                errorPopup.style.color = 'white';
                                errorPopup.style.borderRadius = '8px';
                                errorPopup.style.zIndex = '1000';
                                errorPopup.innerText = 'Something went wrong.';

                                document.body.appendChild(errorPopup);

                                setTimeout(() => {
                                    errorPopup.remove();
                                    location.reload();
                                }, 3000);
                            });

                    }
                });
            }

            // === Auto Close Any Flash Popup After 3s ===
            setTimeout(() => {
                const popup = document.querySelector('.popup-container');
                if (popup) popup.style.display = 'none';
            }, 3000);

            // === Download with Password Modal ===
            document.querySelectorAll('.download-file').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const modal = this.closest('tr').querySelector('.modal-overlay');
                    if (modal) modal.style.display = 'flex';
                });
            });

            // === Close Download Modal ===
            document.querySelectorAll('.modal-close-button').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    const modal = this.closest('.modal-overlay');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.querySelector('.modal-input').value = '';
                    }
                });
            });

            window.addEventListener('click', function(e) {
                document.querySelectorAll('.modal-overlay').forEach(modal => {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                        modal.querySelector('.modal-input').value = '';
                    }
                });
            });

            // === Toggle Sidebar ===
            document.querySelectorAll('.sidebar-toggle').forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const dropdown = toggle.nextElementSibling;
                    if (dropdown) dropdown.classList.toggle('show');
                });
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // === Rename Item ===
        function renameItem(oldName, renameUrl) {
            const newName = prompt("Enter the new name:", oldName);
            if (newName && newName !== oldName) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = renameUrl;

                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="old_name" value="${oldName}">
                    <input type="hidden" name="new_name" value="${newName}">
                `;

                document.body.appendChild(form);
                form.submit();
            }
        }

        // === File View Modal with Password ===
        function showFilePasswordModal(fileUrl) {
            window.fileUrl = fileUrl;
            document.getElementById('filePasswordModal').style.display = 'flex';
        }

        function closeFileModal() {
            document.getElementById('filePasswordModal').style.display = 'none';
            document.getElementById('filePasswordInput').value = '';
        }

        function submitFilePassword() {
            const input = document.getElementById('filePasswordInput');
            const password = input.value.trim();

            if (!password) {
                alert("Please enter a password");
                return;
            }

            const finalUrl = `${window.fileUrl}?password=${encodeURIComponent(password)}`;
            window.open(finalUrl, '_blank');
            closeFileModal();
        }

        // === Modal for Downloading Folders with Password ===
        function openPasswordModal() {
            document.getElementById('passwordModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
    </script>
</body>
</html>