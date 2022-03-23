<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\services;


use app\common\model\Struct;
use think\facade\Db;

class StructService
{

    /**
     * 当修改组织构架时，修改子类所有的parent_name和levels
     * @param $pid
     * @return bool
     */
    public function updateExtendField($pid):bool{
        if(!$pid) return false;

        $parentInfo = Struct::bFind($pid);
        if(!$parentInfo) return false;

        $parent_name = trim($parentInfo['parent_name'].','.$parentInfo['name'],',');
        $levels = trim($parentInfo['levels'].','.$parentInfo['id'],',');
        try {
            $childList = Db::table(Struct::tableName())->where('parent_id',$pid)->select()->toArray();
        }catch (\Exception $exception){
            return  true;
        }
        foreach ($childList as $child){
            if($child['parent_name']!=$parent_name || $child['levels']!=$levels){
                $res = Struct::bUpdate(['id'=>$child['id'],'parent_name'=>$parent_name,'levels'=>$levels]);
                if($res){
                    $this->updateExtendField($child['id']);
                }
            }
        }
        return true;
    }

    /**
     * 获取某个组织的所有子组织
     * @param $id
     * @param bool $onlyId
     * @return array
     */
    public static function getChildList($id, $onlyId = false):array
    {
        $list = [];
        if ($id > 0) {
            try {
                $list = Db::table(Struct::tableName())->whereFindInSet('levels',$id)->select()->toArray();
            } catch (\Exception $exception){
                return [];
            }
            if($onlyId){
                $list= array_column($list,'id');
            }
        }
        return $list ?: [];
    }

    /**
     * 获取所有菜单，用于树形组件使用
     * @return array
     */
    public function getList():array
    {
        try {
            $list = Db::table(Struct::tableName())->field(['id', 'parent_id', 'name'])->order(['parent_id'=>'asc','listsort'=>'asc','id'=>'asc'])->select()->toArray();
        } catch (\Exception $exception){
            $list = [];
        }
        return $list;
    }
}
