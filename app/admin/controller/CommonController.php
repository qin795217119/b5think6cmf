<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础开发管理平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\common\helpers\Result;
use app\common\helpers\UploadHelper;
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
}
