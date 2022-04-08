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
use app\admin\extend\services\AdminPosService;
use app\admin\validate\PositionValidate;
use app\common\cache\PositionCache;
use app\common\model\Position;


class PositionController extends BaseController
{
    use TraitActionHelper;
    protected $model = Position::class;
    protected $validate = PositionValidate::class;


    protected function saveAfter(array $data, string $type): void
    {
        PositionCache::clear();
    }

    protected function deleteAfter(array $data): void
    {
        PositionCache::clear();
        //删除管理员岗位
        (new AdminPosService())->deleteByPos($data['id']);
    }
}
