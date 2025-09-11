<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiDiperpal</title>
    <link rel="stylesheet" href="{{ asset('styles/style.css') }}">
</head>

<body>
    <div class="container">
        <div class="hero-section">
            <h1>Selamat Datang di</h1>
            <img class="logo" src="{{ asset('images/logo.png') }}" alt="Logo">
            <p>Sistem Digitalisasi Dokumen Perencanaan, Keuangan dan Pelaporan DPMPD Kab. Landak</p>
            <a href="{{ route('filament.admin.auth.login') }}" class="btn-login">LOGIN</a>
        </div>
    </div>
</body>

</html>
