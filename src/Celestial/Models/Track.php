<?php

namespace Celestial\Models;

use Celestial\Config\Application;
use Constellation\Database\DB;
use Constellation\Model\Model;
use FFMpeg;
use Exception;

class Track extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("tracks", ["id"], $id);
    }

    public static function search(string $type, string $term, int $customer_id)
    {
        $db = DB::getInstance();
        return $db->selectMany("SELECT tracks.id,
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
                CONCAT('".$_ENV['SERVER_URL']."/api/v1/music/play/', md5) as src,
                IF(track_likes.id > 0, 1, 0) as liked
            FROM tracks LEFT JOIN track_likes ON track_id = tracks.id AND customer_id = ?
            WHERE {$type} LIKE ?
            ORDER BY artist, album, track_number", $customer_id, "%{$term}%");
    }

    public function play()
    {
        if (file_exists($this->filenamepath)) {
            ob_start();
            $file =
                $this->mime_type !== 'audio/mpeg'
                ? $this->transcode()
                : $this->filenamepath;
            ob_clean();
            header('Content-Description: File Transfer');
            header("Content-Transfer-Encoding: binary");
            header('Content-Type: audio/mpeg');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Accept-Ranges: bytes');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit();
        } else {
            error_log("Track filename path not found {$this->filenamepath}");
        }
    }

    public function transcode()
    {
        $storage_dir = Application::$storage["storage_path"] . '/transcode/';
        $md5_file = $storage_dir . md5($this->filenamepath) . '.mp3';
        if (!file_exists($storage_dir)) {
            if (!mkdir($storage_dir)) {
                error_log('Failed to create transcode directory!');
            }
        }
        if (!file_exists($md5_file)) {
            $ffmpeg = FFMpeg\FFMpeg::create([
                'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/bin/ffprobe',
                'timeout' => 60 * 5,
                'ffmpeg.threads' => 12,
            ]);
            $audio_channels = 2;
            $bitrate = 160;
            $audio = $ffmpeg->open($this->filenamepath);
            $format = new FFMpeg\Format\Audio\Mp3('libmp3lame');
            $format
                ->setAudioChannels($audio_channels)
                ->setAudioKiloBitrate($bitrate);
            try {
                $audio->save($format, $md5_file);
                error_log("Transcode file: {$md5_file}");
            } catch (Exception $e) {
                error_log('Transcode error: ' . $e->getMessage());
            }
        }
        return $md5_file;
    }
}
