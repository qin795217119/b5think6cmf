<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\extend\services\AdminLoginService;
use app\common\helpers\Result;
use app\common\model\LoginLog;
use think\captcha\facade\Captcha;
use think\facade\Cache;
use think\facade\Request;

class PublicController extends BaseController
{
    /**
     * 登录页面及保存
     * @return string|\think\response\Json
     */
    public function loginAction(){
        if(Request::isPost()){
            $result = (new AdminLoginService())->login();
            $data = $result->getData();
            LoginLog::logAdd($this->request->post('username',''),$data['success']?1:0,$data['msg']);
            return $result;
        }else{
            return $this->render();
        }
    }

    /**
     * 退出登录
     * @return \think\response\Redirect
     */
    public function logoutAction(){
        (new AdminLoginService())->logout();
        return redirect(url('index/index')->build());
    }

    /**
     * 获取验证码
     * @return \think\Response
     */
    public function captchaAction(){
        return Captcha::create();
    }

    /**
     * 清空缓存
     * @return \think\response\Json
     */
    public function cacheclearAction(){
        Cache::clear();
        return Result::success();
    }

    /**
     * 错误页
     * @return string
     */
    public function errorAction(){
        $code = $this->request->get('code',400);
        $msg = $this->request->get('msg','发生错误');
        return $this->render('',['code'=>$code,'msg'=>$msg]);
    }
}
