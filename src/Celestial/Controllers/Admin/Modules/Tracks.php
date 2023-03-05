<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Module\Module;

class Tracks extends Module
{
    public function __construct()
    {
        $this->allow_insert = false;
        $this->show_export_csv = false;
        $this->title = "Tracks";
        $this->table = "tracks";
        $this->table_columns = [
            "'' as play" => "Tracks",
            "'' as track_col_1" => "",
            "'' as track_col_2" => "",
            "'' as track_col_3" => "",
            "cover" => null,
            "playtime_string" => null,
            "file_format" => null,
            "year" => null,
            "artist" => null,
            "album" => null,
            "title" => null,
            "md5" => null,
            "bitrate" => null,
            "filesize" => null,
            "genre" => null,
            "mime_type" => null,
        ];
        $this->table_format = [
            "created_at" => "datetime-local",
            "cover" => "image",
        ];
        $this->form_columns = [
            "cover" => "Cover",
            "artist" => "Artist",
            "album" => "Album",
            "title" => "Title",
            "genre" => "Genre",
            "year" => "Year",
        ];
        $this->form_control = [
            "cover" => "image",
            "artist" => "input",
            "album" => "input",
            "title" => "input",
            "genre" => "input",
            "year" => "input",
        ];
        $this->table_filters = ["md5", "artist", "album", "title", "genre", "year"];
        $this->order_by_clause = "artist,album,track_number";
        parent::__construct("tracks");
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function override($context, $datum)
    {
        if ($context == 'table') {
            // Play track button
            $md5 = $datum['md5'];
            $datum['play'] = "<div class='ml-10 mr-10'><button class='sm play-track' data-md5='{$md5}'><i stroke-width='2' data-feather='play' width='16'></i></button></div>";
            // Track information
            $bitrate = number_format($datum['bitrate'] / 1_000) ?? 0;
            $genre = implode(', ', explode('|', $datum['genre'])) ?? 'n/a';
            $filesize = $this->formatBytes($datum['filesize'], 1);
            $datum['track_col_1'] = "<div class='track-information flex align-items-center'>
                <img src='/api/v1/cover/{$md5}/70/70' alt='cover' loading='lazy'>
                <div class='info'>
                    <div class='flex' title=\"{$datum['artist']}\"><i data-feather='user' width='16' stroke-width='2' class='pr-10'></i> <span class='truncate' style='max-width: 200px'>{$datum['artist']}</span></div>
                    <div class='flex' title=\"{$datum['album']}\"><i data-feather='disc' width='16' stroke-width='2' class='pr-10'></i> <span class='truncate' style='max-width: 200px'>{$datum['album']}</span></div>
                    <div class='flex' title=\"{$datum['title']}\"><i data-feather='music' width='16' stroke-width='2' class='pr-10'></i> <span class='truncate' style='max-width: 200px'>{$datum['title']}</span></div>
                </div>
                </div>";
            $datum['track_col_2'] = "<div class='track-information flex align-items-center'>
                <div class='info'>
                    <div class='flex'><i data-feather='clock' width='16' stroke-width='2' class='pr-10'></i> {$datum['playtime_string']}</div>
                    <div class='flex'><i data-feather='calendar' width='16' stroke-width='2' class='pr-10'></i> {$datum['year']}</div>
                    <div class='flex' title=\"{$genre}\"><i data-feather='music' width='16' stroke-width='2' class='pr-10'></i> <span class='truncate' style='max-width: 200px'>{$genre}</span></div>
                </div>
                </div>";
            $datum['track_col_3'] = "<div class='track-information flex align-items-center'>
                <div class='info'>
                    <div class='flex'><i data-feather='radio' width='16' stroke-width='2' class='pr-10'></i> {$bitrate} kbps</div>
                    <div class='flex'><i data-feather='hard-drive' width='16' stroke-width='2' class='pr-10'></i> {$datum['file_format']}</div>
                    <div class='flex'><i data-feather='file' width='16' stroke-width='2' class='pr-10'></i> {$filesize}</div>
                </div>
                </div>";
        }
        return $datum;
    }

    protected function getTable()
    {
        $table = parent::getTable();
        $player = <<<EOT
            <section id="tracks-player">
            <audio controls autoplay src>
            Your browser does not support the audio element.
            </audio>
            </section>
EOT;
        return $player . $table;
    }
}
