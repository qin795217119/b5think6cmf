<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\admin\middleware;

use app\admin\extend\helpers\LoginAuthHelper;
use app\admin\extend\services\AdminLoginService;
use app\common\helpers\Result;
use app\Request;
use Closure;
use think\facade\Cookie;
use think\facade\Route;

class AdminLogin
{
    public function handle(Request $request, Closure $next)
    {
        //不需要登录的控制器
        $noLogin = ['public'];
        if (in_array($request->controller(true), $noLogin)) {
            return $next($request);
        }

        //是否登录
        $loginInfo = LoginAuthHelper::adminLoginInfo();
        if ($loginInfo) {
            return $next($request);
        }
        //判断cookie
        if ($this->autoLoginByCookie()) {
            return $next($request);
        }
        //跳转登录
        if ($request->isPost() || $request->isAjax()) {
            return Result::error('请先登录', 300);
        } else {
            $loginUrl = Route::buildUrl('public/login')->build();
            return redirect($loginUrl);
        }
    }

    /**
     * 判断cookie登录
     * @return bool
     */
    public function autoLoginByCookie(): bool
    {
        $userId = Cookie::get('adminLoginCookie');
        if (!$userId) return false;

        $userinfo = (new AdminLoginService())->loginSession($userId);
        if (!$userinfo) {
            return false;
        }
        return true;
    }
}
