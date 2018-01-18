<?php
/**
 * Created by PhpStorm.
 * User: xingjingang
 * Date: 2017/8/31
 * Time: 下午7:29
 */

namespace Woldy\ddsdk\Components;

use Cache;
use Httpful\Request;
use DD;

class Process
{
    public static function createInstance($accessToken, $params)
    {
        $params['method'] = 'dingtalk.smartwork.bpms.processinstance.create';
        $params['timestamp'] = date('Y-m-d H:i:s');
        $params['format'] = 'json';
        $params['session'] = $accessToken;
        $params['v'] = '2.0';

        $response = Request::post('https://eco.taobao.com/router/rest')
            ->TimeoutIn(10)
            ->body($params)
            ->sends("application/x-www-form-urlencoded");
        $response=dd::try_http_query($response);



        if ($response->hasErrors()){
            var_dump($response);
            exit;
        }

        if(!is_object($response->body)){
            $response->body = json_decode($response->body);
        }


        return $response->body;
    }

    public static function getProcessData($accessToken, $params)
    {
        $params['method'] = 'dingtalk.smartwork.bpms.processinstance.list';
        $params['timestamp'] = date('Y-m-d H:i:s');
        $params['format'] = 'json';
        $params['session'] = $accessToken;
        $params['v'] = '2.0';

        $response = Request::post('https://eco.taobao.com/router/rest')
            ->TimeoutIn(10)
            ->body($params)
            ->sends("application/x-www-form-urlencoded");
        $response=dd::try_http_query($response);



        if ($response->hasErrors()){
            var_dump($response);
            exit;
        }

        if(!is_object($response->body)){
            $response->body = json_decode($response->body, true);
        }

      var_dump($response);

        return $response->body;
    }
}
