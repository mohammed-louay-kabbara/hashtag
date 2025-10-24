<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>نبذه</title>
  <meta property="og:title" content="نبذه">
  <meta property="og:description" content="{{ $nabza->stories->caption }}">
  <meta property="og:image" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="text-align:center; font-family:sans-serif;">
  <h2>{{ $nabza->title }}</h2>
  <p>{{ $nabza->description }}</p>
  <img src="{{ asset($nabza->image) }}" width="90%">
  
  <a href="http://alphaword.sy//nabza/{{ $nabza->id }}" 
     style="display:inline-block;padding:10px 20px;background:#007bff;color:white;border-radius:6px;text-decoration:none;margin-top:20px;">
     فتح في التطبيق
  </a>
</body>
</html>
