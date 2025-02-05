<?php

namespace LeKoala\Crm\Tests;

use LeKoala\Crm\AddressHelper;
use SilverStripe\Dev\SapphireTest;

class CrmTest extends SapphireTest
{
    public function testItWorks(): void
    {
        $this->assertTrue(true);
    }

    public function testAddressSplitter(): void
    {
        $arr = [
            'Rue du test 1' => [
                'street' => 'Rue du test',
                'num' => '1'
            ],
            'Rue du test      1' => [
                'street' => 'Rue du test',
                'num' => '1'
            ],
            'Rue du test 12' => [
                'street' => 'Rue du test',
                'num' => '12'
            ],
            'Rue du test 12ABC' => [
                'street' => 'Rue du test',
                'num' => '12ABC'
            ],
            'Rue du test, 12ABC' => [
                'street' => 'Rue du test',
                'num' => '12ABC'
            ],
            'Rue du 15 test, 12ABC' => [
                'street' => 'Rue du 15 test',
                'num' => '12ABC'
            ],
            '12 Rue du test' => [
                'street' => 'Rue du test',
                'num' => '12'
            ],
        ];
        foreach ($arr as $test => $expected) {
            $this->assertEquals($expected, AddressHelper::splitAddress($test), "Address was $test");
        }
    }
}
