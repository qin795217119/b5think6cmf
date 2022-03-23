<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础开发管理平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\extend\helpers\LoginAuthHelper;
use app\admin\extend\services\AdminLoginService;
use app\common\helpers\Result;
use app\common\helpers\UploadHelper;
use app\common\model\Admin;
use think\facade\Validate;
use think\response\Json;


class CommonController extends BaseController
{
    /**
     * 图片上传
     * @return \think\response\Json
     */
    public function uploadimgAction():Json
    {
        if($this->request->isPost()){
            $upload = new UploadHelper();
            $upload->type = 'img';
            $upload->cat = $this->request->request('cat', 'images');
            $upload->width = intval($this->request->request('width', 0));
            $upload->height = intval($this->request->request('height', 0));
            return $upload->run();
        }else{
            return Result::error('请求类型错误');
        }

    }

    /**
     * 视频上传
     * @return \think\response\Json
     */
    public function uploadvideoAction():Json
    {
        if($this->request->isPost()) {
            $upload = new UploadHelper();
            $upload->type = 'video';
            $upload->cat = $this->request->request('cat', 'video');
            return $upload->run();
        }else{
            return Result::error('请求类型错误');
        }
    }

    /**
     * 文件上传
     * @return \think\response\Json
     */
    public function uploadfileAction():Json
    {
        if($this->request->isPost()) {
            $upload = new UploadHelper();
            $upload->type = 'file';
            $upload->cat = $this->request->request('cat', 'file');
            return $upload->run();
        }else{
            return Result::error('请求类型错误');
        }
    }

    /**
     * 裁剪图片
     * @return string
     */
    public function cropperAction(){
        $data=[
            'id' => $this->request->request('id',''),
            'cat' => $this->request->request('cat',''),
        ];
        return $this->render('',$data);
    }

    //修改密码
    public function repassAction(){
        if($this->request->isPost()){
            $rules = [
                'oldpass|旧密码' => 'require',
                'newpass|新密码' => 'require|min:6|max:20',
                'confirmpass|确认密码' => 'require|min:6|max:20|confirm:newpass',
            ];
            $data = $this->request->post();
            //验证数据格式
            $validate = Validate::rule($rules);
            if (!$validate->check($data)) {
                return Result::error($validate->getError() ?: '表单数据错误');
            }
            if($data['oldpass'] == $data['newpass']){
                return Result::error('新旧密码不能一样');
            }
            $service = new AdminLoginService();
            $service->getUser('id',LoginAuthHelper::adminLoginInfo('info.id'));
            if(!$service->validatePassword($data['oldpass'])){
                return  Result::error($service->message);
            }
            Admin::bUpdate(['id'=>$service->_user['id'],'password'=>md5($data['newpass'])]);
            return Result::success('密码修改成功');
        }else{
            return $this->render();
        }
    }

    public function lockscreenAction(){
        if($this->request->isPost()){
            $password = $this->request->post('password');
            if(!$password) return Result::error('请输入密码');
            $service = new AdminLoginService();
            $adminId = LoginAuthHelper::adminLoginInfo('info.id');
            if(!$adminId){
                return Result::error('登录失效，请重新登录');
            }
            $service->getUser('id',$adminId);
            if(!$service->validatePassword($password)){
                return Result::error($service->message);
            }
            if($service->_user['status']!=1){
                return Result::error('用户状态错误，请重新登录');
            }
            session('islock',null);
            return Result::success('登录成功');
        }else{
            session('islock',1);
            $adminInfo = LoginAuthHelper::adminLoginInfo('info');
            return $this->render('',['user'=>$adminInfo]);
        }
    }
}
