<?php

namespace App\Helpers;

class CountryHelper
{
    public static function getAllCountries(): array
    {
        return [
            ['code' => 'US', 'name' => 'United States', 'flag' => '🇺🇸'],
            ['code' => 'GB', 'name' => 'United Kingdom', 'flag' => '🇬🇧'],
            ['code' => 'CA', 'name' => 'Canada', 'flag' => '🇨🇦'],
            ['code' => 'AU', 'name' => 'Australia', 'flag' => '🇦🇺'],
            ['code' => 'DE', 'name' => 'Germany', 'flag' => '🇩🇪'],
            ['code' => 'FR', 'name' => 'France', 'flag' => '🇫🇷'],
            ['code' => 'IT', 'name' => 'Italy', 'flag' => '🇮🇹'],
            ['code' => 'ES', 'name' => 'Spain', 'flag' => '🇪🇸'],
            ['code' => 'NL', 'name' => 'Netherlands', 'flag' => '🇳🇱'],
            ['code' => 'BE', 'name' => 'Belgium', 'flag' => '🇧🇪'],
            ['code' => 'CH', 'name' => 'Switzerland', 'flag' => '🇨🇭'],
            ['code' => 'SE', 'name' => 'Sweden', 'flag' => '🇸🇪'],
            ['code' => 'NO', 'name' => 'Norway', 'flag' => '🇳🇴'],
            ['code' => 'DK', 'name' => 'Denmark', 'flag' => '🇩🇰'],
            ['code' => 'FI', 'name' => 'Finland', 'flag' => '🇫🇮'],
            ['code' => 'RU', 'name' => 'Russia', 'flag' => '🇷🇺'],
            ['code' => 'CN', 'name' => 'China', 'flag' => '🇨🇳'],
            ['code' => 'JP', 'name' => 'Japan', 'flag' => '🇯🇵'],
            ['code' => 'KR', 'name' => 'South Korea', 'flag' => '🇰🇷'],
            ['code' => 'IN', 'name' => 'India', 'flag' => '🇮🇳'],
            ['code' => 'BD', 'name' => 'Bangladesh', 'flag' => '🇧🇩'],
            ['code' => 'PK', 'name' => 'Pakistan', 'flag' => '🇵🇰'],
            ['code' => 'AE', 'name' => 'United Arab Emirates', 'flag' => '🇦🇪'],
            ['code' => 'SA', 'name' => 'Saudi Arabia', 'flag' => '🇸🇦'],
            ['code' => 'BR', 'name' => 'Brazil', 'flag' => '🇧🇷'],
            ['code' => 'MX', 'name' => 'Mexico', 'flag' => '🇲🇽'],
            ['code' => 'AR', 'name' => 'Argentina', 'flag' => '🇦🇷'],
            ['code' => 'CL', 'name' => 'Chile', 'flag' => '🇨🇱'],
            ['code' => 'CO', 'name' => 'Colombia', 'flag' => '🇨🇴'],
            ['code' => 'ZA', 'name' => 'South Africa', 'flag' => '🇿🇦'],
            ['code' => 'NG', 'name' => 'Nigeria', 'flag' => '🇳🇬'],
            ['code' => 'EG', 'name' => 'Egypt', 'flag' => '🇪🇬'],
            ['code' => 'TR', 'name' => 'Turkey', 'flag' => '🇹🇷'],
            ['code' => 'SG', 'name' => 'Singapore', 'flag' => '🇸🇬'],
            ['code' => 'MY', 'name' => 'Malaysia', 'flag' => '🇲🇾'],
            ['code' => 'TH', 'name' => 'Thailand', 'flag' => '🇹🇭'],
            ['code' => 'ID', 'name' => 'Indonesia', 'flag' => '🇮🇩'],
            ['code' => 'VN', 'name' => 'Vietnam', 'flag' => '🇻🇳'],
            ['code' => 'PH', 'name' => 'Philippines', 'flag' => '🇵🇭'],
            ['code' => 'NZ', 'name' => 'New Zealand', 'flag' => '🇳🇿'],
            ['code' => 'IE', 'name' => 'Ireland', 'flag' => '🇮🇪'],
            ['code' => 'AT', 'name' => 'Austria', 'flag' => '🇦🇹'],
            ['code' => 'PL', 'name' => 'Poland', 'flag' => '🇵🇱'],
            ['code' => 'PT', 'name' => 'Portugal', 'flag' => '🇵🇹'],
            ['code' => 'GR', 'name' => 'Greece', 'flag' => '🇬🇷'],
            ['code' => 'CZ', 'name' => 'Czech Republic', 'flag' => '🇨🇿'],
            ['code' => 'RO', 'name' => 'Romania', 'flag' => '🇷🇴'],
            ['code' => 'HU', 'name' => 'Hungary', 'flag' => '🇭🇺'],
            ['code' => 'UA', 'name' => 'Ukraine', 'flag' => '🇺🇦'],
            ['code' => 'IL', 'name' => 'Israel', 'flag' => '🇮🇱'],
            ['code' => 'QA', 'name' => 'Qatar', 'flag' => '🇶🇦'],
            ['code' => 'KW', 'name' => 'Kuwait', 'flag' => '🇰🇼'],
            ['code' => 'HK', 'name' => 'Hong Kong', 'flag' => '🇭🇰'],
            ['code' => 'TW', 'name' => 'Taiwan', 'flag' => '🇹🇼'],
            ['code' => 'CL', 'name' => 'Chile', 'flag' => '🇨🇱'],
            ['code' => 'PE', 'name' => 'Peru', 'flag' => '🇵🇪'],
            ['code' => 'KZ', 'name' => 'Kazakhstan', 'flag' => '🇰🇿'],
            ['code' => 'UZ', 'name' => 'Uzbekistan', 'flag' => '🇺🇿'],
            ['code' => 'AZ', 'name' => 'Azerbaijan', 'flag' => '🇦🇿'],
            ['code' => 'GE', 'name' => 'Georgia', 'flag' => '🇬🇪'],
            ['code' => 'AM', 'name' => 'Armenia', 'flag' => '🇦🇲'],
            ['code' => 'BY', 'name' => 'Belarus', 'flag' => '🇧🇾'],
            ['code' => 'BG', 'name' => 'Bulgaria', 'flag' => '🇧🇬'],
            ['code' => 'HR', 'name' => 'Croatia', 'flag' => '🇭🇷'],
            ['code' => 'RS', 'name' => 'Serbia', 'flag' => '🇷🇸'],
            ['code' => 'SK', 'name' => 'Slovakia', 'flag' => '🇸🇰'],
            ['code' => 'SI', 'name' => 'Slovenia', 'flag' => '🇸🇮'],
            ['code' => 'LT', 'name' => 'Lithuania', 'flag' => '🇱🇹'],
            ['code' => 'LV', 'name' => 'Latvia', 'flag' => '🇱🇻'],
            ['code' => 'EE', 'name' => 'Estonia', 'flag' => '🇪🇪'],
            ['code' => 'IS', 'name' => 'Iceland', 'flag' => '🇮🇸'],
            ['code' => 'LU', 'name' => 'Luxembourg', 'flag' => '🇱🇺'],
            ['code' => 'CY', 'name' => 'Cyprus', 'flag' => '🇨🇾'],
            ['code' => 'MT', 'name' => 'Malta', 'flag' => '🇲🇹'],
            ['code' => 'MA', 'name' => 'Morocco', 'flag' => '🇲🇦'],
            ['code' => 'KE', 'name' => 'Kenya', 'flag' => '🇰🇪'],
            ['code' => 'GH', 'name' => 'Ghana', 'flag' => '🇬🇭'],
            ['code' => 'CR', 'name' => 'Costa Rica', 'flag' => '🇨🇷'],
            ['code' => 'PA', 'name' => 'Panama', 'flag' => '🇵🇦'],
            ['code' => 'DO', 'name' => 'Dominican Republic', 'flag' => '🇩🇴'],
            ['code' => 'PR', 'name' => 'Puerto Rico', 'flag' => '🇵🇷'],
            ['code' => 'EC', 'name' => 'Ecuador', 'flag' => '🇪🇨'],
            ['code' => 'UY', 'name' => 'Uruguay', 'flag' => '🇺🇾'],
            ['code' => 'VE', 'name' => 'Venezuela', 'flag' => '🇻🇪'],
        ];
    }

    public static function getFlag(string $code): string
    {
        $code = strtoupper(trim($code));
        $flags = [
            'US' => '🇺🇸', 'GB' => '🇬🇧', 'CA' => '🇨🇦', 'AU' => '🇦🇺',
            'DE' => '🇩🇪', 'FR' => '🇫🇷', 'IT' => '🇮🇹', 'ES' => '🇪🇸',
            'NL' => '🇳🇱', 'BE' => '🇧🇪', 'CH' => '🇨🇭', 'SE' => '🇸🇪',
            'NO' => '🇳🇴', 'DK' => '🇩🇰', 'FI' => '🇫🇮', 'RU' => '🇷🇺',
            'CN' => '🇨🇳', 'JP' => '🇯🇵', 'KR' => '🇰🇷', 'IN' => '🇮🇳',
            'BD' => '🇧🇩', 'PK' => '🇵🇰', 'AE' => '🇦🇪', 'SA' => '🇸🇦',
            'BR' => '🇧🇷', 'MX' => '🇲🇽', 'AR' => '🇦🇷', 'CL' => '🇨🇱',
            'CO' => '🇨🇴', 'ZA' => '🇿🇦', 'NG' => '🇳🇬', 'EG' => '🇪🇬',
            'TR' => '🇹🇷', 'SG' => '🇸🇬', 'MY' => '🇲🇾', 'TH' => '🇹🇭',
            'ID' => '🇮🇩', 'VN' => '🇻🇳', 'PH' => '🇵🇭', 'NZ' => '🇳🇿',
            'IE' => '🇮🇪', 'AT' => '🇦🇹', 'PL' => '🇵🇱', 'PT' => '🇵🇹',
            'GR' => '🇬🇷', 'CZ' => '🇨🇿', 'RO' => '🇷🇴', 'HU' => '🇭🇺',
            'UA' => '🇺🇦', 'IL' => '🇮🇱', 'QA' => '🇶🇦', 'KW' => '🇰🇼',
            'HK' => '🇭🇰', 'TW' => '🇹🇼', 'KZ' => '🇰🇿', 'UZ' => '🇺🇿',
            'AZ' => '🇦🇿', 'GE' => '🇬🇪', 'AM' => '🇦🇲', 'BY' => '🇧🇾',
            'BG' => '🇧🇬', 'HR' => '🇭🇷', 'RS' => '🇷🇸', 'SK' => '🇸🇰',
            'SI' => '🇸🇮', 'LT' => '🇱🇹', 'LV' => '🇱🇻', 'EE' => '🇪🇪',
            'IS' => '🇮🇸', 'LU' => '🇱🇺', 'CY' => '🇨🇾', 'MT' => '🇲🇹',
            'MA' => '🇲🇦', 'KE' => '🇰🇪', 'GH' => '🇬🇭', 'CR' => '🇨🇷',
            'PA' => '🇵🇦', 'DO' => '🇩🇴', 'PR' => '🇵🇷', 'EC' => '🇪🇨',
            'UY' => '🇺🇾', 'VE' => '🇻🇪',
        ];

        return $flags[$code] ?? '🌐';
    }

    public static function getCountryName(string $code): string
    {
        $code = strtoupper(trim($code));
        $countries = self::getAllCountries();
        foreach ($countries as $c) {
            if ($c['code'] === $code) {
                return $c['name'];
            }
        }
        return 'United States';
    }
}
