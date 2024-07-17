<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>تهانينا ، تم قبول طلب التوظيف  </title>
</head>
<body>
    <h2>تهانينا ، تم قبول طلب التوظيف </h2>
    <p>@if ($user->gender==='male')

    عزيزي
    @else
    عزيزتي
    @endif
{{ $user->name }}
</p>
    <p>يسعدنا أن نبلغك بأن طلبك للتوظيف قد تم قبوله. نتطلع للتعاون معك في فريقنا.</p>
    <p>شكرًا لك،</p>
    <p>الإدارة</p>
</body>
</html>
