<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class PositionValidate extends Validate
{
    protected $rule = [
        'name|岗位名称'=>'require|min:2|max:30',
        'poskey|岗位标识' => 'require|min:2|max:30',
        'listsort|显示顺序'=>'require|integer',
        'status|菜单状态'=>'require|integer|in:0,1',
        'note|备注'=>'max:255',
    ];

}
