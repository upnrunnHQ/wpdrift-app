<?php
// app/Http/Helpers.php
class Helpers
{
    // To check provided url is live or not
    public static function get_url_response($url)
    {
        $response = array();
        //Check if URL is empty
        $is_url = filter_var($url, FILTER_VALIDATE_URL) !== false;
        if (!$is_url) {
            return false;
        } else {
            return true;
        }
        $response = "";
        if (!empty($url)) {
            $response = @get_headers($url);
        }
        if ($response != "") {
            if ((bool)in_array("HTTP/1.1 200 OK", $response, true)) {
                return (bool)in_array("HTTP/1.1 200 OK", $response, true);
            } elseif ((bool)in_array("HTTP/1.0 200 OK", $response, true)) {
                return (bool)in_array("HTTP/1.0 200 OK", $response, true);
            } else {
                return false;
            }
        } else {
            return false;
        }

        /*Array
        (
            [0] => HTTP/1.1 200 OK
            [Date] => Sat, 29 May 2004 12:28:14 GMT
            [Server] => Apache/1.3.27 (Unix)  (Red-Hat/Linux)
            [Last-Modified] => Wed, 08 Jan 2003 23:11:55 GMT
            [ETag] => "3f80f-1b6-3e1cb03b"
            [Accept-Ranges] => bytes
            [Content-Length] => 438
            [Connection] => close
            [Content-Type] => text/html
        )*/
    }
    public static function get_curl_response($url, $access_token)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($html, 0, $header_size);
        curl_close($ch);
        return $html;
    }
    // simple GET call
    public static function simple_get_curl_response($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $html = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($html, 0, $header_size);
        curl_close($ch);
        return $html;
    }
}
