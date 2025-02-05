<?php

namespace LeKoala\Crm;

/**
 * Validates and filters VAT numbers
 *
 * @link https://en.wikipedia.org/wiki/VAT_identification_number
 * @author LeKoala <thomas@lekoala.be>
 */
class VatHelper
{

    /**
     * Filters VAT Number
     *
     * @param string $id
     * @param bool $removePrefix
     * @return string
     */
    public static function filterNumber(string $id, bool $removePrefix = false)
    {
        if ($removePrefix) {
            $id = preg_replace('/[^0-9]/', '', $id);
        } else {
            $id = strtoupper($id);
            $id = preg_replace('/[ -,.]/', '', $id);
        }
        return $id;
    }

    /**
     * Return TRUE if supplied tax ID is valid for supplied country.
     *
     * @param string $id Taxation ID, e.g. ATU99999999 for Austria.
     * @param string $country Country code, e.g. AT
     * @returns bool
     */
    public static function isValid($id, $country = null)
    {
        $id = self::filterNumber($id);
        if (strlen($id) < 8) {
            return false;
        }
        if ($country === null) {
            $country = substr($id, 0, 2);
        }
        $country = strtoupper($country);
        switch ($country) {
            case 'AT': // AUSTRIA
                $isValid = (bool) preg_match('/^(AT)U(\d{8})$/', $id);
                break;
            case 'BE': // BELGIUM
                $isValid = (bool) preg_match('/(BE)(0?\d{9})$/', $id);
                break;
            case 'BG': // BULGARIA
                $isValid = (bool) preg_match('/(BG)(\d{9,10})$/', $id);
                break;
            case 'CHE': // Switzerland
                $isValid = (bool) preg_match('/(CHE)(\d{9})(MWST)?$/', $id);
                break;
            case 'CY': // CYPRUS
                $isValid = (bool) preg_match('/^(CY)([0-5|9]\d{7}[A-Z])$/', $id);
                break;
            case 'CZ': // CZECH REPUBLIC
                $isValid = (bool) preg_match('/^(CZ)(\d{8,10})(\d{3})?$/', $id);
                break;
            case 'DE': // GERMANY
                $isValid = (bool) preg_match('/^(DE)([1-9]\d{8})/', $id);
                break;
            case 'DK': // DENMARK
                $isValid = (bool) preg_match('/^(DK)(\d{8})$/', $id);
                break;
            case 'EE': // ESTONIA
                $isValid = (bool) preg_match('/^(EE)(10\d{7})$/', $id);
                break;
            case 'EL': // GREECE
                $isValid = (bool) preg_match('/^(EL)(\d{9})$/', $id);
                break;
            case 'ES': // SPAIN
                $isValid = (bool) preg_match('/^(ES)([A-Z]\d{8})$/', $id) || preg_match(
                    '/^(ES)([A-H|N-S|W]\d{7}[A-J])$/',
                    $id
                ) || preg_match('/^(ES)([0-9|Y|Z]\d{7}[A-Z])$/', $id)
                    || preg_match('/^(ES)([K|L|M|X]\d{7}[A-Z])$/', $id);
                break;
            case 'EU': // EU type
                $isValid = (bool) preg_match('/^(EU)(\d{9})$/', $id);
                break;
            case 'FI': // FINLAND
                $isValid = (bool) preg_match('/^(FI)(\d{8})$/', $id);
                break;
            case 'FR': // FRANCE
                $isValid = (bool) preg_match('/^(FR)(\d{11})$/', $id) || preg_match(
                    '/^(FR)([(A-H)|(J-N)|(P-Z)]\d{10})$/',
                    $id
                ) || preg_match(
                    '/^(FR)(\d[(A-H)|(J-N)|(P-Z)]\d{9})$/',
                    $id
                ) || preg_match(
                    '/^(FR)([(A-H)|(J-N)|(P-Z)]{2}\d{9})$/',
                    $id
                );
                break;
            case 'GB': // GREAT BRITAIN
                $isValid = (bool) preg_match('/^(GB)?(\d{9})$/', $id) || preg_match(
                    '/^(GB)?(\d{12})$/',
                    $id
                ) || preg_match('/^(GB)?(GD\d{3})$/', $id) || preg_match(
                    '/^(GB)?(HA\d{3})$/',
                    $id
                );
                break;
            case 'GR': // GREECE
                $isValid = (bool) preg_match('/^(GR)(\d{8,9})$/', $id);
                break;
            case 'HR': // CROATIA
                $isValid = (bool) preg_match('/^(HR)(\d{11})$/', $id);
                break;
            case 'HU': // HUNGARY
                $isValid = (bool) preg_match('/^(HU)(\d{8})$/', $id);
                break;
            case 'IE': // IRELAND
                $isValid = (bool) preg_match('/^(IE)(\d{7}[A-W])$/', $id) || preg_match(
                    '/^(IE)([7-9][A-Z\*\+)]\d{5}[A-W])$/',
                    $id
                ) || preg_match('/^(IE)(\d{7}[A-W][AH])$/', $id);
                break;
            case 'IT': // ITALY
                $isValid = (bool) preg_match('/^(IT)(\d{11})$/', $id);
                break;
            case 'LV': // LATVIA
                $isValid = (bool) preg_match('/^(LV)(\d{11})$/', $id);
                break;
            case 'LT': // LITHUNIA
                $isValid = (bool) preg_match('/^(LT)(\d{9}|\d{12})$/', $id);
                break;
            case 'LU': // LUXEMBOURG
                $isValid = (bool) preg_match('/^(LU)(\d{8})$/', $id);
                break;
            case 'MT': // MALTA
                $isValid = (bool) preg_match('/^(MT)([1-9]\d{7})$/', $id);
                break;
            case 'NL': // NETHERLAND
                $isValid = (bool) preg_match('/^(NL)(\d{9})B\d{2}$/', $id);
                break;
            case 'NO': // NORWAY
                $isValid = (bool) preg_match('/^(NO)(\d{9})$/', $id);
                break;
            case 'PL': // POLAND
                $isValid = (bool) preg_match('/^(PL)(\d{10})$/', $id);
                break;
            case 'PT': // PORTUGAL
                $isValid = (bool) preg_match('/^(PT)(\d{9})$/', $id);
                break;
            case 'RO': // ROMANIA
                $isValid = (bool) preg_match('/^(RO)([1-9]\d{1,9})$/', $id);
                break;
            case 'RS': // SERBIA
                $isValid = (bool) preg_match('/^(RS)(\d{9})$/', $id);
                break;
            case 'SI': // SLOVENIA
                $isValid = (bool) preg_match('/^(SI)([1-9]\d{7})$/', $id);
                break;
            case 'SK': // SLOVAK REPUBLIC
                $isValid = (bool) preg_match(
                    '/^(SK)([1-9]\d[(2-4)|(6-9)]\d{7})$/',
                    $id
                );
                break;
            case 'SE': // SWEDEN
                $isValid = (bool) preg_match('/^(SE)(\d{10}01)$/', $id);
                break;
            default:
                $isValid = true;
        }

        return $isValid;
    }
}
