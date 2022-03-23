<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\services;


use app\common\model\Menu;
use think\facade\Db;

class MenuService
{
    /**
     * 菜单类型
     * @return array
     */
    public function typeList():array
    {
        return ['M' => '目录', 'C' => '菜单', 'F' => '按钮'];
    }

    /**
     * 获取所有菜单，用于树形组件使用
     * @param bool $root
     * @return array
     */
    public function getList(bool $root = false):array
    {
        try {
            $list = Db::table(Menu::tableName())->field(['id', 'parent_id', 'name'])->order(['parent_id'=>'asc','listsort'=>'asc','id'=>'asc'])->select()->toArray();
        } catch (\Exception $exception){
            $list = [];
        }
        if ($root) {
            $first = [
                'id' => 0,
                'parent_id' => -1,
                'name' => '顶级菜单'
            ];
            array_unshift($list, $first);
        }
        return $list;
    }
}