<?php

/*
Writer : Teamkitgroup | @TeamkitGroupCHN
Libray : Search in MrTehran Application
Mod : Hack </>
Github : https://github.com/aminrngbr1122
Her sponsor and Telegram channel : Hossien Pira | @h3dev | t.me/StealthySolutions
Thank You as {HTTPMONSTER} (:
*/

namespace Mrtehran;

// use libray HTTPMONSTER
use DarkPHP\HTTPMonster;

// Set the PHP configuration
ini_set('memory_limit', '2G');
ini_set('max_execution_time', '0');
error_reporting(0);

/**
 * MRN Class - Wrapper for MRN API functionality
 */
final class MRN extends HTTPMonster
{
    private string $search;
    private array $datas;
    public function __clone()
    {
         if (self::$search == '') {
            return (array) json_decode(file_get_contents('https://mrtehran.net/mt-app/v606/home_data.php?is_iran=1'),true);
         }
    }
    /**
     * Constructor - Initializes the MRN object
     *
     * @param string $search - The search term to be used for querying tracks
     */
    public function __construct(string $search = '')
    {
        $this->search = $search;
        $this->datas = array();
    }

    /**
     * Search - Perform a search and retrieve track data
     *
     * @return array - An array of track data
     */
    public function Search(): array
    {
        $http = new HTTPMonster();
        $data = json_decode(
            $http->Method('GET')
                ->Url("https://mrtehran.net/mt-app/v606/main_search_tracks.php?page=0&is_iran=1&search_text=" . urlencode($this->search))
                ->Timeout(0)
                ->Option(CURLOPT_SSL_VERIFYHOST, 0)
                ->Send(),
            true
        );

        foreach ($data['tracks'] as $sound) {
            $this->datas[] = [
                'id' => $sound['track_id'],
                'release_date' => $sound['release_date'],
                'artist_name_en' => $sound['track_artist'],
                'artist_name_fa' => $sound['track_artist_fa'],
                'title_en' => $sound['track_title'],
                'title_fa' => $sound['track_title_fa'],
                'track_artwork' => "https://cdnmrtehran.ir/media/" . $sound['track_artwork'],
                'track_audio_download_link' => base64_encode("https://cdnmrtehran.ir/media/" . $sound['track_audio'])
            ];
        }

        unset($http);
        unset($data);
        return (array) $this->datas;
    }

    /**
     * GetData - Retrieve like and comment numbers data for a track
     *
     * @param int|string $id - The ID of the track
     * @return array - An array containing likes and number of comments
     */
    public function GetData(int|string $id): array
    {
        $http = new HTTPMonster();
        $data = json_decode(
            $http->Method('GET')
                ->Url("https://mrtehran.net/mt-app/v606/user_like_checker.php?user_id=0&track_id=" . strval($id))
                ->Timeout(0)
                ->Option(CURLOPT_SSL_VERIFYHOST, 1)
                ->Send(),
            true
        );

        $this->datas = [
            'likes' => $data['likes'],
            'number_comments' => $data['number_comments']
        ];

        unset($http);
        unset($data);
        return (array) $this->datas;
    }

    /**
     * GetComments - Retrieve comments for a track
     *
     * @param int|string $id - The ID of the track
     * @return array - An array containing comments data
     */
    public function GetComments(int|string $id): array
    {
        $http = new HTTPMonster();
        $data = json_decode(
            $http->Method('GET')
                ->Url("https://mrtehran.net/mt-app/v606/comments.php?track_id=" . strval($id) . "&page=0&is_iran=1")
                ->Timeout(0)
                ->Option(CURLOPT_SSL_VERIFYHOST, 0)
                ->send(),
            true
        );

        foreach ($data['comments'] as $sound) {
            $this->datas[] = [
                'comment_date' => $sound['comment_date'],
                'comment' => $sound['comment'],
                'user_name' => $sound['user_name'],
                'user_icon' => $sound['user_thumbnail']
            ];
        }

        unset($http);
        unset($data);
        return (array) $this->datas;
    }

    /**
     * Download_or_Get_music_link - Download or get the music link
     *
     * @param string $url_base - The base64-encoded URL
     * @param bool $link - Whether to return a link or perform a download
     * @return array|int - An array containing the file URL if $link is true, otherwise 1 for successful download
     */
    function Download_or_Get_music_link(string $url_base, bool $link = false): array|int
    {
        $url = base64_decode($url_base);
        if ($link === true) {
            $this->datas = ['File_Url' => $url];
            return (array) $this->datas;
        } else {
            if (isset($_SERVER['REQUEST_METHOD'])) {
            header('Content-Type: audio/mp3');
            header('Content-Disposition: attachment; filename="' . basename($url) . '"');
            die(readfile($url));
        } else {
            if(copy($url, "./" . basename($url) ."")) {
                return 1;
            } else {
                return 0;
            }
        }
      }
   }

    /**
     * Destructor - Cleans up the object
     */
    public function __destruct()
    {
        $this->datas = array();
    }
}

/**
 * HTTPMonster - A class for sending HTTP requests
 */