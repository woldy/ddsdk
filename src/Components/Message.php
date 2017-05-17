<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Storage;
use Httpful\Request;
use App\Models\Ding\DingUsersModel;
class Message{
    /**
     * 根据加密串发送企业消息。
     * @Author   Woldy
     * @DateTime 2016-05-12T09:15:19+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    [type]                   $config      [description]
     * @param    [type]                   $code        [description]
     * @return   [type]                                [description]
     */
	public static function sendMessageByCode($ACCESS_TOKEN,$config,$code){
		$join=self::decode($code);

		$param=json_decode($join,true);
	    $AgentID=$config->get('dd')['AgentID'];
	    $content=base64_decode(str_replace(" ","+",$param['content']));
        $touser='';
        $toparty=[];


        if(isset($param['emails']) && !empty($param['emails'])){
            $l=array_filter(explode('|',$param['emails']));
            $email_users=DingUsersModel::whereIn('email',$l)->whereRaw('LENGTH(email)>3')->select('dingid')->get()->toArray();
            $email_users=array_column($email_users,'dingid');
            if(!empty($email_users)){
                $touser=implode('|', $email_users);
            }
        }

        if(isset($param['workcodes']) && !empty($param['workcodes'])){

            $l=array_filter(explode('|',$param['workcodes']));
            $workcode_users=DingUsersModel::whereIn('workcode',$l)->whereRaw('LENGTH(workcode)>3')->select('dingid')->get()->toArray();
            $workcode_users=array_column($workcode_users,'dingid');
            if(!empty($workcode_users)){
                $touser=implode('|', $workcode_users);
            }
        }

         if(isset($param['dingids']) && !empty($param['dingids'])){
             $touser.="|".$param['dingids'];
         }

         if(isset($param['groups']) && !empty($param['groups'])){
             $toparty=$param['groups'];
         }


         if(isset($param['appid'])){
             $AgentID=$param['appid'];
         }

         if(!isset($param['type'])){
             $type='text';
         }else{
             $type=$param['type'];
         }

         $media='';
         if(isset($param['media_url']) && !empty($param['media_url'])){
             if(strrpos($param['media_url'],'http')===false){
                 $media='';
             }
             else{
                 $media=self::upLoadFile($ACCESS_TOKEN,$param['media_url']);
             }
         }

	   return self::sendMessage($touser,$toparty,$content,$AgentID,$ACCESS_TOKEN,$type,$media);

	}


    public static function sendMessageByData($data){

    }

    public static function upLoadFile($ACCESS_TOKEN,$path='',$type='image'){

        if(preg_match("/cli/i", php_sapi_name())){
            $tmppath="./storage/app/tmp/dingup_".str_random(32).".jpg";
        }else{
            $tmppath=$_SERVER['DOCUMENT_ROOT']."/../storage/app/tmp/dingup_".str_random(32).".jpg";
        }
        

        file_put_contents($tmppath,file_get_contents($path));
        $response=Request::post('https://oapi.dingtalk.com/media/upload?access_token='.$ACCESS_TOKEN."&type={$type}")
                    ->attach(array('media' =>$tmppath))
                    ->sends('upload')
                    ->send();
        if($response->body->errcode!=0){
            echo json_encode($response->body);
            exit;
        }
        return $response->body;
    }


    public static function getUser($email){
           $result['list']=[];
            $all=(Storage::disk('local')->get("/ding/all.csv"));
            $list=explode("\n",$all);
            $count=0;
            foreach ($list as $item) {
                $item=trim($item);
                $info=explode(',',$item);
                if(isset($info[5]) && strrpos($info[5], '@')!==false){ //email
                    if($info[5]==$email){
                        return $info[0];
                    }
                }
            }
    }

    /**
     * 根据详细参数发送企业消息
     * @Author   Woldy
     * @DateTime 2016-05-12T09:15:39+0800
     * @param    [type]                   $touser      [description]
     * @param    [type]                   $toparty     [description]
     * @param    [type]                   $content     [description]
     * @param    [type]                   $AgentID     [description]
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    string                   $type        [description]
     * @return   [type]                                [description]
     */
	public static function sendMessage($touser,$toparty,$content,$AgentID,$ACCESS_TOKEN,$type='text',$media=''){


        if($type=='text'){
            if(mb_detect_encoding( $content,'UTF-8') !='UTF-8'){
                $content=iconv('GB2312', 'UTF-8', $content);
            }
            $data=array("content"=>$content);
        }else if($type=='link'){
            $data=json_decode($content,true);
            if(!empty($media)){
                $data['picUrl']=$media->media_id;
            }
        }else if($type=='oa'){
            $data=json_decode($content,true);
            if(!empty($media)){
                $data['body']['image']=$media->media_id;
            }

        }


        $param=array(
            'touser' =>$touser,
            'toparty'=>$toparty,
            'agentid'=>$AgentID,
            "msgtype"=>$type,
            $type=>$data
        );

        $response = Request::post('https://oapi.dingtalk.com/message/send?access_token='.$ACCESS_TOKEN)
            ->body(json_encode($param))
            ->sends('application/json')
            ->send();
        if ($response->hasErrors()){
            var_dump($response);
            exit;
        }

        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }
        if ($response->body->errcode != 0){
            var_dump($response->body);
            exit;
        }
        return $response->body;

	}

    /**
     * 加密串函数
     * @Author   Woldy
     * @DateTime 2016-05-12T09:16:37+0800
     * @param    string                   $string [description]
     * @param    string                   $skey   [description]
     * @return   [type]                           [description]
     */
	static function encode($string = '', $skey = 'woldy') {
    	$strArr = str_split(base64_encode($string));
    	$strCount = count($strArr);
    	foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key].=$value;
    	return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
	}

    /**
     * 解密串函数
     * @Author   Woldy
     * @DateTime 2016-05-12T09:16:56+0800
     * @param    string                   $string [description]
     * @param    string                   $skey   [description]
     * @return   [type]                           [description]
     */
	static function decode($string = '', $skey = 'woldy') {
    	$strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    	$strCount = count($strArr);
    	foreach (str_split($skey) as $key => $value)
        $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    	return base64_decode(join('', $strArr));
	}
}
