<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\services;

use app\common\helpers\Result;
use app\common\model\Admin;
use think\captcha\facade\Captcha;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Validate;
use think\response\Json;

class AdminLoginService
{
    //错误信息
    public $message = '';

    //用户信息
    public $_user;

    //是否登录保存cookie
    public $cookie = false;

    /**
     * 登录操作
     * @return Json
     */
    public function login(): Json
    {

        $data = Request::post();
        if (!$this->validate($data)) {
            return Result::error($this->message);
        }
        $user = $this->getUser('username', $data['username']);
        if (!$user) {
            return Result::error('用户名或密码错误');
        }
        if(!$this->validatePassword($data['password'])){
            return Result::error($this->message);
        }

        if (!$this->loginSession($user['id'])) {
            return Result::error($this->message);
        }

        return Result::success('登录成功');
    }

    /**
     * 保存登录信息
     * @param $id
     * @return bool
     */
    public function loginSession($id): bool
    {
        $user = $this->getUser('id', $id);
        if (!$user) {
            $this->message = '用户信息错误';
            return false;
        }

        if ($user['status'] != 1) {
            $this->message = '用户已被禁用，无法登录';
            return false;
        }

        $dataScope = 0; //数据权限
        $roleId = [];//角色ID数组
        $is_admin = 0;//超级管理员或者超管角色
        $menuList = []; //权限列表
        $struct = (new AdminStructService())->getStructByAdminId($user['id'],true);//组织部门

        $root_admin_id = Config::get('system.root_admin_id');
        if($user['id'] == $root_admin_id){
            $is_admin = 1;
        }
        //非超管时，获取角色
        if(!$is_admin){
            $root_role_id = Config::get('system.root_role_id');
            $roleList= (new AdminRoleService())->getRoleByAdmin($user['id'],true);
            if($roleList){
                foreach ($roleList as $role){
                    if(!$role['status']) continue;
                    if($role['id'] == $root_role_id){
                        $is_admin = 1;
                        break;
                    }else{
                        $dataScope += $role['data_scope'];
                        $roleId[] = $role['id'];
                    }
                }
            }
        }

        //非超管获取菜单列表
        if(!$is_admin){
            $menuList = (new RoleMenuService())->getRoleMenuList($roleId);
        }

        //非超管且无角色 无法登录
        if(!$is_admin && !$roleId){
            $this->message = '无角色分组，登录失败';
            return false;
        }

        $sessionData = [
            'info' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['realname'],
                'is_admin' => $is_admin
            ],
            'dataScope' => $is_admin?0:$dataScope,
            //预留多组织方式
            'struct' => $struct?[['id'=>$struct['id'],'name'=>$struct['name']]]:[],
            'role' => $is_admin?[]:$roleId,
            'menu' => $is_admin?[]:$menuList,
        ];
        Session::set('adminLoginInfo', $sessionData);

        if ($this->cookie) {
            Cookie::set('adminLoginCookie', $user['id'], 24 * 3600 * 2);
        }

        return true;
    }

    /**
     * 退出登录
     */
    public function logout(): void
    {
        Session::clear();
        Cookie::delete('adminLoginCookie');
    }

    /**
     * 验证密码
     * @param $password
     * @return bool
     */
    public function validatePassword($password):bool{
        if(!$this->_user){
            $this->message = '用户信息获取失败';
            return false;
        }
        if($this->_user['password']!=md5($password)){
            $this->message = '密码错误';
            return false;
        }
        return true;
    }
    /**
     * 获取用户信息
     * @return mixed
     */
    public function getUser($field = '', $value = '')
    {
        if ($this->_user === null) {
            if ($field && $value) {
                $this->_user = Db::table(Admin::tableName())->where($field, $value)->find();
            }
        }
        return $this->_user;
    }

    /**
     * 验证登录信息
     * @param $data
     * @return bool
     */
    public function validate($data): bool
    {
        $rules = [
            'username|用户名' => 'require|min:2|max:20',
            'password|密码' => 'require|min:6|max:20',
            'captcha|验证码' => 'require|length:4',
        ];

        //验证数据格式
        $validate = Validate::rule($rules);
        if (!$validate->check($data)) {
            $this->message = $validate->getError() ?: '表单数据错误';
            return false;
        }
        //验证验证码
        if (!Captcha::check($data['captcha'])) {
            $this->message = '验证码错误';
            return false;
        }
        if (isset($data['remember']) && $data['remember']) {
            $this->cookie = true;
        }
        return true;
    }
}