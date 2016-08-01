# Leafiagram
いくつかのAPIが使えます<br>
<br>
##example .
```php
require 'Leafiagram.php';

$ig = new Leafiagram("your username","your password");
$ig->login();

$media_id = "1306575142667629143_2633241191";
$ig->addLike($media_id);

```