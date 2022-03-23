<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\common\helpers;


use app\common\cache\ConfigCache;
use think\facade\Config;
use think\facade\Filesystem;
use think\Image;

class UploadHelper
{
    public $type = 'img'; //文件类型 img,file,video
    public $fileName = 'file';//上传文件名称
    public $cat = 'images';//路径前缀
    public $maxSize = 0; //文件最大 kb
    public $ext = []; //文件后缀
    public $savePath = '';//保存路径规格 Y代表为/年 M为/年/月  YM为/年月

    //缩略图设置，其中一个大于0则开启
    public $width = 0; //缩略图宽度
    public $height = 0;//缩略图高度

    public $water = false;//水印设置


    public function run()
    {
        $method = $this->type . 'Upload';
        if (method_exists($this, $method)) {
            if (!$this->fileName) $this->fileName = 'file';
            return $this->$method();
        } else {
            return Result::error('方法错误');
        }
    }

    /**
     * 图片上传
     * @return \think\response\Json
     * @throws \Throwable
     */
    private function imgUpload()
    {
        if (!$this->cat) $this->cat = 'images';
        if (!$this->ext) $this->ext = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];
        if ($this->maxSize < 1) $this->maxSize = 10 * 1024;//10M
        return $this->_upload();
    }

    /**
     * 视频上传
     * @return \think\response\Json
     * @throws \Throwable
     */
    private function videoUpload()
    {
        if (!$this->cat) $this->cat = 'video';
        if (!$this->ext) $this->ext = ['mp4', 'm3u8', 'ogv', 'webm'];
        if ($this->maxSize < 1) $this->maxSize = 100 * 1024;//100M
        return $this->_upload();
    }


    /**
     * 文件上传
     * @return \think\response\Json
     * @throws \Throwable
     */
    private function fileUpload()
    {
        if (!$this->cat) $this->cat = 'file';
        if ($this->maxSize < 1) $this->maxSize = 100 * 1024;//100M
        return $this->_upload();
    }

    /**
     * 上传操作
     * @return \think\response\Json
     * @throws \Throwable
     */
    private function _upload()
    {
        $fileObj = request()->file($this->fileName);
        if (!$fileObj || !is_object($fileObj)) {
            return Result::error('上传文件不能为空');
        }
        //验证大小和格式
        $rule[$this->fileName]= [];
        $message[$this->fileName]= [];
        if($this->ext){
            $rule[$this->fileName]['fileExt'] = $this->ext;
            $message[$this->fileName]['fileExt'] = '格式只能是:' . implode(',', $this->ext);
        }
        $rule[$this->fileName]['fileSize'] = $this->maxSize * 1024;
        $message[$this->fileName]['fileSize'] = '大小不能超过' . Transform::sizeFormat($this->maxSize * 1024);

        validate($rule, $message)->check([$this->fileName => $fileObj]);

        //根路径
        $root = Config::get('filesystem.disks.public.url') . '/';
        //保存路径
        $savePath = $this->getSavePath();
        //定义保存名称
        $saveName = $this->getSaveName($fileObj);

        //如果是图片 并且 生成缩略图或添加水印
        $water_text = trim(ConfigCache::get('img_water_text_color', ''));
        if ($this->type == 'img' && ($this->width > 0 || $this->height > 0) || ($this->water && $water_text)) {
            $image = Image::open($fileObj);
            if (!$image) {
                return Result::error('图片打开错误');
            }
            $width = $image->width(); // 返回图片的宽度
            $height = $image->height(); // 返回图片的高度

            //保存后返回的地址
            $fullDir = str_replace('//','/','/' . $root . '/' .$savePath);
            $fullPath = $fullDir. '/' . $saveName;

            $imageObj = $image;
            //如果需要生成缩略图
            if ($this->width > 0 || $this->height > 0) {
                $canDrop = false;
                $thumbType = 1;//等比例缩放
                if ($this->width > 0 && $this->height > 0) {
                    if ($width > $this->width || $height > $this->height) {
                        $canDrop = true;
                        $thumbType = 3;//居中裁剪
                    }
                } elseif ($this->width > 0) {
                    if($width>$this->width){
                        $canDrop = true;
                        $scale = $this->width / $width;
                        $this->height = intval($scale * $height);
                    }
                } else {
                    if($height>$this->height){
                        $canDrop = true;
                        $scale = $this->height / $height;
                        $this->width = intval($scale * $width);
                    }
                }
                if($canDrop){
                    $imageObj = $imageObj->thumb($this->width, $this->height, $thumbType);
                }
            }
            //需要水印
            if ($this->water && $water_text) {
                $position = intval(ConfigCache::get('img_water_text_position', 0));
                $position = ($position > 0 && $position < 10) ? $position : 9;
                $fontSize = intval(ConfigCache::get('img_water_text_font', 0)) ?: 18;
                $fontColor = trim(ConfigCache::get('img_water_text_color', '')) ?: '000000';

                $imageObj = $imageObj->text($water_text, app()->getRootPath() . 'public/static/common/fonts/HYZhongSongJ.ttf', $fontSize, '#' . $fontColor, $position,10);
            }
            //创建目录
            if (!is_dir('.'.$fullDir)) {
                if (false === @mkdir('.'.$fullDir, 0777, true) && !is_dir('.'.$fullDir)) {
                    return Result::error('存储目录创建失败');
                }
            }
            //保存
            $imageObj->save('.' . $fullPath);
        } else {
            $uploadResult = Filesystem::disk('public')->putFileAs($savePath, $fileObj, $saveName);
            if (!$uploadResult) {
                return Result::error('上传失败');
            }
            $fullPath = str_replace('//', '/', $root . $uploadResult);
        }
        $data = [
            'path' => $fullPath,
            'url' => Functions::getFileUrl($fullPath),
            'originName' => $fileObj->getOriginalName(),
            'ext' =>$fileObj->getExtension()
        ];


        return Result::success('上传成功', $data);
    }

    /**
     * 获取保存路径
     * @return string
     */
    public function getSavePath(): string
    {
        if (!$this->savePath || $this->savePath == 'D') {
            $savePath = date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
        } elseif ($this->savePath == 'Y') {
            $savePath = date('Y');
        } elseif ($this->savePath == 'M') {
            $savePath = date('Y') . DIRECTORY_SEPARATOR . date('m');
        } elseif ($this->savePath == 'YM') {
            $savePath = date('Ym');
        } else {
            $savePath = $this->savePath;
        }
        $savePath = ($this->cat ? ($this->cat . DIRECTORY_SEPARATOR) : '') . $savePath;
        $savePath = str_replace(DIRECTORY_SEPARATOR, '/', $savePath);
        return $savePath;
    }

    /**
     * 获取保存名称
     * @param $fileObj
     * @return string
     */
    public function getSaveName($fileObj): string
    {
        return md5(microtime(true) . $fileObj->getPathname()) . '.' . $fileObj->extension();
    }


}