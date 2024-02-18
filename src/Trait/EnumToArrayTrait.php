<?php

namespace App\Trait;

trait EnumToArrayTrait
{
    public static function toAssociativeArray(): array
    {
        $array = [];
        foreach(self::cases() as $case) {
            $array[$case->name] = $case->value;
        }
        return $array;
    }

    //forma 1
    public static function getNames(): array
    {
        return array_map(function($item){
            return $item->name;
        }, self::cases());
    }

    //forma 2
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}