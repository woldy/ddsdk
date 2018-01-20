<?php
namespace Woldy\ddsdk\Components;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use Log;
class Util {
    public static function try_http_query($response,$retry=3,$exit=true,$url=''){
      $resp='';
      try {
           $resp= $response->send();
      } catch (ConnectionErrorException $e) {
          if($retry<1){
            if($exit){
              die("网络不稳啊".$url."\n");
            }else{
              echo "这次请求不太行，正在重试\n".$response->uri;
              return false;
            }
          }else{
            echo "这次请求不太行，正在重试\n".$response->uri;
            return self::try_http_query($response,--$retry,$exit,$url);
          }
      }
      $response=$resp;
      // if ($response->hasErrors()){
      //
      // }
      // if(!is_object($response->body)){
      //     $response->body=json_decode($response->body);
      // }

      // if ($response->body->errcode != 0){
      //
      // }

      return $response;
    }
}
