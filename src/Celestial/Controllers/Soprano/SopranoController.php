<?php

namespace Celestial\Controllers\Soprano;

use Celestial\Config\Application;
use Celestial\Models\{Customer, Playlist, Radio, Track, TrackLikes};
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

    #[Get(API_PREFIX . "/music/playlists", "soprano-playlist-get", ["api"])]
    public function playlists(): array
    {
        $request = $this->validateRequest([
            "uuid" => [
                "required",
            ]
        ]);
        if ($request) {
            $customer = Customer::findByAttribute("uuid", $request->uuid);
            $playlists = $this->db
                ->selectMany("SELECT *
                    FROM playlists
                    WHERE customer_id = ?
                    ORDER BY name", $customer->id);
            return [
                "payload" => $playlists
            ];
        }
        return [
            "success" => false,
            "message" => "unknown user",
        ];
    }

    #[Get(API_PREFIX . "/music/playlist/{playlist_id}", "soprano-playlist-get", ["api"])]
    public function playlist($playlist_id): array
    {
        $request = $this->validateRequest([
            "uuid" => [
                "required",
            ]
        ]);
        if ($request) {
            $tracks = $this->db
                ->selectMany("SELECT *
                    FROM playlist_tracks
                    LEFT JOIN tracks ON tracks.id = track_id
                    WHERE playlist_id = ?", $playlist_id);
            return [
                "payload" => $tracks
            ];
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
        // User uuid valdiation
        $request = $this->validateRequest([
            "uuid" => [
                "required",
            ]
        ]);
        if ($request) {
            // Make sure track exists
            $track = Track::findByAttribute("md5", $md5);
            if (!$track) {
                return [
                    "success" => false,
                    "message" => "track doesn't exist",
                ];
            }
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
            header("Access-Control-Allow-Origin: *");
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
            if (isset($request->uuid) && $request->uuid !== 'null' && $request->uuid) {
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
        $stations = $this->db
        ->selectMany("SELECT *
            FROM radio
            ORDER BY location, station_name");
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

    #[Options(API_PREFIX . "/music/albums", "soprano.music-albums", ["api"])]
    #[Get(API_PREFIX . "/music/albums", "soprano.music-albums", ["api"])]
    public function music_albums()
    {
        $albums = $this->db
        ->selectMany("SELECT distinct(album)
            FROM tracks
            ORDER BY album
            LIMIT 500");
        return [
            "payload" => $albums
        ];
    }

    #[Options(API_PREFIX . "/music/artists", "soprano.music-artists", ["api"])]
    #[Get(API_PREFIX . "/music/artists", "soprano.music-artists", ["api"])]
    public function music_artists()
    {
        $artists = $this->db
        ->selectMany("SELECT distinct(artist)
            FROM tracks
            ORDER BY artist
            LIMIT 500");
        return [
            "payload" => $artists
        ];
    }

    #[Options(API_PREFIX . "/music/genres", "soprano.music-genres", ["api"])]
    #[Get(API_PREFIX . "/music/genres", "soprano.music-genres", ["api"])]
    public function music_genres()
    {
        $genres = $this->db
        ->selectMany("SELECT distinct(genre)
            FROM tracks
            ORDER BY genre
            LIMIT 500");
        return [
            "payload" => $genres
        ];
    }


    #[Options(API_PREFIX . "/image", "soprano.image", ["api"])]
    #[Get(API_PREFIX . "/image", "soprano.image", ["api"])]
    public function image()
    {

        $request = $this->validateRequest([
            "url" => [
                "required",
            ]
        ]);
        if ($request) {
            $image_url = $request->url;
            $content = file_get_contents($image_url);
            if ($content !== false) {
                header("Access-Control-Allow-Origin: *");
                echo $content;
            }
        }
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
                // Set headers
                $expires = 60 * 60 * 24 * 30; // about a month
                header("Cache-Control: public, max-age={$expires}");
                header("Expires: " . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
                header("Access-Control-Allow-Origin: *");
                header("Content-Type: image/png");

                $storage_path = Application::$environment['environment_path'];
                $cache_directory = "/tmp/";

                // Generate a unique cache filename based on the parameters.
                $cache_filename = md5($md5 . $width . $height) . '.png';
                $cache_filepath = $cache_directory . $cache_filename;

                // Check if the cached image exists.
                if (file_exists($cache_filepath)) {
                    // Serve the cached image.
                    readfile($cache_filepath);
                    exit;
                }

                $filepath = $storage_path . $track->cover;
                if (file_exists($filepath)) {
                    $imagick = new \imagick($storage_path . $track->cover);
                    //crop and resize the image
                    $imagick->cropThumbnailImage($width, $height);
                    //remove the canvas
                    $imagick->setImagePage(0, 0, 0, 0);
                    $imagick->setImageFormat("png");
                    // Save the resized image to the cache directory.
                    $imagick->writeImage($cache_filepath);
                    echo $imagick->getImageBlob();
                    exit;
                } else {
                    // Serve the
                    $no_album = $storage_path . "/public/img/no-album.png";
                    readfile($no_album);
                    exit;
                }
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
