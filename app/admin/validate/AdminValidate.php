<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class AdminValidate extends Validate
{
    protected $rule = [
        'username' => 'require|min:2|max:30|alphaDash|unique:b5net_admin',
        'struct' => 'require',
        'roles' => 'require',
        'password' => 'min:6|max:30',
        'realname' => 'min:2|max:30',
        'status' => 'require|integer|in:0,1',
        'note' => 'max:255',
    ];

    protected $field = [
        'username' => '登陆账号',
        'struct' => '组织部门',
        'roles' => '角色分组',
        'password' => '登录密码',
        'realname' => '真实姓名',
        'status' => '状态',
        'note' => '备注'
    ];
}
