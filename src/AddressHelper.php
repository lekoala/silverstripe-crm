<?php

namespace LeKoala\Crm;

class AddressHelper
{
    /**
     * This expects a format like My street, 111, something. , are optionals
     * @return array{street:string,num:string}
     */
    public static function splitAddress($st)
    {
        $st = $st ?? '';

        preg_match('/([\s|,][0-9]\s?[\w\.\/-]*$)/', $st, $matches);

        $num = $matches[0] ?? '';
        if ($num) {
            $street = str_replace($num, '', $st);
        } else {
            // Maybe the street starts with the number
            preg_match('/([0-9][\w\.\/-]*)\s/', $st, $matches);
            $num = $matches[0] ?? '';
            if ($num) {
                $street = str_replace($num, '', $st);
            }
        }
        return [
            'street' => trim($street, ' ,'),
            'num' => trim($num, ' ,'),
        ];
    }
}
