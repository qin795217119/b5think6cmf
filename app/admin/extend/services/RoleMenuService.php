<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\services;

use think\facade\Db;

class RoleMenuService
{
    /**
     * 更新授权信息
     * @param $role_id
     * @param string|array $treeId
     * @return bool
     */
    public function update($role_id, $treeId = null):bool
    {
        if (!$role_id) return false;

        if(!$this->deleteByRole($role_id)){
            return false;
        }

        if (!$treeId) return true;
        if (!is_array($treeId)) {
            $treeId = explode(',', $treeId);
        }
        $treeId = array_unique($treeId);
        $data = [];
        foreach ($treeId as $menu_id) {
            if ($menu_id) {
                $data[] = ['role_id' => $role_id, 'menu_id' => $menu_id];
            }
        }
        Db::name('b5net_role_menu')->insertAll($data);
        return true;
    }

    /**
     * 获取角色分组的菜单权限ID
     * @param $role_id
     * @return array
     */
    public function getRoleMenuList($role_id): array
    {
        $list = [];
        if(is_array($role_id)){
            foreach ($role_id as $role){
                try {
                    $chList = Db::table('b5net_role_menu')->where('role_id', $role)->select()->toArray();
                } catch (\Exception $exception) {
                    $chList = [];
                }
                if ($chList) {
                    $chList = array_unique(array_column($chList, 'menu_id'));
                    $list = array_merge($list,$chList);
                }
            }
            $list = array_unique($list);
        }else {
            try {
                $list = Db::table('b5net_role_menu')->where('role_id', $role_id)->select()->toArray();
            } catch (\Exception $exception) {

            }
            if ($list) {
                $list = array_unique(array_column($list, 'menu_id'));
            }
        }
        return $list ?: [];
    }

    /**
     * 删除某个角色的授权信息
     * @param $role_id
     * @return bool
     */
    public function deleteByRole($role_id):bool{
        if($role_id){
            try {
                Db::table('b5net_role_menu')->where('role_id', $role_id)->delete();
            }catch (\Exception $exception){
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 删除某个菜单的授权信息
     * @param $menu_id
     * @return bool
     */
    public function deleteByMenu($menu_id):bool{
        if($menu_id){
            try {
                Db::table('b5net_role_menu')->where('menu_id', $menu_id)->delete();
            }catch (\Exception $exception){
                return false;
            }
            return true;
        }
        return false;
    }
}