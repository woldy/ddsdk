<?php
/**
 * Created by PhpStorm.
 * User: xingjingang
 * Date: 2018/1/30
 * Time: 下午6:04
 */

namespace Woldy\ddsdk\Components;
use Cache;

class Media
{
    public static function getMedia($accessToken, $params)
    {
        $result = file_get_contents("https://oapi.dingtalk.com/media/downloadFile?access_token={$accessToken}&media_id={$params['mediaId']}");

        $response['header'] = $http_response_header;
        $response['content'] = $result;

        return $response;
    }
}