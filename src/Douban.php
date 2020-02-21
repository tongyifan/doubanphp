<?php


namespace Douban;

use Cake\Cache\Cache;

class Douban
{
    protected $config;

    private $sid;

    public $douban_id;
    public $imdb_id;
    public $imdb_rating;
    public $imdb_votes;
    public $douban_rating;
    public $douban_votes;

    public $chinese_title;
    public $foreign_title;
    public $aka;
    public $trans_title;
    public $this_title;

    public $year;
    public $region;
    public $genre;
    public $language;
    public $playdate;
    public $episodes;
    public $duration;
    public $awards;
    public $introduction;
    public $tags;
    public $poster;

    public $director;
    public $writer;
    public $cast;

    public $format;

    public $data;
    private $use_cache = false;


    public function __construct($sid, $source = null, $config = null)
    {
        if (is_null($config))
            $config = new Config();
        $this->config = $config;
        if (!Cache::configured())
            Cache::setConfig('default', $this->config->cache_config);

        if ($source !== null) {
            $source = strtolower($source);
            switch ($source) {
                case 'douban':
                    if (is_numeric($sid))
                        $this->sid = $sid;
                    else
                        throw new DoubanException("Parse douban ID error. $sid given.");
                    break;
                case 'imdb':
                    if (is_numeric($sid))
                        $this->sid = 'tt' . str_pad($sid, 7, STR_PAD_LEFT);
                    else if (preg_match("/(tt\d+)/", $sid, $matches))
                        $this->sid = $matches[1];
                    else
                        throw new DoubanException("Parse IMDb ID error. $sid given.");
                    break;
                default:
                    throw new DoubanException("Unknown sid source, expect 'douban' or 'imdb'");
            }
        } else {
            if (is_numeric($sid))
                $this->sid = $sid;
            else if (preg_match("/(tt\d+)/", $sid, $matches))
                $this->sid = $matches[1];
            else
                throw new DoubanException("Parse ID error. $sid given.");
        }

        $this->gen();
    }

    private function request($nocache = false)
    {
        if (($data = Cache::read($this->sid)) === false || $nocache || $this->config->debug) {
            $url = $this->config->api_endpoint . "?site=douban&sid=" . $this->sid . ($this->config->debug ? "&debug=1&nocache=" . rand() : "");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($response, true);

            if (!$data['success']) {
                if ($this->config->debug)
                    throw new DoubanException($data['debug']);
                else
                    throw new DoubanException($data['error']);
            }
            $this->data = $data;
        } else {
            $this->use_cache = true;
            if (is_numeric($data) && ($data = Cache::read($data)) === false)
                $data = $this->request(true);
            $this->data = $data;
        }

        return $data;
    }


    private function gen()
    {
        $data = $this->request();

        if (!is_numeric($data['sid'])) {
            if (preg_match('/movie.douban.com\/subject\/(\d+)\//', $data['format'], $matches))
                $this->douban_id = 0 + $matches[1];
        } else $this->douban_id = 0 + $data['sid'];
        $this->imdb_id = @$data['imdb_id'] ?: null;

        if (!is_null($this->imdb_id)) {
            if (preg_match("/([0-9]*\.?[0-9]+)\/10 from (\d+) users/", $data['imdb_rating'], $matches)) {
                $this->imdb_rating = 0 + $matches[1];
                $this->imdb_votes = 0 + $matches[2];
            }
        }
        $this->douban_rating = $data['douban_rating_average'];
        $this->douban_votes = 0 + str_replace(',', '', $data['douban_votes']);

        $this->chinese_title = $data['chinese_title'];
        $this->foreign_title = $data['foreign_title'] ?: @$data['trans_title'][0];
        $this->aka = @$data['aka'] ?: [];  // aka可能不存在
        $this->trans_title = $data['trans_title'];
        $this->this_title = $data['this_title'];

        $this->year = 0 + trim($data['year']);
        $this->region = $data['region'];
        $this->genre = $data['genre'];
        $this->language = $data['language'];
        $this->playdate = $data['playdate'];
        $this->episodes = $data['episodes'];
        $this->duration = $data['duration'];
        $this->awards = explode('\n', $data['awards']);
        $this->introduction = $data['introduction'];
        $this->tags = $data['tags'];
        $this->poster = $data['poster'];

        $this->director = preg_split('/(?:\s\/\s)|\n/', $data['director']);
        $this->writer = preg_split('/(?:\s\/\s)|\n/', $data['writer']);
        $this->cast = preg_split('/(?:\s\/\s)|\n/', $data['cast']);

        $this->format = $data['format'];

        if (!$this->use_cache) {
            if ($this->sid != $this->douban_id)
                Cache::add($this->sid, $this->douban_id);
            Cache::add($this->douban_id, $data);
        }
    }
}