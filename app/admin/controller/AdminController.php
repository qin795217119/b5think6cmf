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
use app\admin\extend\services\AdminPosService;
use app\admin\extend\services\AdminRoleService;
use app\admin\extend\services\AdminStructService;
use app\admin\extend\services\StructService;
use app\admin\validate\AdminValidate;
use app\common\cache\PositionCache;
use app\common\helpers\Result;
use app\common\model\Admin;
use app\common\model\Role;
use think\facade\Config;
use think\facade\Db;

class AdminController extends BaseController
{
    use TraitActionHelper;

    protected $model = Admin::class;
    protected $validate = AdminValidate::class;

    /**
     * 选择人员视图
     * @return string
     */
    public function treeAction(){
        $mult = $this->request->get('mult',0);
        $ids = $this->request->get('ids', '');
        return $this->render('',['user_ids'=>$ids,'mult'=>$mult]);
    }

    /**
     * 人员视图获取人员列表
     * @return string|\think\response\Json
     * @throws \Exception
     */
    public function ajaxtreelistAction(){
        if(!$this->request->isPost()){
            return $this->toError('请求类型错误');
        }
        $params = [];
        $post = $this->request->post();

        $userIdList = $this->listStructParse($post);
        if($userIdList === false){
            $params['where']['id'] = 0;
        }elseif($userIdList){
            $params['in']['id'] = implode(',', array_unique($userIdList));
        }
        $params['like']['realname'] = $post['like']['realname']??'';
        $params['where']['status'] = 1;

        $params['field'] = 'id,realname,status,create_time';
        $query = Db::table($this->model::tableName());
        $query = $this->indexWhere($query, $params);
        $root_id = Config::get('system.root_admin_id');
        $query = $query->where('id','<>',$root_id);
        $pageSize = intval($post['pageSize'] ?? 10);
        $pageNum = intval($post['pageNum'] ?? 1);
        $pageNum = $pageNum < 1 ? 1 : $pageNum;
        $count = $query->count();
        $query = $query->page($pageNum, $pageSize);
        $list = $query->select()->toArray();
        $list = $this->indexAfter($list);
        return Result::success('操作成功',$list, ['total' => $count]);
    }
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
        $structUserList = $this->listStructParse($params);
        if($structUserList === false){
            $params['where']['id'] = 0;
        }elseif($structUserList){
            $userIdList = array_merge($userIdList,$structUserList);
        }

        if($userIdList){
            $params['in']['id'] = implode(',', array_unique($userIdList));
        }
        return $params;
    }

    /**
     * 列表查询时，组织架构处理
     * @param $post
     * @return array|false
     */
    protected function listStructParse($post){
        $userIdList = [];
        //组织架构处理
        $contains = $post['contains']??0;
        $root_struct_id = Config::get('system.root_struct_id');
        $struct_id = $post['structId'] ?? '';
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
                $userIdList = $list?:false;
            }
        }
        return $userIdList;
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
        $posService = new AdminPosService();
        foreach ($list as $key => $value) {
            $structInfo = $structService->getStructByAdminId($value['id'], true);
            $struct_name = '';
            if($structInfo){
                $struct_name = $structInfo['name'];
                if($structInfo['parent_name']){
                    $parent_name = explode(',',$structInfo['parent_name']);
                    $parent_name = array_pop($parent_name);
                    $struct_name = $parent_name.'/'.$struct_name;
                }
            }
            $value['struct_name'] = $struct_name;

            $roleList = $roleService->getRoleByAdmin($value['id'], true);
            $roleList = $roleList ? array_column($roleList, 'name') : [];
            $value['role_name'] = implode(',', $roleList);

            $posInfo = $posService->getPosByAdmin($value['id'],true);
            $value['pos_name'] = $posInfo?$posInfo['name']:'';
            $value['pos_key'] = $posInfo?$posInfo['poskey']:'';

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
        $posList = PositionCache::lists();
        return $this->render('', ['roleList' => $roleList,'posList'=>$posList]);
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
        $posList = PositionCache::lists();
        $posId = (new AdminPosService())->getPosByAdmin($info['id']);
        return $this->render('', ['info'=>$info,'roleList' => $roleList,'structInfo'=>$structInfo,'roleId'=>$roleId,'posList'=>$posList,'posId'=>$posId]);
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
            $pos = $this->request->post('pos', '');

            (new AdminRoleService())->update($data['id'], $roles);
            (new AdminStructService())->update($data['id'], $struct);
            (new AdminPosService())->update($data['id'], $pos);
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

        //删除管理员岗位
        (new AdminPosService())->deleteByAdmin($data['id']);
    }
}
