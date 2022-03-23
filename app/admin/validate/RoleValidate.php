<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class RoleValidate extends Validate
{

    protected $rule = [
        'name' => 'require|min:2|max:30',
        'rolekey' => 'require|min:3|max:30|alphaDash|unique:b5net_role',
        'listsort' => 'integer',
        'status' => 'integer|in:1,0',
        'note'=>'max:400'
    ];

    protected $field=[
        'name' => '角色名称',
        'rolekey' => '角色标识',
        'listsort' => '显示顺序',
        'status' => '状态',
        'note' => '备注',
    ];
//
//    protected $scene = [
//        'add'  =>  ['name','rolekey','listsort'],
//        'edit'  =>  ['name','rolekey','listsort']
//    ];
}
