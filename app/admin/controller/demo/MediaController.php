<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础开发管理平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin\controller\demo;

use app\admin\BaseController;
use app\admin\extend\helpers\TraitActionHelper;
use app\common\model\demo\Media;


class MediaController extends BaseController
{
    use TraitActionHelper;
    protected $model = Media::class;

}
