<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\services;

use app\common\cache\PositionCache;
use think\facade\Db;

class AdminPosService
{
    /**
     * 更新信息
     * @param $admin_id
     * @param string $pos_id
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function update($admin_id, string $pos_id = ''): bool
    {
        if (!$admin_id) return false;
        Db::table('b5net_admin_pos')->where('admin_id', $admin_id)->delete();
        if (!$pos_id) return true;
        $pos_id = explode(',',$pos_id);
        foreach ($pos_id as $role){
            Db::table('b5net_admin_pos')->insert(['admin_id' => $admin_id, 'pos_id' => $role]);
        }
        return true;
    }

    /**
     * 获取某个人员的岗位列表
     * @param $admin_id
     * @param false $showPos
     * @return array|false|mixed
     * @throws \Throwable
     */
    public function getPosByAdmin($admin_id, $showPos = false)
    {
        if (!$admin_id) return [];

        try {
            $info = Db::table('b5net_admin_pos')->where('admin_id', $admin_id)->find();
        } catch (\Exception $exception) {
            return [];
        }
        if (!$info) return false;
        if (!$showPos) return $info['pos_id'];

        $posList = PositionCache::lists();
        $posList = array_column($posList,null,'id');

        return $posList[$info['pos_id']] ?? [];
    }

    /**
     * 删除某个岗位的管理员信息
     * @param $pos_id
     * @return bool
     */
    public function deleteByPos($pos_id): bool
    {
        if ($pos_id) {
            try {
                Db::table('b5net_admin_pos')->where('pos_id', $pos_id)->delete();
            } catch (\Exception $exception) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 删除某个管理员的岗位信息
     * @param $admin_id
     * @return bool
     */
    public function deleteByAdmin($admin_id): bool
    {
        if ($admin_id) {
            try {
                Db::table('b5net_admin_pos')->where('admin_id', $admin_id)->delete();
            } catch (\Exception $exception) {
                return false;
            }
            return true;
        }
        return false;
    }
}
