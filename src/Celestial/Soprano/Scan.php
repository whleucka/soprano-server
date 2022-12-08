<?php

namespace Celestial\Soprano;

use Celestial\Config\Application;
use Constellation\Database\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;
use getID3;
use getid3_lib;

class Scan
{
    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Scan music path (Celestial\Config\Application)
     * for audio files and synchronize the db
     * - Only inserts new records to db
     * - CLI method
     */
    public function scanMusicTracks(): void
    {
        $files = $this->getMusicFiles();
        $this->db->beginTransaction();
        $new = $skipped = [];
        printf("Synchronizing %s files..." . PHP_EOL, count($files));
        foreach ($files as $file) {
            $tags = $this->getID3Tag($file);
            if ($this->synchronizeTrack($tags)) {
                $new[] = $file;
            } else {
                $skipped[] = $file;
            }
        }
        if (!empty($skipped)) {
            printf("Skipped %s existing tracks..." . PHP_EOL, count($skipped));
        }
        if (!empty($new)) {
            printf("Sychronizing %s new tracks..." . PHP_EOL, count($new));
        }
        print("Updating the database..." . PHP_EOL);
        $this->db->commit();
    }

    public function removeMissingTracks()
    {
        $this->db->beginTransaction();
        $tracks = $this->getDatabaseTracks();
        printf("Synchronizing %s existing tracks..." . PHP_EOL, count($tracks));
        $removed = $skipped = [];
        foreach ($tracks as $track) {
            if (!file_exists($track->filenamepath)) {
                if ($this->removeOrphanTrack($track->md5)) {
                    $removed[] = $track->filenamepath;
                }
            } else {
                $skipped[] = $track->filenamepath;
            }
        }
        if (!empty($skipped)) {
            printf("Skipped %s existing tracks..." . PHP_EOL, count($skipped));
        }
        if (!empty($removed)) {
            printf("Removed %s orphaned tracks..." . PHP_EOL, count($removed));
        }
        print("Updating the database..." . PHP_EOL);
        $this->db->commit();
    }

    /**
     * Get all the music files from the music
     * path (Celestial\Config\Application)
     */
    public function getMusicFiles(): array
    {
        $music_path = Application::$soprano['music_path'];
        if (!file_exists($music_path)) {
            die("Music path does not exist: {$music_path}");
        }

        $files = [];
        $dir = new RecursiveDirectoryIterator($music_path);
        $it = new RecursiveIteratorIterator($dir);
        $music_files = new RegexIterator(
            $it,
            '/^.+\.(mp3|flac|m4a|ogg|wav)$/i',
            RecursiveRegexIterator::GET_MATCH
        );
        foreach ($music_files as $music_file) {
            list($filenamepath) = $music_file;
            $files[] = $filenamepath;
        }
        sort($files);
        return $files;
    }

    public function getDatabaseTracks()
    {
        return $this->db->selectMany("SELECT * FROM tracks");
    }

    /**
     * Remove a track if it doesn't
     * exist on the filesystem
     */
    public function removeOrphanTrack($md5): bool
    {
        $this->db
            ->query("DELETE FROM tracks
                WHERE md5 = ?", $md5);
        return $this->db->stmt->rowCount() > 0;
    }

    /**
     * Return the ID3 tag from
     * a filename path
     */
    public function getID3Tag(string $filenamepath)
    {
        if (!file_exists($filenamepath)) {
            die("Music file does not exist: {$filenamepath}");
        }
        $getID3 = new getID3();
        $tags = $getID3->analyze($filenamepath);
        getid3_lib::CopyTagsToComments($tags);
        return [
            "md5" => md5($filenamepath),
            "cover" => $this->processAlbumArt($tags),
            "filesize" => $tags["filesize"],
            "filenamepath" => $tags["filenamepath"],
            "file_format" => $tags["fileformat"] ?? "",
            "mime_type" => $tags["mime_type"],
            "bitrate" => $tags["bitrate"],
            "playtime_seconds" => $tags["playtime_seconds"],
            "playtime_string" => $tags["playtime_string"],
            "title" => key_exists("title", $tags["comments_html"])
                ? html_entity_decode($tags["comments_html"]["title"][0])
                : 'No Title',
            "artist" => key_exists("artist", $tags["comments_html"])
                ? html_entity_decode($tags["comments_html"]["artist"][0])
                : 'No Artist',
            "track_number" => key_exists("track_number", $tags["comments_html"])
                ? html_entity_decode($tags["comments_html"]["track_number"][0])
                : '',
            "album" => key_exists("album", $tags["comments_html"])
                ? html_entity_decode($tags["comments_html"]["album"][0])
                : 'No Album',
            "genre" => key_exists("genre", $tags["comments_html"])
                ? html_entity_decode(
                    implode("|", $tags["comments_html"]["genre"])
                )
                : '',
            "year" => key_exists("year", $tags["comments_html"])
                ? html_entity_decode($tags["comments_html"]["year"][0])
                : '',
        ];
    }

    /**
     * Insert track to database
     */
    public function synchronizeTrack(array $tags): bool
    {
        $exists = $this->db->selectOne("SELECT * FROM tracks WHERE md5 = ?", $tags["md5"]);
        if (!$exists) {
            $this->db
                ->query("INSERT INTO tracks SET
                        md5 = ?,
                        cover = ?,
                        filesize = ?,
                        filenamepath = ?,
                        file_format = ?,
                        mime_type = ?,
                        bitrate = ?,
                        playtime_seconds = ?,
                        playtime_string = ?,
                        title = ?,
                        artist = ?,
                        track_number = ?,
                        album = ?,
                        genre = ?,
                        year = ?", ...array_values($tags));
            return $this->db->stmt->rowCount() > 0;
        }
        return false;
    }

    public function processAlbumArt(array $tags)
    {
        $cover_art = "/img/no-album.png";
        // Embedded
        if (isset($tags["comments"]["picture"])) {
            $pictures = $tags["comments"]["picture"];
            foreach ($pictures as $picture) {
                if (isset($picture["picturetype"])) {
                    if (preg_match("/cover/i", $picture["picturetype"])) {
                        $cover_art = $this->extractAlbumArt($picture);
                        if ($cover_art) {
                            return $cover_art;
                        }
                    }
                }
            }
        }
        // Look for any cover assets in the same directory
        $storage_dir = $this->getStorageDir();
        foreach (["png", "jpg", "jpeg"] as $ext) {
            $cover_path = "{$tags["filepath"]}/cover.{$ext}";
            if (file_exists($cover_path)) {
                $filename = md5($cover_path) . "." . $ext;
                $storage_dir = $storage_dir . "/covers/";
                $cover_asset = $storage_dir . $filename;
                if (!file_exists($cover_asset)) {
                    copy($cover_path, $cover_asset);
                }
                $public_path = "/storage/covers/{$filename}";
                return $public_path;
            }
        }
        return $cover_art;
    }

    public function extractAlbumArt($picture): ?string
    {
        $storage_dir = $this->getStorageDir();
        switch ($picture["image_mime"]) {
            case "image/jpeg":
                $ext = ".jpg";
                break;
            case "image/png":
                $ext = ".png";
                break;
        }
        if (isset($ext)) {
            $encoded = base64_encode($picture["data"]);
            $image_string = str_replace(" ", "+", $encoded);
            $image_data = base64_decode($image_string);
            $filename = md5($image_string) . $ext;
            $cover_directory = $storage_dir . "/covers/";
            if (!file_exists($cover_directory)) {
                if (!mkdir($cover_directory)) {
                    error_log(
                        "Unable to create covers directory. Please check permissions."
                    );
                    exit;
                }
            }
            $storage_path = $cover_directory . $filename;
            $public_path = "/storage/covers/{$filename}";
            if (!file_exists($storage_path)) {
                file_put_contents($storage_path, $image_data);
            }
            return $public_path;
        }
    }

    public function getStorageDir()
    {
        $storage_dir = Application::$storage["storage_path"];
        return $storage_dir;
    }
}
