<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    @vite(['resources/css/login.css', 'resources/js/app.js'])
</head>
<body>
    <div class="content">
        <form action="{{ route('login') }}" method="POST" class="form">
            @csrf
            <h1 class="title">Log In</h1>
            <p class="label">Username:</p>
            <input type="text" class="input" name="username" placeholder="Masukkan username">
            <p class="label">Password:</p>
            <input type="password" class="input" name="password" placeholder="Masukkan password">
            @if ($errors->any())
                <p class="error">{{ $errors->first() }}</p>
            @endif
            <div class="option">
                <button type="submit" class="button">Login</button>
            </div>
        </form>        
    </div>
</body>
</html>