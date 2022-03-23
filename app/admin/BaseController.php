<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin;

use app\admin\middleware\AdminAuth;
use app\admin\middleware\AdminLogin;

use app\admin\middleware\DemoMode;
use app\common\cache\ConfigCache;
use app\common\helpers\Result;
use think\App;
use think\facade\View;

class BaseController
{
    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [AdminLogin::class,AdminAuth::class,DemoMode::class];

    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;


    public function __construct(App $app){
        $this->app = $app;
        $this->request = $app->request;
        if($this->request->isGet() && !$this->request->isAjax()){
            View::assign('system_name',ConfigCache::get('sys_config_sysname'));
        }
    }


    /**
     * 跳转到错误页
     * @param string $msg
     * @param int $code
     * @return string|\think\response\Json
     */
    public function toError(string $msg='',int $code = 500){
        $msg = $msg?:'发生错误了';
        if($this->request->isPost()){
            return Result::error($msg,$code);
        }else{
            return $this->render('public/error',['msg'=>$msg,'code'=>$code]);
        }
    }
    /**
     * 页面渲染方法
     * @param string $template
     * @param array $vars
     * @return string
     */
    public function render(string $template = '' ,array $vars = []){
        $template = $template?:$this->request->action(true);
        return View::fetch($template,$vars);
    }
}