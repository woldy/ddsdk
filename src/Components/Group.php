<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
use Woldy\ddsdk\Components\Group;
class group{
    /**
     * 获取所有部门
     * @Author   Woldy
     * @DateTime 2016-08-31T10:36:26+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    boolean                  $refresh      [description]
     * @return   [type]                                 [description]
     */
	public static function getAllGroups($ACCESS_TOKEN,$refresh=false){
            $allgroups=Cache::get('all_groups');
            if(empty($allgroup) || $refresh){
                $param=http_build_query(
                    array(
                        'access_token'=>$ACCESS_TOKEN
                    )
                );
            
                $response = Request::get('https://oapi.dingtalk.com/department/list?'.$param)->send();
                if ($response->hasErrors()){
                    var_dump($response);
                    exit;
                }
                if ($response->body->errcode != 0){
                    var_dump($response->body);
                    exit;
                }
                $result = $response->body->department;  
                Cache::put('all_groups', $result,60);              
            }else{
                $result=$allgroups;
            }
            return  $result;
	}

    /**
     * 根据ID获取部门信息
     * @Author   Woldy
     * @DateTime 2016-08-31T10:36:38+0800
     * @param    [type]                   $groupid      [description]
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    boolean                  $refresh      [description]
     * @return   [type]                                 [description]
     */
    public static function getGroupById($groupid,$ACCESS_TOKEN,$refresh=false){
        $group=Cache::get('group_'.$groupid);
        if(empty($group) || $refresh){
            $groups=self::getAllGroups($ACCESS_TOKEN,$refresh);
            $groups=json_decode(json_encode($groups),TRUE);
            $groupinfo='';
            foreach ($groups as $group) {
                if($group['id']==$groupid){
                    $groupinfo=$group;
                    break;
                }
            }

            $group['fullname']='';
            $group['parent_ids']=[];
            if(isset($group['parentid'])){
                $group['parent']=self::getGroupById($group['parentid'],$ACCESS_TOKEN,$refresh);
                $g= $group;
                while (isset($g['parent'])) {
                    $group['fullname']=$g['parent']['name'].'-'.$group['fullname'];
                    array_push($group['parent_ids'],$g['parentid']);
                    $g=$g['parent'];
                } 
            }
            $group['fullname']=$group['fullname'].'-'.$group['name'];
            $group['fullname']=str_replace('--', '-', $group['fullname']);
            $group['sub_groups']=self::getSubGroups($groupid,$ACCESS_TOKEN,$refresh);
            Cache::put('group_'.$groupid, $group,5);  
        }
        return $group;
    }


    /**
     * 获取子部门信息
     * @Author   Woldy
     * @DateTime 2016-08-31T10:37:03+0800
     * @param    [type]                   $groupid      [description]
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    boolean                  $refresh      [description]
     * @return   [type]                                 [description]
     */
    public static function getSubGroups($groupid,$ACCESS_TOKEN,$refresh=false){
        $groups=self::getAllGroups($ACCESS_TOKEN,$refresh);
        $groups=json_decode(json_encode($groups),TRUE);
        $subgroups=[];
        foreach ($groups as $group) {
            if(!isset($group['parentid'])) $group['parentid']=0;
            if($group['parentid']==$groupid){
                array_push($subgroups,$group);
            }
        }
        return $subgroups;
    }
 
 
    public static function getGroupUsers($groupid,$ACCESS_TOKEN){
            $param=http_build_query(
                array(
                    'access_token'=>$ACCESS_TOKEN,
                    'department_id'=>$groupid
                )
            );
            $response = Request::get('https://oapi.dingtalk.com/user/list?'.$param)->send();
            if ($response->hasErrors()){
                var_dump($response);
                exit;
            }
            if ($response->body->errcode != 0){
                var_dump($response->body);
                exit;
            }
            $result = $response->body->userlist;            
            return  $result;
    }

    public static function delGroup($groupid,$ACCESS_TOKEN){
            $param=http_build_query(
                array(
                    'access_token'=>$ACCESS_TOKEN,
                    'id'=>$groupid
                )
            );
            $response = Request::get('https://oapi.dingtalk.com/department/delete?'.$param)->send();
            if ($response->hasErrors()){
                var_dump($response);
                exit;
            }
            if ($response->body->errcode != 0){
                var_dump($response->body);
                exit;
            }
            $result = $response->body;            
            return  $result;
    }
 
}