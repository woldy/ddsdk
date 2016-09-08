<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
use Woldy\ddsdk\Components\Group;
use Log;
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
                Cache::put('all_groups', $result,200);              
            }else{
                $result=$allgroups;
            }
            return  $result;
	}

    /**
     *  
     * @Author   根据部门名称获取id
     * @DateTime 2016-09-02T10:35:19+0800
     * @param    [type]                   $groupname    [description]
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    boolean                  $refresh      [description]
     * @return   [type]                                 [description]
     */
    public static function getGroupByName($namepart,$ACCESS_TOKEN,$parentid=1,$refresh=false){

            if(!is_array($namepart)){
                $namepart=explode('-',$namepart);
            }

            $group=Cache::get('group_name_'.implode('-', $namepart));
            if(empty($group) || $refresh){
                $groups=self::getAllGroups($ACCESS_TOKEN,$refresh);
                if($refresh){
                //var_dump($group);
                //exit;
                }
                foreach ($groups as $group) {
                if($group->name==$namepart[0]  && ($group->parentid[0]==$parentid || $group->parentid==$parentid)){
                    array_shift($namepart);
                    if(count($namepart)==0){
                        //return self::getGroupById($group->id,$ACCESS_TOKEN);
                        Cache::put('group_name_'.implode('-', $namepart), $group,200); 
                        return $group;
                    }else{
                        return self:: getGroupByName($namepart,$ACCESS_TOKEN,$group->id,$refresh);
                    }
                    break;
               }
            }
           }else{
            return $group;
           }
       

            // var_dump($namepart);
            // var_dump($parentid);
            $add=self::createGroup($namepart[0],$parentid,$ACCESS_TOKEN);
            if($add->errcode==0){
                echo 'add group: '.self::getGroupById($parentid,$ACCESS_TOKEN)['fullname'].'-'.$namepart[0]."\n";
                Log::info("ding|group_add|".self::getGroupById($parentid,$ACCESS_TOKEN)['fullname'].'-'.$namepart[0]);
                return self::getGroupByName($namepart,$ACCESS_TOKEN,$parentid,true);
            }else{
                echo 'can\'t  found department: ';
                //var_dump($parentid);
                var_dump(self::getGroupById($parentid,$ACCESS_TOKEN)['fullname']);
                var_dump($namepart);       
                var_dump($add);        
            }

            exit;
            
    }

 
    public static function createGroup($name,$parentid,$ACCESS_TOKEN){
            $param=array(
                'access_token' =>$ACCESS_TOKEN, 
                'name'=>$name,
                'parentid'=>$parentid,
            );

            $response = Request::post('https://oapi.dingtalk.com/department/create?access_token='.$ACCESS_TOKEN)
                ->body(json_encode($param))
                ->sendsJson()
                ->send();
            if ($response->hasErrors()){
                // var_dump($response);
                // exit;
            }
            if ($response->body->errcode != 0){
                // var_dump($response->body);
                // exit;
            }

            return $response->body;
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
    public static function getGroupById($groupid,$ACCESS_TOKEN,$sub=true,$refresh=false){
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
            if($sub){
                $group['sub_groups']=self::getSubGroups($groupid,$ACCESS_TOKEN,$refresh);
            }
            Cache::put('group_'.$groupid, $group,300);  
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
    

 
    public static function getGroupUsers($groupid,$ACCESS_TOKEN,$refresh=false){
        $groupusers=Cache::get('group_users_'.$groupid);
        if(empty($groupusers) || $refresh){
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
            $groupusers = $response->body->userlist;
            Cache::put('group_users_'.$groupid,$groupusers,30);  
        }            
        return  $groupusers;
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