# Leafiagram
まだ他人のpostにハート付けるapiしか叩けませんてへぺろ<br>
<br>
```php
require 'Leafiagram.php';

$ig = new Leafiagram("your username","your password");
$ig->login();

$media_id = "1306575142667629143_2633241191";
$ig->addLike($media_id);
```