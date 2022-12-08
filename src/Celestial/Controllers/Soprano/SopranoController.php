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
                ->selectMany("SELECT *
                    FROM tracks
                    WHERE artist LIKE ? OR
                    album LIKE ? OR
                    title LIKE ?
                    ORDER BY artist, album, track_number",
                    ...array_fill(0, 3, "%{$request->term}%"));
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
