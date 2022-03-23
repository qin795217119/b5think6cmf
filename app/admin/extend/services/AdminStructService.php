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

/**
 * 人员组织机构管理
 * Class AdminStructService
 * @package App\Services
 */
class AdminStructService
{
    /**
     * 更新信息
     * @param $admin_id
     * @param $struct_id
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function update($admin_id, $struct_id): bool
    {
        if (!$admin_id) return false;
        Db::table('b5net_admin_struct')->where('admin_id', $admin_id)->delete();
        if (!$struct_id) return true;
        Db::table('b5net_admin_struct')->insert(['admin_id' => $admin_id, 'struct_id' => $struct_id]);
        return true;
    }

    /**
     * 获取某个人员的组织部门
     * @param $admin_id
     * @param false $showStruct
     * @return array|false|mixed|Db|\think\Model
     */
    public function getStructByAdminId($admin_id, $showStruct = false)
    {
        if (!$admin_id) return false;
        try {
            $info = Db::table('b5net_admin_struct')->where('admin_id', $admin_id)->find();
        } catch (\Exception $exception) {
            return false;
        }
        if (!$info) return false;
        if (!$showStruct) return $info['struct_id'];
        $struct = Struct::bFind($info['struct_id']);
        return $struct ?: [];
    }

    /**
     * 获取某个组织下的用户
     * @param $struct_id
     * @return array
     */
    public function getAdminIdByStructId($struct_id): array
    {
        if (!$struct_id) return [];

        try {
            if(is_array($struct_id)){
                $list = Db::table('b5net_admin_struct')->whereIn('struct_id', $struct_id)->select()->toArray();
            }else{
                $list = Db::table('b5net_admin_struct')->where('struct_id', $struct_id)->select()->toArray();
            }
        } catch (\Exception $exception) {
            return [];
        }
        return $list ? array_column($list, 'admin_id') : [];
    }

    /**
     * 删除某个角色的组织信息
     * @param $admin_id
     * @return bool
     */
    public function deleteByAdmin($admin_id): bool
    {
        if ($admin_id) {
            try {
                Db::table('b5net_admin_struct')->where('admin_id', $admin_id)->delete();
            } catch (\Exception $exception) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 删除某个组织的角色信息
     * @param $struct_id
     * @return bool
     */
    public function deleteByStruct($struct_id): bool
    {
        if ($struct_id) {
            try {
                Db::table('b5net_admin_struct')->where('struct_id', $struct_id)->delete();
            } catch (\Exception $exception) {
                return false;
            }
            return true;
        }
        return false;
    }
}
