<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\extend\helpers\LoginAuthHelper;
use app\common\model\Menu;
use think\facade\Db;

class IndexController extends BaseController
{
    public function indexAction()
    {
        $userInfo = LoginAuthHelper::adminLoginInfo();
        $menuHtml = $this->getMenuListByLogin();
        return $this->render('', ['user_info' => $userInfo,'menuHtml'=>$menuHtml]);
    }

    public function homeAction()
    {
        return $this->render();
    }

    public function downloadAction(){
        $fileName = $this->request->get('fileName','');
        if(!$fileName) return $this->toError('参数错误');

        header('location:'.$fileName);
    }

    /**
     * 根据登录session获取菜单
     * @return string
     * @throws \Exception
     */
    protected function getMenuListByLogin(): string
    {
        $menuHtml = '';
        $menuList = [];
        $adminId = LoginAuthHelper::adminLoginInfo('info.id');
        if ($adminId) {
            $isAdmin = LoginAuthHelper::adminLoginInfo('info.is_admin');

            if ($isAdmin) {
                $menuList = Db::table(Menu::tableName())->field(['id', 'type', 'name', 'url', 'parent_id', 'icon', 'is_refresh', 'target'])->where('type', '<>', 'F')->where('status', 1)->order(['parent_id' => 'asc', 'listsort' => 'asc', 'id' => 'asc'])->select()->toArray();
            } else {
                $menuIdList = LoginAuthHelper::adminLoginInfo('menu');
                if ($menuIdList) {
                    //获取菜单
                    $menuList = Db::table(Menu::tableName())->field(['id', 'type', 'name', 'url', 'parent_id', 'icon', 'is_refresh', 'target'])->whereIn('id', $menuIdList)->where('type', '<>', 'F')->where('status', 1)->order(['parent_id' => 'asc', 'listsort' => 'asc', 'id' => 'asc'])->select()->toArray();
                }
            }
        }

        if ($menuList) {
            $menuTree = $this->getMenuTree($menuList);
            if ($menuTree) {
                $menuHtml = $this->menuToHtml($menuTree);
            }
        }
        return $menuHtml;
    }


    /**
     * 将菜单转为数形无限极
     * @param $list
     * @param int $pid
     * @param int $deep
     * @return array
     */
    protected function getMenuTree($list, $pid = 0, $deep = 0): array
    {
        $tree = [];
        foreach ($list as $key => $row) {
            if ($row['parent_id'] == $pid) {
                $row['deep'] = $deep;
                unset($list[$key]);
                $row['child'] = $this->getMenuTree($list, $row['id'], $deep + 1);
                $tree[] = $row;
            }
        }
        return $tree;
    }

    /**
     * 将菜单树形转为html
     * @param $menus
     * @param int $deep
     * @return string
     */
    protected function menuToHtml($menus, $deep = 0): string
    {
        $html = '';
        if (is_array($menus)) {
            foreach ($menus as $t) {
                if ($t['deep'] == $deep) {
                    if ($t['type'] == 'C') {
                        $url = $t['url'];
                        if ($url && strpos($url, 'http') !== 0) {
                            $url = url($url)->build();
                        }
                        if ($t['parent_id'] == 0) {
                            $html .= '<li><a class="' . ($t['target'] == '1' ? 'menuBlank' : 'menuItem') . '" href="' . $url . '" data-refresh="' . ($t['is_refresh'] ? 'true' : 'false') . '">' . ($t['icon'] ? '<i class="' . $t['icon'] . '"></i>' : '') . ' <span class="nav-label">' . $t['name'] . '</span></a></li>';
                        } else {
                            $html .= '<li><a class="' . ($t['target'] == '1' ? 'menuBlank' : 'menuItem') . '" href="' . $url . '" data-refresh="' . ($t['is_refresh'] ? 'true' : 'false') . '">' . $t['name'] . '</a></li>';
                        }

                    } else {
                        //实现最多三级菜单
                        if ($t['child'] && $deep < 3) {
                            $html .= '<li><a href="javascript:;">' . ($t['icon'] ? '<i class="' . $t['icon'] . '"></i>' : '') . ' <span class="nav-label">' . $t['name'] . '</span><span class="fa arrow"></span></a>';
                            $html .= '<ul class="nav ' . ($deep == 0 ? 'nav-second-level' : 'nav-third-level') . '">';
                            $html .= $this->menuToHtml($t['child'], $deep+1);
                            $html = $html . "</ul></li>";
                        }
                    }
                }

            }
        }
        return $html;
    }
}
