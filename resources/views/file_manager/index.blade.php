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
    {{-- Navbar --}}
    @vite(['resources/css/navbar.css', 'resources/js/app.js'])
    <nav class="navbar">
        <img src="{{ asset('assets/logo_ums.png') }}" class="logo" alt="UMS Logo">
        <div class="menu">
            @foreach ($data as $dataType => $studyPrograms)
                <div class="dropdown">
                    <p class="dropdown-toggle">{{ $dataType }}</p>
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
    </nav>

    {{-- Content --}}
    <div class="content">
        <div class="title-container">
            <h1 class="title">Repositori {{ $type }} Prodi {{ $program }}</h1>
        </div>
        <div class="file-manager-container">
            <form action="" class="search-form">
                <input type="text" class="search-input" name="search" placeholder="Cari file atau folder">
                <button class="search-button">Search</button>
            </form>

            <div class="option-container">
                {{-- Upload File Form --}}
                <form action="{{ route('repository.upload_file', ['type' => $type, 'program' => $program]) }}" method="POST" enctype="multipart/form-data" id="upload-form" style="display: none;">
                    @csrf
                    <input type="file" name="file" id="file-input">
                </form>
                <button id="upload-button" class="upload-button">Upload File</button>

                {{-- Add Folder Form --}}
                <form action="{{ route('repository.add_folder', ['type' => $type, 'program' => $program]) }}"
                    method="POST" style="display: flex">
                    @csrf
                    <input type="text" name="folder_name" id="folder_name" class="name-folder-input" placeholder="Ketik nama folder yang ingin ditambahkan">
                    <button type="submit" class="add-folder-button">Add Folder</button>
                </form>
            </div>


            {{-- File and Folder List --}}
            <table class="file-folder-list-container">
                <thead>
                    <tr>
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
                            <td><strong>{{ $folder }}</strong></td>
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
                            <td>{{ $file['name'] }}</td>
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
        document.getElementById('upload-button').addEventListener('click', function() {
            document.getElementById('file-input').click();
        });

        document.getElementById('file-input').addEventListener('change', function() {
            document.getElementById('upload-form').submit();
        });
    </script>
</body>
</html>