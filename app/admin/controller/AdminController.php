<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\extend\helpers\TraitActionHelper;
use app\admin\extend\services\AdminRoleService;
use app\admin\extend\services\AdminStructService;
use app\admin\extend\services\StructService;
use app\admin\validate\AdminValidate;
use app\common\helpers\Result;
use app\common\model\Admin;
use app\common\model\Role;
use think\facade\Config;

class AdminController extends BaseController
{
    use TraitActionHelper;

    protected $model = Admin::class;
    protected $validate = AdminValidate::class;

    /**
     * 首页渲染
     * @return string
     */
    protected function indexRender(): string
    {
        $root_id = Config::get('system.root_admin_id');
        $roleList = Role::bSelect();
        return $this->render('', ['root_id' => $root_id,'roleList'=>$roleList]);
    }

    /**
     * 首页列表查询前条件处理
     * @param array $params
     * @return array
     */
    protected function indexBefore(array $params): array
    {
        $userIdList = [];
        //角色处理
        $role_id = $params['role_id'] ?? '';
        if($role_id){
            $roleList = (new AdminRoleService())->getAdminIdByRoleId($role_id);
            if(!$roleList){
                $params['where']['id'] = 0;
                return $params;
            }
            $userIdList = $roleList;
        }

        //组织架构处理
        $contains = $params['contains']??0;
        $root_struct_id = Config::get('system.root_struct_id');
        $struct_id = $params['structId'] ?? '';
        if ($struct_id) {
            $structList = [];
            if($contains){
                if($root_struct_id != $struct_id){
                    //获取所有子组织
                    $structList = StructService::getChildList($struct_id,true);
                    $structList[] = $struct_id;
                }
            }else{
                $structList[] = $struct_id;

            }
            if($structList){
                //获取组织下的用户
                $list = (new AdminStructService())->getAdminIdByStructId($structList);
                if(!$list){
                    $params['where']['id'] = 0;
                    return $params;
                }
                $userIdList = array_merge($userIdList,$list);
            }
        }
        if($userIdList){
            $params['in']['id'] = implode(',', array_unique($userIdList));
        }
        return $params;
    }

    /**
     * 首页列表处理
     * @param array $list
     * @return array
     */
    protected function indexAfter(array $list): array
    {
        $structService = new AdminStructService();
        $roleService = new AdminRoleService();
        foreach ($list as $key => $value) {
            $structInfo = $structService->getStructByAdminId($value['id'], true);
            $value['struct_name'] = $structInfo ? $structInfo['name'] : '';

            $roleList = $roleService->getRoleByAdmin($value['id'], true);
            $roleList = $roleList ? array_column($roleList, 'name') : [];
            $value['role_name'] = implode(',', $roleList);
            $list[$key] = $value;
        }
        return $list;
    }

    /**
     * 添加页渲染
     * @return string
     */
    protected function addRender(): string
    {
        $roleList = Role::bSelect([], ['listsort' => 'asc']);
        return $this->render('', ['roleList' => $roleList]);
    }

    /**
     * 编辑页渲染
     * @param array $info
     * @return string
     */
    protected function editRender(array $info): string
    {
        $structInfo = (new AdminStructService())->getStructByAdminId($info['id'], true);
        $roleId = (new AdminRoleService())->getRoleByAdmin($info['id']);
        $roleList = Role::bSelect([], ['listsort' => 'asc']);
        return $this->render('', ['info'=>$info,'roleList' => $roleList,'structInfo'=>$structInfo,'roleId'=>$roleId]);
    }

    /**
     * 验证前进行数据处理
     * @param array $data
     * @param string $type
     * @return array
     */
    protected function validateBefore(array $data, string $type)
    {
        if (isset($data['password']) && !$data['password']) {
            if ($type == 'add') {
                $data['password'] = '123456';
            } else if ($type == 'edit') {
                unset($data['password']);
            }
            if(isset($data['realname']) && !$data['realname']){
                $data['realname'] = $data['username'];
            }
        }
        return $data;
    }

    /**
     * 添加和编辑保存前对数据进行处理
     * @param array $data
     * @param string $type
     * @return array|string
     */
    protected function saveBefore(array $data, string $type)
    {
        if($type == 'add' || $type == 'edit'){
            if(isset($data['password'])){
                if(!$data['password']) return '登录密码不能为空';
                $data['password'] = md5($data['password']);
            }
        }
        return $data;
    }

    /**
     * 添加和编辑后更新角色和组织信息
     * @param array $data
     * @param string $type
     * @throws \think\db\exception\DbException
     */
    protected function saveAfter(array $data, string $type): void
    {
        if ($type == 'add' || $type == 'edit') {
            $roles = $this->request->post('roles', '');
            $struct = $this->request->post('struct', '');

            (new AdminRoleService())->update($data['id'], $roles);
            (new AdminStructService())->update($data['id'], $struct);
        }
    }

    /**
     * 删除前判断
     * @param array $data
     * @return bool|string
     */
    protected function deleteBefore(array $data)
    {
        $root_id = Config::get('system.root_admin_id');
        if ($data['id'] == $root_id) {
            return '默认超级管理员无法删除';
        }
        return true;
    }

    /**
     * 删除后操作
     * @param array $data
     */
    protected function deleteAfter(array $data): void
    {
        //删除管理员角色
        (new AdminRoleService())->deleteByAdmin($data['id']);

        //删除管理员组织部门
        (new AdminStructService())->deleteByAdmin($data['id']);
    }
}
