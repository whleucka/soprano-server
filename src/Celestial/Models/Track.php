<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class Track extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("tracks", ["id"], $id);
    }

    public function play()
    {
        if (file_exists($this->filenamepath)) {
            // TODO: Transcode to mp3 to have more compatibility
            ob_start();
            $file =
                $this->mime_type !== 'audio/mpeg'
                ? $this->transcode()
                : $this->filenamepath;
            ob_clean();
            header('Content-Type: ' . mime_content_type($file));
            header('Content-Disposition: inline');
            header('Cache-Control: public, max-age=2629746');
            header('Accept-Ranges: bytes');
            header('Content-Length: ' . filesize($file));
            header('Content-Transfer-Encoding: chunked');
            header('Connection: Keep-Alive');
            header('X-Pad: avoid browser bug');
            readFile($file);
            exit();
        } else {
            error_log("Track filename path not found {$this->filenamepath}");
        }
    }

    public function transcode()
    {
        // TODO Fix me and transcode via ffmpeg
        return $this->filenamepath;
    }
}

