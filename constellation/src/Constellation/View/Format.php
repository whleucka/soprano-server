<?php

namespace Constellation\View;

use Carbon\Carbon;

class Format
{
    public static function default($column, $value)
    {
        return "<span class='default-{$column}'>{$value}</span>";
    }

    public static function pct($column, $value, ...$args)
    {
        return "<span class='pct-{$column}'>" .
            number_format($value * 100, ...$args) .
            "%</span>";
    }

    public static function dateTime($column, $value, $format = "Y-m-d H:i:s")
    {
        if ($value == "0000-00-00 00:00:00") {
            return "&nbsp;";
        }
        $date = Carbon::createFromFormat($format, $value, "UTC");
        $date->locale("en_CA");
        $datetime = $date->format($format);
        return "<span class='datetime-{$column}'>{$datetime}</span>";
    }

    public static function dateTimeLocal(
        $column,
        $value,
        $format = "Y-m-d H:i:s"
    ) {
        if ($value == "0000-00-00 00:00:00") {
            return "&nbsp;";
        }
        $date = Carbon::createFromFormat($format, $value, "UTC");
        // TODO pull tz from user profile
        $date->setTimezone("America/Edmonton");
        // TODO pull locale from user profile
        $date->locale("en_CA");
        $datetime = $date->format($format);
        return "<span class='datetime-local-{$column}'>{$datetime}</span>";
    }

    public static function ago($column, $value)
    {
        if ($value == "0000-00-00 00:00:00") {
            return "&nbsp;";
        }
        $date = new Carbon($value);
        $date->locale("en_CA");
        $datetime = $date->diffForHumans();
        return "<span class='ago-{$column}'>{$datetime}</span>";
    }

    public static function image($column, $value)
    {
        return "<img class='table-image image-{$column}' src='{$value}' alt='{$column}-image' />";
    }
}
