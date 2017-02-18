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
            if(empty($allgroups) || $refresh){
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

                if(!is_object($response->body)){
                    $response->body=json_decode($response->body);
                }

                if ($response->body->errcode != 0){
                    var_dump($response->body);
                    exit;
                }
                $allgroups = $response->body->department;  
                $groups=[];
                foreach ($allgroups as $group) {
                    $groups[$group->id]=json_decode(json_encode($group),true);
                    $groups[$group->id]['tmpid']=$group->id;
                    $groups[$group->id]['parent_ids']=[];
                    $groups[$group->id]['name_part']=[];
                    $groups[$group->id]['fullname']='';
                }


                $cover_all=false;
                while(!$cover_all){
                    $cover_all=true;
                    foreach ($groups as $key => $value) {
                        if($groups[$key]['tmpid']>1){
                            $cover_all=false;
                            array_unshift($groups[$key]['parent_ids'], $groups[$key]['tmpid']);
                            array_unshift($groups[$key]['name_part'], $groups[$groups[$key]['tmpid']]['name']);
                            $groups[$key]['tmpid']=$groups[$groups[$key]['tmpid']]['parentid'];
                        }else if($groups[$key]['tmpid']==1){
                            array_unshift($groups[$key]['parent_ids'], 1);
                            array_unshift($groups[$key]['name_part'], $groups[1]['name']);
                            $groups[$key]['fullname']=implode('-',$groups[$key]['name_part']);
                            $groups[$key]['tmpid']=0;
                        }
                    }
                }
                $allgroups=$groups;
                Cache::put('all_groups', $groups,200);  
                            
            }

            return  $allgroups;

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
    public static function getGroupByName($name,$ACCESS_TOKEN,$create=true,$refresh=false){
            $group=Cache::get('group_name_'.$name);

            if(empty($group) || $refresh){
                $groups=self::getAllGroups($ACCESS_TOKEN,$refresh);
                foreach ($groups as $group) {
                    if($group['fullname']==$name){
                        Cache::put('group_name_'.$name,$group,3600);
                        return $group;
                    }
                }
            }else{
                return $group;
            }

            if (!$create) {
                return false;
            }
       
            $name_part=explode('-',$name);

 


            $add_group=[];

            while (count($name_part)>0) {
                array_unshift($add_group,array_pop($name_part));
                foreach ($groups as $group) {
                    if($group['fullname']==implode('-',$name_part)){//查找上级
                        $pgroup=json_decode(json_encode(['id'=>$group['id']]));
                        foreach ($add_group as $add_name) {
                                $add=self::createGroup($add_name,$pgroup->id,$ACCESS_TOKEN);
                                if($add->errcode==0){
                                    $pgroup=self::getGroupByName($group['fullname'],$ACCESS_TOKEN,true,true);
                                    echo 'add group: '.$group['fullname'].'-'. $add_name."\n";
                                    Log::info("ding|group_add|".$group['fullname'].'-'. $add_name);
                                }else if($add->errcode==60008){
                                    $pgroup=self::getGroupByName($group['fullname'],$ACCESS_TOKEN,true,true);
                                }else{
                                
                                    echo 'can\'t  add department: ';
                                    var_dump($pgroup);
                                    var_dump($add);
                                    var_dump($ACCESS_TOKEN);
                                    //var_dump(self::getGroupById($pgroup->id,$ACCESS_TOKEN)['fullname']);
                                    var_dump($group['fullname'].'-'. $add_name);       
                                        
                            }
                        }
                        $pgroup=json_decode(json_encode($pgroup),true);
                        // var_dump($pgroup);
                        // exit;
                        return $pgroup;
                        //return self::getGroupById($pgroup->id,$ACCESS_TOKEN,false,true);
                    }

                }
            }
    
            return [];
    }

 
    public static function createGroup($name,$parentid,$ACCESS_TOKEN){
            $param=array(
                'access_token' =>$ACCESS_TOKEN, 
                'name'=>$name,
                'parentid'=>$parentid,
            );
 
            $response = Request::post('https://oapi.dingtalk.com/department/create?access_token='.$ACCESS_TOKEN)
            ->body(json_encode($param),'json')
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
                var_dump($parentid);
                var_dump($name);
                var_dump($response->body);
                exit;
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
        $subflag=$sub?'sub_':'nosub_';
        $group=Cache::get('group_'.$subflag.$groupid);
        if(empty($group) || $refresh){
            $groups=self::getAllGroups($ACCESS_TOKEN,$refresh);
            if(!isset($groups[$groupid])){
                $groups=self::getAllGroups($ACCESS_TOKEN,true);
                if(!isset($groups[$groupid])){
                    return [
                        'id'=>$groupid,
                        'fullname'=>'unknown',
                        'name'=>'unknown'
                    ];
                }
            }
            $group=$groups[$groupid];
            if($sub){
                $group['sub_groups']=self::getSubGroups($groupid,$ACCESS_TOKEN,1,$refresh);
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
    public static function getSubGroups($groupid,$ACCESS_TOKEN,$deep=1,$refresh=false){
        $groups=self::getAllGroups($ACCESS_TOKEN,$refresh);
        $subgroups=[];
        foreach ($groups as $group) {
            if(in_array($groupid, $group['parent_ids'])){    //groupid在subgroup的parentid(1,aaa,bbb,ccc,parentid,eee,fff,subid)中
                if($deep==0){//全部子部门
                   array_push($subgroups,$group);   
                }else{//指定深度内
                    if(count($group['parent_ids'])>$deep){ 
                        if($group['parent_ids'][count($group['parent_ids'])-$deep-1]==$groupid){
                            $gidx=array_search($groupid,$group['parent_ids']);
                            $sidx=array_search($group['id'],$group['parent_ids']);
                            if($sidx-$gidx<=$deep){
                                array_push($subgroups,$group); 
                            }
                        }
                    }
                }
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
            // echo 'x';
            // echo "\nhttps://oapi.dingtalk.com/user/list?".$param;
            $response = Request::get('https://oapi.dingtalk.com/user/list?'.$param)->send();

            // echo 'o';
            if ($response->hasErrors()){
                var_dump('https://oapi.dingtalk.com/user/list?'.$param);
                var_dump($response);
                exit;
            }

            if(!isset($response->body->errcode)){
                $response->body=json_decode($response->body);         
            }
 

            if ($response->body->errcode != 0){
                var_dump('https://oapi.dingtalk.com/user/list?'.$param);
                var_dump($response->body);
                exit;
            }
            $groupusers = $response->body->userlist;
            Cache::put('group_users_'.$groupid,$groupusers,3000);  
        }else{
            //echo 'x';
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
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }   
            if ($response->body->errcode != 0){
                var_dump($response->body);
                exit;
            }
            $result = $response->body;            
            return  $result;
    }

    /**
     * 更新部门
     * @Author   woldy
     * @DateTime 2016-09-29T22:51:00+0800
     * @param    [type]                   $group        [description]
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @return   [type]                                 [description]
     */
    public static function updateGroup($group,$ACCESS_TOKEN){
            $response = Request::post('https://oapi.dingtalk.com/department/update?access_token='.$ACCESS_TOKEN)
                ->body(json_encode($group))
                ->sends('application/json')
                ->send();
            if ($response->hasErrors()){
               // echo $group['id'].',';
                     var_dump($group);
                 var_dump($response);
                //exit;
            }
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }   
            if ($response->body->errcode != 0){
                //echo $group['id'].',';
                var_dump($group);
                 var_dump($response->body);
                //exit;
            }
            return $response->body;
    }

    public static function getGroupInfo($groupid,$ACCESS_TOKEN){
            $param=http_build_query(
                array(
                    'access_token'=>$ACCESS_TOKEN,
                    'id'=>$groupid
                )
            );
            $response = Request::get('https://oapi.dingtalk.com/department/get?'.$param)->send();
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
            $result = $response->body;            
            return  $result;
    }
 
}