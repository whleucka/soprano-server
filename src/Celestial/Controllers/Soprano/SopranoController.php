<?php

namespace Celestial\Controllers\Soprano;

use Celestial\Config\Application;
use Celestial\Models\{Customer, Radio, Track, TrackLikes};
use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Post, Get, Options};
use Exception;

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
                    return [
                        "payload" => $customer->getAttributes()
                    ];
                }
            }
        }
        return [
            "success" => false,
            "message" => "bad email and/or password",
        ];
    }

    #[Options(API_PREFIX . "/customer/load", "soprano.customer-load", ["api"])]
    #[Post(API_PREFIX . "/customer/load", "soprano.customer-load", ["api"])]
    public function customer_load()
    {
        $data = $this->validateRequest([
            "uuid" => ["required"],
        ]);
        if ($data) {
            $customer = Customer::findByAttribute("uuid", $data->uuid);
            if ($customer) {
                return [
                    "payload" => $customer->getAttributes()
                ];
            }
        }
        return [
            "success" => false,
            "message" => "unknown user",
        ];
    }

    #[Post(API_PREFIX . "/liked/count", "soprano.liked-count", ["api"])]
    public function liked_count()
    {
        // User uuid valdiation
        $request = $this->validateRequest([
            "uuid" => [
                "required",
            ]
        ]);
        if ($request) {
            $customer = Customer::findByAttribute("uuid", $request->uuid);
            if (!$customer) {
                return [
                    "success" => false,
                    "message" => "customer doesn't exist",
                ];
            }
            $count = $this->db->selectVar("SELECT ifnull(count(*), 0)
                FROM tracks
                INNER JOIN track_likes ON track_id = tracks.id
                WHERE customer_id = ?", $customer->id);
            return [
                "payload" => $count
            ];
        }
        return [
            "success" => false,
            "message" => "unknown user",
        ];
    }

    #[Post(API_PREFIX . "/liked/playlist", "soprano.liked-playlist", ["api"])]
    public function liked_playlist()
    {
        // User uuid valdiation
        $request = $this->validateRequest([
            "uuid" => [
                "required",
            ]
        ]);
        if ($request) {
            $customer = Customer::findByAttribute("uuid", $request->uuid);
            if (!$customer) {
                return [
                    "success" => false,
                    "message" => "customer doesn't exist",
                ];
            }
            $tracks = $this->db->selectMany("SELECT tracks.id,
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
                CONCAT('" . $_ENV['SERVER_URL'] . "', cover) as cover,
                CONCAT('" . $_ENV['SERVER_URL'] . "/api/v1/music/play/', md5) as src,
                1 as liked
                FROM tracks
                INNER JOIN track_likes ON track_id = tracks.id
                WHERE customer_id = ?
                ORDER BY artist, album, track_number", $customer->id);
            return [
                "payload" => $tracks
            ];
        }
        return [
            "success" => false,
            "message" => "unknown user",
        ];
    }

    // Toggle a track 'like' or 'un-like'
    #[Post(API_PREFIX . "/like/{md5}", "soprano.like", ["api"])]
    public function like($md5)
    {
        // Make sure track exists
        $track = Track::findByAttribute("md5", $md5);
        if (!$track) {
            return [
                "success" => false,
                "message" => "track doesn't exist",
            ];
        }
        // User uuid valdiation
        $request = $this->validateRequest([
            "uuid" => [
                "required",
            ]
        ]);
        if ($request) {
            $customer = Customer::findByAttribute("uuid", $request->uuid);
            if (!$customer) {
                return [
                    "success" => false,
                    "message" => "customer doesn't exist",
                ];
            }

            $like = new TrackLikes([$customer->id, $track->id]);
            if ($like->isLoaded()) {
                // We should 'un-like'
                $like->delete();
                return [
                    "payload" => ['like' => false],
                ];
            } else {
                // 'like' the track
                $like->create(['customer_id' => $customer->id, 'track_id' => $track->id]);
                return [
                    "payload" => ['like' => true],
                ];
            }
            return [
                "payload" => $like->getAttributes()
            ];
        }
        return [
            "success" => false,
            "message" => "unknown user",
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
            "message" => "track doesn't exist",
        ];
    }

    #[Options(API_PREFIX . "/music/search", "soprano.music-search", ["api"])]
    #[Post(API_PREFIX . "/music/search", "soprano.music-search", ["api"])]
    public function music_search()
    {
        $request = $this->validateRequest([
            "term" => [
                "required",
            ],
            "type" => [
                "required",
            ],
        ]);
        if ($request) {
            $customer_id = 99999;
            if ($request->uuid != 'null') {
                $customer = Customer::findByAttribute("uuid", $request->uuid);
                if (!$customer) {
                    return [
                        "success" => false,
                        "message" => "customer doesn't exist",
                    ];
                }
                $customer_id = $customer->id;
            }
            return [
                "payload" => Track::search($request->type, $request->term, $customer_id),
            ];
        }
        return [
            "success" => false,
            "message" => "term is required",
        ];
    }

    #[Options(API_PREFIX . "/radio/stations", "soprano.radio-stations", ["api"])]
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
            "message" => "url is required",
        ];
    }


    #[Options(API_PREFIX . "/cover/{md5}/{width}/{height}", "soprano.radio-parse", ["api"])]
    #[Get(API_PREFIX . "/cover/{md5}/{width}/{height}", "soprano.radio-parse", ["api"])]
    public function thumbnail($md5, $width, $height)
    {
        $track = Track::findByAttribute("md5", $md5);
        $width = intval($width);
        $height = intval($height);
        if ($track) {
            try {
                $storage_path = Application::$environment['environment_path'];
                $imagick = new \imagick($storage_path . $track->cover);
                //crop and resize the image
                $imagick->cropThumbnailImage($width, $height);
                //remove the canvas
                $imagick->setImagePage(0, 0, 0, 0);
                $imagick->setImageFormat("png");

                header("Access-Control-Allow-Origin: *");
                header("Content-Type: image/png");
                header("Expires: 0");
                echo $imagick->getImageBlob();
                exit;
            } catch (Exception $ex) {
                // No errors in log
                print("imagick error: check logs " . $ex->getMessage());
                exit;
            }
        }
        return [
            "success" => false,
            "message" => "track doesn't exist",
        ];
    }
}
