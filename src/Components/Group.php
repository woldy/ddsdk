<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class group{
	public static function getAllGroups($ACCESS_TOKEN,$refresh=false){
            $allgroup=Cache::get('all_group');
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
                Cache::put('all_group', $result,60);              
            }else{
                $result=$allgroup;
            }
        return  $result;
	}

    public static function getGroupById($groupid,$ACCESS_TOKEN,$refresh=false){
        $groups=self::getAllGroups($ACCESS_TOKEN,$refresh);

        // var_dump($groups);
        // exit;
        $groups=json_decode(json_encode($groups),TRUE);
        $groupinfo='';
        foreach ($groups as $group) {
            if($group['id']==$groupid){
                $groupinfo=$group;
                break;
            }
        }

        $group['parent_str']='';
        $group['parent_ids']=[];
        if(isset($group['parentid'])){
            $group['parent']=self::getGroupById($group['parentid'],$ACCESS_TOKEN,$refresh);
            $g= $group;
            while (isset($g['parent'])) {
                $group['parent_str']=$g['parent']['name'].'-'.$group['parent_str'];
                array_push($group['parent_ids'],$g['parentid']);
                $g=$g['parent'];
            } 
        }
        $group['parent_str']=$group['parent_str'].'-'.$group['name'];
        $group['sub_groups']=self::getSubGroups($groupid,$ACCESS_TOKEN,$refresh);
        return $group;
    }

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
 
 
 
}