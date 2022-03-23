<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\admin\middleware;

use app\admin\extend\helpers\LoginAuthHelper;
use app\common\helpers\Result;
use app\Request;
use Closure;
use think\facade\Route;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $controller = $request->controller(true);
        $action = $request->action(true);

        //锁屏判断
        $islock=session('islock');
        $lockPerms = ['common:lockscreen','public:logout'];
        if($islock && !in_array($controller.':'.$action,$lockPerms)){
            if($request->isJson() || $request->isAjax()){
                return Result::error('锁屏中，无法此操作', 500);
            }else{
                $errorUrl = Route::buildUrl('common/lockscreen')->build();
                return redirect($errorUrl);
            }
        }

        //权限判断
        $hasPower = LoginAuthHelper::checkPower($controller,$action);
        if(!$hasPower){
            if($request->isJson() || $request->isAjax()){
                return Result::error('无权访问', 500);
            }else{
                $errorUrl = Route::buildUrl('public/error',['msg'=>'无权访问',500])->build();
                return redirect($errorUrl);
            }
        }
        return $next($request);
    }
}
