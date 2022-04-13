<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\admin\middleware;

use app\admin\extend\helpers\LoginAuthHelper;
use app\common\cache\ConfigCache;
use app\common\helpers\Result;
use app\Request;
use Closure;
use think\facade\Config;
use think\facade\Route;

class DemoMode
{
    public function handle(Request $request, Closure $next)
    {
        $controller = $request->controller(true);
        $action = $request->action(true);

        //不走演示判断的控制器、方法
        $notAuthController = ['public'];
        $notAuthAction = ['tree','lockscreen'];

        if (in_array($controller, $notAuthController) || in_array($action, $notAuthAction) || substr($action,0,4) === 'ajax') {
            return $next($request);
        }

        $noCheckAction = ['index'];
        if(($request->isPost() || $request->isJson()) && !in_array($action,$noCheckAction)){
            $model = ConfigCache::get('demo_mode');
            $is_admin = LoginAuthHelper::adminLoginInfo('info.is_admin');
            if($model == '1' && !$is_admin){
                return Result::error('演示模式，无法此操作', 500);
            }
        }
        return $next($request);
    }
}
