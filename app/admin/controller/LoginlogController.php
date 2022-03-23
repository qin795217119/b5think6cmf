<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础开发管理平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\extend\helpers\LoginAuthHelper;
use app\admin\extend\helpers\TraitActionHelper;
use app\common\helpers\Result;
use app\common\model\LoginLog;
use think\facade\Db;


class LoginlogController extends BaseController
{
    use TraitActionHelper;
    protected $model = LoginLog::class;

    /**
     * 清空数据
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function trashAction(){
        if($this->request->isPost()){
            Db::table($this->model::tableName())->delete(true);
            return Result::success('数据情况完毕');
        }else{
            return Result::error('请求方式错误');
        }
    }
}
