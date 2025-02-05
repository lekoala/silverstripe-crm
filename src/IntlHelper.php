<?php

namespace LeKoala\Crm;

use SilverStripe\i18n\Data\Intl\IntlLocales;

/**
 * Wrapper around IntlLocales from SilverStripe
 * @author Koala
 */
class IntlHelper
{
    /**
     * Get the country list, using IntlLocales
     *
     * Keys are set to uppercase to match ISO standards
     *
     * @return array<string,string>
     */
    public static function getCountries()
    {
        $intl = new IntlLocales;
        $countries = $intl->getCountries();
        $countries = array_change_key_case($countries, CASE_UPPER);
        return $countries;
    }

    /**
     * Get the locales list, using IntlLocales
     *
     * @return array<string,string>
     */
    public static function getLocales()
    {
        $intl = new IntlLocales;
        $locales = $intl->getLocales();
        return $locales;
    }

    /**
     * Get the languages list, using IntlLocales
     *
     * @return array<string,string>
     */
    public static function getLanguages()
    {
        $intl = new IntlLocales;
        $locales = $intl->getLanguages();
        return $locales;
    }

    /**
     * @param string $code
     * @return string
     */
    public static function getCountryNameFromCode($code)
    {
        $list = self::getCountries();
        if (isset($list[$code])) {
            return $list[$code];
        }
        return $code;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getCountryCodeFromName($name)
    {
        $list = array_flip(self::getCountries());
        if (isset($list[$name])) {
            return $list[$name];
        }
        return strtoupper(substr($name, 0, 2));
    }
}
