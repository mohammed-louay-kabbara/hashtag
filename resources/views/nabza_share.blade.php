<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $nabza->title ?? 'نبذة' }}</title>
    <meta property="og:title" content="{{ $nabza->title }}">
    <meta property="og:description" content="{{ $nabza->description ?? '' }}">
    <meta property="og:image" content="{{ $nabza->files[0]->url ?? asset('default.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
</head>
<body style="font-family: sans-serif; text-align:center; padding:30px;">
    <h1>{{ $nabza->title }}</h1>
    <img src="{{ $nabza->files[0]->url ?? asset('default.png') }}" alt="Nabza" style="max-width:300px; border-radius:8px;">
    <p>{{ $nabza->description }}</p>

    <a href="intent://nabza/{{ $nabza->id }}#Intent;scheme=https;package=com.example.hashtag;end"
       style="display:inline-block; background:#007bff; color:white; padding:10px 20px; border-radius:6px; text-decoration:none;">
        افتح في التطبيق
    </a>
</body>
</html>
