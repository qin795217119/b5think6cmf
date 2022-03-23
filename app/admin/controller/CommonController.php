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
            $adminInfo = Admin::bFind(LoginAuthHelper::adminLoginInfo('info.id'));
            if(!$adminInfo) return Result::error('登录信息不存在');
            if(md5($data['oldpass'])!=$adminInfo['password']){
                return Result::error('旧密码不正确');
            }
            Admin::bUpdate(['id'=>$adminInfo['id'],'password'=>md5($data['newpass'])]);
            return Result::success('密码修改成功');
        }else{
            return $this->render();
        }

    }
}
