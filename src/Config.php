<?php


namespace Douban;


class Config
{
    public $api_endpoint = "http://your-ptgen-route.workers.dev/";

    public $debug = false;

    public $cache_config = [
        'className' => 'File',
        'duration' => '+14 days',
        'path' => './cache/',
        'prefix' => 'douban_'
    ];

    public function __construct($ini_file_path = null)
    {
        if ($ini_file_path)
            $ini_files = [$ini_file_path];
        else
            $ini_files = glob(dirname(__FILE__) . '/../conf/*.ini');

        foreach ($ini_files as $file) {
            $ini = parse_ini_file($file, true);
            if (array_key_exists('general', $ini)) {
                foreach ($ini['general'] as $key => $val)
                    $this->$key = $val;
            }

            if (array_key_exists('cache', $ini)) {
                foreach ($ini['cache'] as $key => $val)
                    $this->cache_config[$key] = $val;
            }
        }

        if ($this->cache_config['path'] == './cache/') {
            $this->cache_config['path'] = dirname(__FILE__) . '/../cache/';
        }
    }
}
