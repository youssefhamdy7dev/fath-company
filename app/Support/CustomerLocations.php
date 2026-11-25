<?php

namespace App\Support;

class CustomerLocations
{
    /**
     * Return all supported customer locations in their original order.
     */
    public static function all(): array
    {
        return [
            'ميدان صقر',
            'الكويتية',
            'صقر',
            'العمومى بساتين',
            'البساتين',
            'سوق البساتين',
            'السد العالى',
            'ميدان أبو عبده',
            'على عبدالعزيز',
            'أبو بريك',
            'المطبعة',
            'الجزيرة',
            'دار السلام',
            'البير',
            'المشير وأبو الوفا',
            'عبدالحميد مكى',
            'فايدة كامل',
            'حسنين الدسوقى',
            'المعادى',
            'أخرى',
        ];
    }

    /**
     * Return the original location groups and their locations.
     */
    public static function places(): array
    {
        return [
            'صقر والبساتين' => [
                'ميدان صقر',
                'الكويتية',
                'صقر',
                'العمومى بساتين',
                'البساتين',
                'سوق البساتين',
            ],
            'السد العالى' => [
                'السد العالى',
                'ميدان أبو عبده',
                'على عبدالعزيز',
                'أبو بريك',
            ],
            'الجزيرة ودار السلام' => [
                'المطبعة',
                'الجزيرة',
                'دار السلام',
            ],
            'فايدة كامل وحسنين الدسوقى' => [
                'البير',
                'المشير وأبو الوفا',
                'عبدالحميد مكى',
                'فايدة كامل',
                'حسنين الدسوقى',
                'المعادى',
            ],
            'أخرى' => [
                'أخرى',
            ],
        ];
    }

    /**
     * Map each location to its corresponding place group.
     *
     * When selected group keys are supplied, only those groups
     * are included in the resulting map.
     */
    public static function locationToPlace(?array $selectedPlaceKeys = null): array
    {
        $places = self::places();

        $selectedPlaceKeys ??= array_keys($places);

        $locationToPlace = [];

        foreach ($selectedPlaceKeys as $placeKey) {
            if (!isset($places[$placeKey])) {
                continue;
            }

            foreach ($places[$placeKey] as $location) {
                $locationToPlace[$location] = $placeKey;
            }
        }

        return $locationToPlace;
    }
}
