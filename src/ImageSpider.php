<?php

namespace IvanUskov\ImageSpider;

class ImageSpider
{
    private const SMALL_IMAGE_URL_PATTERN = '/gstatic\.com/';
    private const URL_PATTERN = 'https://www.google.com/search?q=%QUERY%&source=lnms&tbm=isch&sa=X&ved=2ahUKEwiPw-K9qdrwAhU0CRAIHUh0D4UQ_AUoAXoECAEQAw&biw=1920&bih=976';
    private const ENCODING = 'gzip, deflate';
    private const HEADERS = [
        'Authority: www.google.com',
        'Cache-Control: max-age=0',
        'Upgrade-Insecure-Requests: 1',
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
        'Dnt: 1',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
        'Referer: https://www.google.com/',
        'Accept-Encoding: gzip, deflate, br',
    ];

    public static function find(string $query): array
    {
        $url = str_replace('%QUERY%', $query, self::URL_PATTERN);
        $html = self::loadImages($url);

        return self::parseImageUrls($html);
    }

    private static function parseImageUrls(string $body): array
    {
        preg_match_all('/\["([^"]+)",\d+,\d+\]/', $body, $matches);
        $images = array_filter($matches[1] ?? [], function(string $imageUrl) {
            return !preg_match(self::SMALL_IMAGE_URL_PATTERN, $imageUrl);
        });

        return array_map(function($str) { return  self::decodeUnicodeSequence($str); }, $images);
    }

    private static function decodeUnicodeSequence(string $str): string
    {
        $data = json_decode('["' . $str . '"]');
        return array_pop($data);
    }

    private static function loadImages(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, self::ENCODING);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::HEADERS);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}