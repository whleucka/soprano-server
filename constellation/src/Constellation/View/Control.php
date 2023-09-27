<?php

namespace Constellation\View;

class Control
{
    public static function input($column, $value, $type = "text", ...$attrs)
    {
        $attributes = implode(" ", $attrs);
        $control = "<div>";
        $control .= "<input name='{$column}' type='{$type}' class='input-{$column}' value='{$value}' {$attributes} />";
        $control .= "</div>";
        return $control;
    }

    public static function image($column, $value, ...$attrs)
    {
        $attributes = implode(" ", $attrs);
        $control = "<div>";
        $control .= "<img class='image-{$column}' src='{$value}' {$attributes} loading='lazy' />";
        $control .= "</div>";
        return $control;
    }

    public static function textarea($column, $value, ...$attrs)
    {
        $attributes = implode(" ", $attrs);
        $control = "<div>";
        $control .= "<textarea name='{$column}' class='input-{$column}' {$attributes}>";
        $control .= "{$value}";
        $control .= "</textarea>";
        $control .= "</div>";
        return $control;
    }

    public static function editor($column, $value, ...$attrs)
    {
        $attributes = implode(" ", $attrs);
        $control = "<div id='editor'>";
        $control .= "{$value}";
        $control .= "</div>";
        $control .= "<input id='editor-content' value='{$value}' type='hidden' name='{$column}' class='input-{$column}' {$attributes}>";
        return $control;
    }
}
