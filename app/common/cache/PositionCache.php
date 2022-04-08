<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\common\cache;


use app\common\model\Position;
use think\facade\Cache;
use think\facade\Db;

class PositionCache
{
    /**
     * 获取信息
     * @param string|null $id
     * @return array|mixed
     * @throws \Throwable
     */
    public static function get(string $id=null){
        if(!$id) return [];
        $list = self::lists();
        $list = $list?array_column($list,null,'id'):[];
        return isset($list[$id])?$list[$id]:[];
    }

    /**
     * 获取列表
     * @return array
     * @throws \Throwable
     */
    public static function lists():array{
        return Cache::remember('position_list',function (){
            $lists = Db::table(Position::tableName())->field(['id','name','poskey','status'])->order(['listsort'=>'asc','id'=>'asc'])->select()->toArray();
            return $lists?:[];
        });
    }

    /**
     * 清除所有
     */
    public static function clear(){
        Cache::delete('position_list');
    }
}
