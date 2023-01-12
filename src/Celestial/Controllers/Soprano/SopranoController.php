<?php

namespace Celestial\Controllers\Soprano;

use Celestial\Models\Track;
use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;

define("API_PREFIX", "/api/v1");

class SopranoController extends BaseController
{
    #[Get(API_PREFIX . "/music/play/{md5}", "soprano.music-play", ["api"])]
    public function play($md5)
    {
        $track = Track::findByAttribute("md5", $md5);
        if ($track) {
            $track->play();
        }
        return [
            "success" => false,
            "message" => "Track doesn't exist",
        ];
    }

    #[Get(API_PREFIX . "/music/search", "soprano.music-search", ["api"])]
    public function search()
    {
        $request = $this->validateRequest([
            "term" => [
                "required",
            ]
        ]);
        if ($request) {
            $tracks = $this->db
                ->selectMany("SELECT id,
                        md5,
                        filesize,
                        filenamepath,
                        file_format,
                        mime_type,
                        bitrate,
                        playtime_seconds,
                        playtime_string,
                        track_number,
                        artist,
                        title,
                        album,
                        genre,
                        year,
                        CONCAT('".$_ENV['SERVER_URL']."', cover) as cover,
                        CONCAT('".$_ENV['SERVER_URL']."/api/v1/music/play/', md5) as src
                    FROM tracks
                    WHERE artist LIKE ? OR
                    album LIKE ? OR
                    title LIKE ? OR
                    genre LIKE ? OR
                    year LIKE ? OR
                    CONCAT(artist, ' ', album) LIKE ? OR
                    CONCAT(album, ' ', title) LIKE ?
                    ORDER BY artist, album, track_number",
                    ...array_fill(0, 6, "%{$request->term}%"));
            return [
                "payload" => $tracks,
            ];
        }
        return [
            "success" => false,
            "message" => "Validation error",
        ];
    }
}
