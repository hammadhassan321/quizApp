<!doctype html>
<html>
<head>
     <link rel="stylesheet" href="/css/quiz.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container"> 
        <h2>Enter your name to start</h2>
        <form method="POST" action="/start">
            @csrf
            <input type="text" name="name" required maxlength="150" placeholder="Your name">
            <button type="submit">Start</button>
        </form>
    </div>
</body>
</html>
