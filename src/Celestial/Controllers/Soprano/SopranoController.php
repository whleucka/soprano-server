<?php

namespace Celestial\Controllers\Soprano;

use Celestial\Config\Application;
use Celestial\Models\Customer;
use Celestial\Models\Radio;
use Celestial\Models\Track;
use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Post, Get};

define("API_PREFIX", "/api/v1");

class SopranoController extends BaseController
{

    #[Post(API_PREFIX . "/sign-in", "soprano.sign-in", ["api"])]
    public function sign_in()
    {
        $data = $this->validateRequest([
            "email" => ["required", "string", "email"],
            "password" => ["required", "string"],
        ]);
        if ($data) {
            $customer = Customer::findByAttribute("email", $data->email);
            if ($customer) {
                if (password_verify($data->password, $customer->password)) {
                    // YAAAAAAA BUDDy, LIGHT WEIGHT BABY!
                    return [
                        "payload" => $customer->getAttributes()
                    ];
                }
            }
        }
        return [
            "success" => false,
            "message" => "Error: bad email and/or password",
        ];
    }

    #[Get(API_PREFIX . "/music/play/{md5}", "soprano.music-play", ["api"])]
    public function play($md5)
    {
        $track = Track::findByAttribute("md5", $md5);
        if ($track) {
            $track->play();
        }
        return [
            "success" => false,
            "message" => "Error: track doesn't exist",
        ];
    }

    #[Get(API_PREFIX . "/music/search", "soprano.music-search", ["api"])]
    public function music_search()
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
                    CONCAT(album, ' ', title) LIKE ? OR
                    filenamepath LIKE ?
                    ORDER BY artist, album, track_number",
                    ...array_fill(0, 8, "%{$request->term}%"));
            return [
                "payload" => $tracks,
            ];
        }
        return [
            "success" => false,
            "message" => "Error: term is required",
        ];
    }

    #[Get(API_PREFIX . "/radio/stations", "soprano.radio-stations", ["api"])]
    public function radio_stations()
    {
        $radio_stations = Radio::findAll();
        $stations = [];
        foreach ($radio_stations as $station) {
            $stations[] = $station->getAttributes();
        }
        return [
            "payload" => $stations
        ];
    }

    #[Get(API_PREFIX . "/radio/parse", "soprano.radio-parse", ["api"])]
    public function radio_parse()
    {
        $request = $this->validateRequest([
            "url" => [
                "required",
            ]
        ]);
        if ($request) {
            $cmd = "/usr/bin/ffprobe {$request->url} 2>&1 | rg 'artist'";
            $artist = exec($cmd);
            $cmd = "/usr/bin/ffprobe {$request->url} 2>&1 | rg 'title'";
            $title = exec($cmd);
            if ($artist || $title) {
                return [
                    "payload" => [
                        'artist' => trim(end(explode(':', $artist))),
                        'title' => trim(end(explode(':', $title)))
                    ],
                ];
            }
        }
        return [
            "success" => false,
            "message" => "Error: url is required",
        ];
    }


    #[Get(API_PREFIX . "/cover/{md5}/{width}/{height}", "soprano.radio-parse", ["api"])]
    public function thumbnail($md5, $width, $height)
    {
        $track = Track::findByAttribute("md5", $md5);
        $width = intval($width);
        $height = intval($height);
        if ($track) {
            $storage_path = Application::$environment['environment_path'];
            $imagick = new \imagick($storage_path . $track->cover);
            //crop and resize the image
            $imagick->cropThumbnailImage($width, $height);
            //remove the canvas
            $imagick->setImagePage(0, 0, 0, 0);
            $imagick->setImageFormat("png");

            header("Content-Type: image/png");
            echo $imagick->getImageBlob();
            exit;
        }
        return [
            "success" => false,
            "message" => "Error: track doesn't exist",
        ];
    }
}
