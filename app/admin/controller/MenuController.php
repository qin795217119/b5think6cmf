<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础开发管理平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\extend\helpers\TraitActionHelper;
use app\admin\extend\services\MenuService;
use app\admin\extend\services\RoleMenuService;
use app\admin\validate\MenuValidate;
use app\common\helpers\Result;
use app\common\model\Menu;


class MenuController extends BaseController
{
    use TraitActionHelper;
    protected $model = Menu::class;
    protected $validate = MenuValidate::class;

    /**
     * 获取菜单列表
     * @return string
     */
    public function treeAction(){
        $root = $this->request->get('root', 0);
        if($this->request->isPost()){
            $list = (new MenuService())->getList($root?true:false);
            return Result::success('',$list);
        }else{
            $id = $this->request->get('id', 0);
            return $this->render('',['menu_id'=>$id,'root'=>$root]);
        }
    }

    /**
     * 首页列表默认排序
     * @param array $params
     * @return array
     */
    protected function indexBefore(array $params): array
    {
        $params['orderBy'] = ['parent_id'=>'asc','listsort'=>'asc'];
        return $params;
    }

    /**
     * 添加渲染
     * @return string
     */
    protected function addRender(): string
    {
        return $this->render('',['typeList'=>(new MenuService())->typeList()]);
    }

    /**
     * 编辑渲染
     * @param $info
     * @return string
     */
    protected function editRender($info): string
    {
        if($info['parent_id']){
            $parent = Menu::bFind($info['parent_id']);
            if($parent){
                $info['parent_name'] = $parent['name'];
            }else{
                $info['parent_name'] = '错误：'.$info['parent_id'];
            }
        }else{
            $info['parent_name'] = '顶级菜单';
        }

        return $this->render('',['info'=>$info,'typeList'=>(new MenuService())->typeList()]);
    }
    /**
     * 删除后操作
     * @param array $data
     */
    protected function deleteAfter(array $data): void
    {
        //删除菜单的角色授权
        (new RoleMenuService())->deleteByMenu($data['id']);
    }
}
