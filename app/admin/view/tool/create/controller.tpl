<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础开发管理平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin\controller{$dir};

use app\admin\BaseController;
use app\admin\extend\helpers\TraitActionHelper;
use app\common\model{$dir}\{$model};


class {$controller}Controller extends BaseController
{
    use TraitActionHelper;
    protected $model = {$model}::class;


}
