<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
use Woldy\ddsdk\Components\dThreads;
class Contacts{
    /**
     * 根据免登码获取用户信息
     * @Author   Woldy
     * @DateTime 2016-05-12T09:13:43+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    [type]                   $CODE         [description]
     * @return   [type]                                 [description]
     */
 	public static function getUserInfoByCode($ACCESS_TOKEN,$CODE){
        	$param=http_build_query(
        		array(
        			'code' =>$CODE, 
        			'access_token'=>$ACCESS_TOKEN
        		)
        	);
 
            $response = Request::get('https://oapi.dingtalk.com/user/getuserinfo?'.$param)->send();
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
            $userid = $response->body->userid;
 
        return self::getUserInfoByUid($ACCESS_TOKEN,$userid);
	}

    /**
     * 根据uid获取用户信息
     * @Author   Woldy
     * @DateTime 2016-05-12T09:14:17+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    [type]                   $uid          [description]
     * @return   [type]                                 [description]
     */
	public static function getUserInfoByUid($ACCESS_TOKEN,$uid){
        	$param=http_build_query(
        		array(
        			'userid' =>$uid, 
        			'access_token'=>$ACCESS_TOKEN
        		)
        	);
 
            $response = Request::get('https://oapi.dingtalk.com/user/get?'.$param)->send();
            if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
            if(!is_object($response->body)){
            $response->body=json_decode($response->body);
            }   
        	if ($response->body->errcode != 0){
            	// var_dump($response->body);
            	// exit;
        	}
          
 
        return $response->body;
	}



    public static function getAllUsers($ACCESS_TOKEN,$refresh=false,$extPart=''){
         $allusers=Cache::get('all_users');
         if(empty($allusers) || $refresh){
                $allusers=[];
                $groups=Group::getAllGroups($ACCESS_TOKEN,$refresh); 
                $groups=array_values($groups);
                $percent=0;

                $threads=6;
                global $key;
                if($key=='woldy' && in_array('pthreads', get_loaded_extensions())){
                    $g=[];//组织架构分组
                    $f=[];//函数分组
                    $t=[];//线程分组
                    foreach ($groups as $idx=>$group) {
                        if(!empty($extPart)){//排除的分组
                            if(strrpos($group['fullname'], $extPart)){
                                continue;
                            }
                        }
                        if(!isset($g[$idx % $threads])){
                            $g[$idx % $threads]=[];
                        }
                        array_push($g[$idx % $threads],$group);
                    }

 
                    for($i=0;$i<$threads;$i++){
                        $f[$i]=function($p){
                            $partGroupUsers=[];
                            foreach ($p['g'] as $group) {
                                $users=Group::getGroupUsers($group['id'],$p['atk'],$p['refresh']);

                                foreach ($users as $user) {
                                    array_push($partGroupUsers, json_decode(json_encode($user),true));
                                }
                                echo $p['i'].',';
                            }
                            return json_encode($partGroupUsers);
                        };

                        $p=['g'=>$g[$i],'atk'=>$ACCESS_TOKEN,'refresh'=>$refresh,'i'=>$i];
                        $t[$i]=new dThreads($f[$i],$p);
                        $t[$i]->start();
                    }


                    
                    while(count($t)>0) {
                        for($i=0;$i<$threads;$i++){
                            if(isset($t[$i])  && !$t[$i]->runing){
                                echo "\n------{$i}-------\n";
                                    $res=json_decode($t[$i]->result,true);
                                    $allusers=array_merge($allusers,$res);
                                     echo '.';
    
                                //$t[$i]->kill();
                                unset($t[$i]);
                            }
                        }
                    }
             
                }else{
                    foreach ($groups as $idx=>$group) {
                        if(intval($idx*100/count($groups))>$percent){
                            $percent=intval($idx*100/count($groups));
                            echo '.';
                            if($percent % 33==0){
                                echo "\n";
                            }
                        }
                        if(!empty($extPart)){
                            if(strrpos($group['fullname'], $extPart)){
                                continue;
                            }
                        }

                        $users=Group::getGroupUsers($group['id'],$ACCESS_TOKEN,$refresh);
                        foreach ($users as $user) {
                            array_push($allusers, json_decode(json_encode($user),true));
                        }
                    }  
                }



                Cache::put('all_users', $allusers,1160);  
         }
         return $allusers;
    }

    /**
     * 创建用户
     * @Author   Woldy
     * @DateTime 2016-08-16T13:56:23+0800
     * @return   [type]                   [description]
     * // {
//     "userid": "zhangsan",
//     "name": "张三",
//     "orderInDepts" : "{1:10, 2:20}",
//     "department": [1, 2],
//     "position": "产品经理",
//     "mobile": "15913215421",
//     "tel" : "010-123333",
//     "workPlace" :"",
//     "remark" : "",
//     "email": "zhangsan@gzdev.com",
//     "jobnumber": "111111",
//     "isHide": false,
//     "isSenior": false,
//     "extattr": {
//                 "爱好":"旅游",
//                 "年龄":"24"
//                 }
// }
     */
    public static function addUser($ACCESS_TOKEN,$user){
        $response = Request::post('https://oapi.dingtalk.com/user/create?access_token='.$ACCESS_TOKEN)
            ->body(json_encode($user))
            ->sends('application/json','application/json')
            ->send();
        if ($response->hasErrors()){
            // var_dump($response);
            // exit;
        }
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }   
        if ($response->body->errcode != 0){
            // var_dump($response->body);
            // exit;
        }
        return $response->body;
    }


    public static function updateUser($ACCESS_TOKEN,$user){
        $response = Request::post('https://oapi.dingtalk.com/user/update?access_token='.$ACCESS_TOKEN)
            ->body(json_encode($user),'json')
            ->sends('application/json')
            ->send();
               
 
 
        if ($response->hasErrors()){
            // var_dump($response);
            // exit;
        }

        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }   
        if ($response->body->errcode != 0){
        	        var_dump($user);
        var_dump($response->body);
            var_dump($response->body);
            exit;
        }



        return $response->body;
    }


    /**
     * [getUserInfoBySns description]
     * @Author   Woldy
     * @DateTime 2016-08-10T13:53:11+0800
     * @param    [type]                   $SNS_TOKEN [description]
     * @return   [type]                              [description]
     */
    public static function getUserInfoBySns($SNS_TOKEN){
            $param=http_build_query(
                array(
                    'sns_token'=>$SNS_TOKEN
                )
            );
 
            $response = Request::get('https://oapi.dingtalk.com/sns/getuserinfo?'.$param)->send();
            if ($response->hasErrors()){
                var_dump($response);
                exit;
            }
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }   
            if ($response->body->errcode != 0){
               return $response->body;
            }

            $userinfo = $response->body->user_info;
 
        return $userinfo;
    }

    /**
     * 根据unionId获取员工信息
     * @Author   Woldy
     * @DateTime 2016-08-12T19:16:16+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    [type]                   $unionid      [description]
     * @return   [type]                                 [description]
     */
    public static function  getUserIdByUnionId($ACCESS_TOKEN,$unionid){
            $param=http_build_query(
                array(
                    'access_token'=>$ACCESS_TOKEN,
                    'unionid'=>$unionid
                )
            );
 
            $response = Request::get('https://oapi.dingtalk.com/user/getUseridByUnionid?'.$param)->send();
            if ($response->hasErrors()){
                var_dump($response);
                exit;
            }
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }   
            if ($response->body->errcode != 0){
                return $response->body;
            }
            $userid = $response->body->userid;
 
        return $userid;       
    }

    public static function delUserByIds($ACCESS_TOKEN,$ids){
        if(!is_array($ids)){
            $ids=explode(',', $ids);
        }
        foreach ($ids as $id) {
            $param=http_build_query(
                array(
                    'access_token'=>$ACCESS_TOKEN,
                    'userid'=>$id
                )
            );
            //die('https://oapi.dingtalk.com/user/delete?'.$param);
            $response = Request::get('https://oapi.dingtalk.com/user/delete?'.$param)->send();
            if ($response->hasErrors()){
               // var_dump($response);
               // exit;
            }
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }   
            if ($response->body->errcode != 0){
                //return $response->body;
            }
        }

        return $response->body;
    }


    public static function sendToConversation($ACCESS_TOKEN){
        $param=[
            'sender'=>'manager7108',
            'cid'=>'chate86e9bfcdfd212c3a1eec38a3cb89b8e',
            'msgtype'=>'text',
            'text'=>[
                'content'=>'test'
            ]
        ];
        $response = Request::post('https://oapi.dingtalk.com/message/send_to_conversation?access_token='.$ACCESS_TOKEN)
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


    public static function createChat($ACCESS_TOKEN,$ids,$chat_title='噗~'){
        if(!is_array($ids)){
            $ids=explode(',', $ids);
        }
        $param=[
            "name"=> $chat_title,
            "owner"=> $ids[0],
            "useridlist"=>$ids
        ];
        $response = Request::post('https://oapi.dingtalk.com/chat/create?access_token='.$ACCESS_TOKEN)
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

}