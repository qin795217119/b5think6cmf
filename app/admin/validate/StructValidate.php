<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class StructValidate extends Validate
{
    protected $rule = [
        'parent_id|上级部门'=>'require|integer',
        'name|部门名称' => 'require|min:2|max:50',
        'listsort|显示顺序'=>'require|integer',
        'leader|负责人' => 'max:20',
        'phone|联系电话' => 'max:20',
        'status|菜单状态'=>'require|integer|in:0,1',
        'note|备注'=>'max:255',
    ];

}
