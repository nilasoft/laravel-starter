<!doctype html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Exception</title>
    @include('exceptions.gateways.components.styles')
    @include('exceptions.gateways.components.jquery')
</head>
<body>

<div class="loading-page">
    <div class="counter justify-items-center">
        <p>Error!</p>
        <h1>{{ $error }}</h1>
        <hr/>
    </div>
</div>

</body>
</html>
