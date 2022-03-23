<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\services;


use think\facade\Db;

class RoleStructService
{
    /**
     * 更新授权信息
     * @param $role_id
     * @param string|array $struct_id
     * @return bool
     */
    public function update($role_id, $struct_id = null): bool
    {
        if (!$role_id) {
            return false;
        }

        if(!$this->deleteByRole($role_id)){
            return false;
        }

        if (!$struct_id) return true;

        if (!is_array($struct_id)) {
            $struct_id = explode(',', $struct_id);
        }
        $struct_id = array_unique($struct_id);
        $data = [];
        foreach ($struct_id as $id) {
            if ($id) {
                $data[] = ['role_id' => $role_id, 'struct_id' => $id];
            }
        }
        Db::table('b5net_role_struct')->insertAll($data);
        return true;
    }

    /**
     * 获取角色分组的菜单权限ID
     * @param $roleId
     * @return array
     */
    public static function getRoleStructList($roleId): array
    {
        $list = [];
        if ($roleId) {
            try {
                if(is_array($roleId)){
                    $list = Db::table('b5net_role_struct')->whereIn('role_id', $roleId)->select()->toArray();
                }else{
                    $list = Db::table('b5net_role_struct')->where('role_id', $roleId)->select()->toArray();
                }
                if ($list) {
                    $list = array_unique(array_column($list, 'struct_id'));;
                }
            } catch (\Exception $exception) {

            }
        }
        return $list ?: [];
    }

    /**
     * 删除某个角色的数据权限信息
     * @param $role_id
     * @return bool
     */
    public function deleteByRole($role_id):bool{
        if($role_id){
            try {
                Db::table('b5net_role_struct')->where('role_id', $role_id)->delete();
            } catch (\Exception $exception) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 删除某个组织的数据权限信息
     * @param $struct_id
     * @return bool
     */
    public function deleteByStruct($struct_id):bool{
        if($struct_id){
            try {
                Db::table('b5net_role_struct')->where('struct_id', $struct_id)->delete();
            } catch (\Exception $exception) {
                return false;
            }
            return true;
        }
        return false;
    }
}
