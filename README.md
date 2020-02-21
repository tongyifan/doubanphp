# DoubanPHP
Library for parsing metadata information about movies and TV shows from Douban Movie. 
This library use data api from [Rhilip/pt-gen-cfworker](https://github.com/Rhilip/pt-gen-cfworker), cache included.

0. Setup your own pt-gen on Cloudflare worker
See [Rhilip/pt-gen-cfworker](https://github.com/Rhilip/pt-gen-cfworker). Then change `api_endpoint` in `conf/config.ini` to your own cfworker.

1. Requirements
* PHP >= 5.6
* PHP cURL extension
* PHP JSON extension
2. Install
```bash
composer install tongyifan/doubanphp
```
3. Usage
```php
<?php
require 'vendor/autoload.php';

use Douban\Douban;

$douban_id = '30458442';
$douban = new Douban($douban_id);

$douban_rating = $douban->douban_rating;
```
4. Advanced usage
* You can change cache settings by changing `conf/config.ini`.
* Or change it in your application.
```php
<?php
require 'vendor/autoload.php';

use Douban\Douban;
use Douban\Config;

$config = new Config();
// see https://book.cakephp.org/3/en/core-libraries/caching.html for more information.
$config->cache_config = [
    'className' => 'Redis',
    'duration' => '+14 days',
    'prefix' => 'doubanphp_',
    'host' => '127.0.0.1',
    'port' => 6379
];

$imdb_id = 'tt11043632';
$douban = new Douban($imdb_id, null, $config);
$douban_rating = $douban->douban_rating;

// or use numeric IMDb id, but you should add "source".
$imdb_id = '11043632';
$douban = new Douban($imdb_id, 'imdb', $config);
$douban_rating = $douban->douban_rating;
```