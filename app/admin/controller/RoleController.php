<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础开发管理平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\extend\helpers\DataScopeHelper;
use app\admin\extend\helpers\TraitActionHelper;
use app\admin\extend\services\AdminRoleService;
use app\admin\extend\services\RoleMenuService;
use app\admin\extend\services\RoleStructService;
use app\admin\validate\RoleValidate;
use app\common\helpers\Result;
use app\common\model\Role;
use app\Request;
use think\facade\Config;


class RoleController extends BaseController
{
    use TraitActionHelper;
    protected $model = Role::class;
    protected $validate = RoleValidate::class;

    /**
     * 首页渲染
     * @param Request $request
     * @return string
     */
    protected function indexRender(Request $request): string
    {
        $root_id = Config::get('system.root_role_id');
        return $this->render('',['root_id'=>$root_id]);
    }

    /**
     * 角色授权
     * @return string|\think\response\Json
     */
    public function authAction()
    {
        if ($this->request->isPost()) {
            $role_id = $this->request->post('id', 0);
            $treeId = $this->request->post('treeId', '');
            $result = (new RoleMenuService())->update($role_id,$treeId);
            if(!$result){
                return Result::error('授权发生错误');
            }
            return Result::success();
        } else {
            $role_id = $this->request->get('role_id', 0);
            if (!$role_id) return $this->toError('参数错误');
            $info = $this->model::bFind($role_id);
            if (empty($info)) return $this->toError('角色信息不存在');
            $menuList = (new RoleMenuService())->getRoleMenuList($role_id);
            return $this->render("", ['info' => $info, 'menuList' => implode(',', $menuList)]);
        }
    }

    /**
     * 角色数据权限
     * @return string|\think\response\Json
     */
    public function datascopeAction()
    {
        if($this->request->isPost()){
            $role_id = $this->request->post('id', 0);
            if (!$role_id) {
                return Result::error('参数错误');
            }
            $info = Role::bFind($role_id);
            if (empty($info)) {
                return $this->toError('角色信息不存在');
            }
            $dataList = DataScopeHelper::typeList();//数据范围列表
            $data_scope = $this->request->post('data_scope','');
            if(!$data_scope || !array_key_exists($data_scope,$dataList)){
                return Result::error('请选择数据范围');
            }
            $treeId = $this->request->post('treeId', '');
            $result = (new RoleStructService())->update($role_id, $data_scope=='8'?$treeId:'');
            if(!$result){
                return Result::error('发生错误了');
            }

            $result = Role::bUpdate(['id'=>$role_id,'data_scope'=>$data_scope]);
            if($result === false){
                return Result::error('数据保存失败');
            }

            return Result::success();
        }else{
            $role_id = $this->request->get('role_id', 0);
            if (!$role_id) {
                return $this->toError('参数错误');
            }
            $info = Role::bFind($role_id);
            if (empty($info)) {
                return $this->toError('角色信息不存在');
            }
            $typeList = DataScopeHelper::typeList();//数据范围列表
            $userStruct = RoleStructService::getRoleStructList($role_id);
            return $this->render("", ['info' => $info, 'typeList' => $typeList, 'userStruct' => implode(',', $userStruct)]);
        }
    }

    /**
     * 删除前判断
     * @param array $data
     * @return bool|string
     */
    protected function deleteBefore(array $data)
    {
        $root_id = Config::get('system.root_role_id');
        if($data['id'] == $root_id){
            return '默认超管角色无法删除';
        }
        return true;
    }

    /**
     * 删除角色后
     * @param array $data
     */
    protected function deleteAfter(array $data): void
    {
        //删除对应的管理员角色信息
        (new AdminRoleService())->deleteByRole($data['id']);

        //删除对应的权限菜单信息
        (new RoleMenuService())->deleteByRole($data['id']);

        //删除对应角色数据权限信息
        (new RoleStructService())->deleteByRole($data['id']);
    }
}
