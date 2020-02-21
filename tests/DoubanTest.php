<?php

namespace Douban;

use PHPUnit\Framework\TestCase;

class DoubanTest extends TestCase
{
    public function testDouban()
    {
        $tests = [
            // Random choose from https://github.com/bimzcy/rank4douban/blob/master/data/01_IMDbtop250.csv
            'tt0111161',
            'tt0119698',
            'tt0361748',
            'tt0211915',
            'tt0080678',
            '25765735',
            '10741643',
            '1309115',
            '1293181',
            '1293838',
            '6307447',
            // Random choose from https://github.com/bimzcy/rank4douban/blob/master/data/06_Bangumitop250.csv
            '1401536',
            '24460604',
            '26206746',
            '1477916',
            '7064681'
        ];
        foreach ($tests as $id) {
            try {
                $douban = new Douban($id);

                $this->assertInternalType('int', $douban->douban_id);
                if (!is_null($douban->imdb_id)) {
                    $this->assertStringStartsWith('tt', $douban->imdb_id);
                    $this->assertLessThan(10, $douban->imdb_rating);
                    $this->assertInternalType('int', $douban->imdb_votes);
                }
                $this->assertLessThan(10, $douban->douban_rating);
                $this->assertInternalType('int', $douban->douban_votes);

                $this->assertNotNull($douban->chinese_title);
                $this->assertNotNull($douban->foreign_title);
                $this->assertInternalType('array', $douban->aka);
                $this->assertInternalType('array', $douban->trans_title);
                $this->assertInternalType('array', $douban->this_title);

                $this->assertGreaterThan(1900, $douban->year);
                $this->assertInternalType('array', $douban->region);
                $this->assertInternalType('array', $douban->genre);
                $this->assertInternalType('array', $douban->language);
                $this->assertInternalType('array', $douban->playdate);
                $this->assertInternalType('string', $douban->episodes);
                $this->assertInternalType('string', $douban->duration);
                $this->assertInternalType('array', $douban->awards);
                $this->assertInternalType('string', $douban->introduction);
                $this->assertInternalType('array', $douban->tags);
                $this->assertStringMatchesFormat('https://img%d.doubanio.com/view/photo/l_ratio_poster/public/p%d.jpg', $douban->poster);

                $this->assertInternalType('array', $douban->director);
                $this->assertInternalType('array', $douban->writer);
                $this->assertInternalType('array', $douban->cast);

                $this->assertArrayHasKey('version', $douban->data);
            } catch (DoubanException $e) {
                print("$id: $e\n");
            }
        }
    }
}
