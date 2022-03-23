<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use app\admin\extend\services\MenuService;
use app\common\model\Menu;
use think\Validate;

class MenuValidate extends Validate
{
    protected $rule = [
        'parent_id|上级菜单'=>'require|integer',
        'name|菜单名称' => 'require|min:2|max:50',
        'type|菜单类型' => 'require|checkType',
        'perms|菜单标识' => 'requireCallback:checkPermsRequire|checkPerms',
        'url|请求地址' => 'requireCallback:checkUrlRequire',
        'target|打开方式' => 'require|integer|in:0,1',
        'listsort|显示顺序'=>'require|integer',
        'status|菜单状态'=>'require|integer|in:0,1',
        'is_refresh|是否刷新'=>'require|integer|in:0,1',
        'note|备注'=>'max:255',
    ];


    //requireCallback验证某个字段是否必填
    protected function checkPermsRequire($value,$data){
        if($data['type']!='M') return true;
    }

    protected function checkUrlRequire($value,$data){
        if($data['type'] == 'C') return true;
    }


    //自定义方式验证
    protected function checkType($value){
        $typeList = (new MenuService())->typeList();
        return isset($typeList[$value])?true:'菜单类型错误';
    }

    protected function checkPerms($value,$rule,$data){
        if($data['type']!='M'){
            if(!$value)  return '菜单标识不能为空';
            $info = Menu::bFind('',[['perms','=',$value]]);
            $id = $data['id']??'';
            if($info && (($id && $id!=$info['id']) || !$id)){
                return '菜单标识已存在';
            }
        }
        return true;
    }
}
