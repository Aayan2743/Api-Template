<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
        <p>Hello {{ $data['user_data']->name }},</p>
        <p>Your OTP {{ $data['otp'] }},</p>
        <p>Expired Time {{ $data['exp'] }},</p>
       
        <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>