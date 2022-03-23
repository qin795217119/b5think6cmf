<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\services;

use app\common\model\Role;
use think\facade\Db;

class AdminRoleService
{
    /**
     * 更新信息
     * @param $admin_id
     * @param string $role_id
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function update($admin_id, string $role_id = ''): bool
    {
        if (!$admin_id) return false;
        Db::table('b5net_admin_role')->where('admin_id', $admin_id)->delete();
        if (!$role_id) return true;
        $role_id = explode(',',$role_id);
        foreach ($role_id as $role){
            Db::table('b5net_admin_role')->insert(['admin_id' => $admin_id, 'role_id' => $role]);
        }
        return true;
    }

    /**
     * 获取某个人员的角色列表
     * @param $admin_id
     * @param false $showRole 是否显示角色详细信息
     * @return array
     */
    public function getRoleByAdmin($admin_id, $showRole = false): array
    {
        if (!$admin_id) return [];

        try {
            $list = Db::table('b5net_admin_role')->where('admin_id', $admin_id)->select()->toArray();
        } catch (\Exception $exception) {
            return [];
        }

        if (!$showRole) {
            return $list ? array_column($list, 'role_id') : [];
        }
        $result = [];
        foreach ($list as $value) {
            $info = Role::bFind($value['role_id']);
            if ($info) {
                $result[] = $info;
            }
        }
        return $result;
    }

    /**
     * 获取某个角色下的所有用户ID
     * @param $role_id
     * @return array
     */
    public function getAdminIdByRoleId($role_id): array
    {
        if (!$role_id) return [];

        try {
            $list = Db::table('b5net_admin_role')->where('role_id', $role_id)->select()->toArray();
        } catch (\Exception $exception) {
            return [];
        }
        return $list ? array_column($list, 'admin_id') : [];
    }

    /**
     * 删除某个角色的管理员信息
     * @param $role_id
     * @return bool
     */
    public function deleteByRole($role_id): bool
    {
        if ($role_id) {
            try {
                Db::table('b5net_admin_role')->where('role_id', $role_id)->delete();
            } catch (\Exception $exception) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 删除某个管理员的角色信息
     * @param $admin_id
     * @return bool
     */
    public function deleteByAdmin($admin_id): bool
    {
        if ($admin_id) {
            try {
                Db::table('b5net_admin_role')->where('admin_id', $admin_id)->delete();
            } catch (\Exception $exception) {
                return false;
            }
            return true;
        }
        return false;
    }
}
