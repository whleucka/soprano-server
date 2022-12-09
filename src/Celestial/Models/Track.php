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
            $mime_type = $this->mime_type ?? mime_content_type($file);
            ob_clean();
            header('Content-Description: File Transfer');
            header("Content-Transfer-Encoding: binary");
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.filesize($file));
            readfile($file);
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

