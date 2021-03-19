# image-spider
Simple script that allows you to find images in google

### Installing

```bash
composer require ivan-uskov/image-spider
```

### Usage

```php
<?php

use IvanUskov\ImageSpider\ImageSpider;

$imageUrls = ImageSpider::find('coffee');

```