<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\taglib;

use think\template\TagLib;

class Upload extends TagLib
{
    protected $tags = [
        'img' => ['attr' => 'name,title,link,tips,multi,width,height,cat,crop,data', 'close' => 0],
        'video'=>['attr'=>'name,title,tips,cat,place,data', 'close' => 0],
        'file'=>['attr'=>'name,title,tips,cat,exts,link,multi,inputname,data', 'close' => 0],
    ];

    public function tagImg(array $tag): string
    {
        $name = $tag['name']??'';
        $title = $tag['title']??'上传图片';
        $link = $tag['link']??'';
        $tips = $tag['tips']??'';
        $multi = intval($tag['multi']??'1');
        $width = $tag['width']??'';
        $height = $tag['height']??'';
        $cat = $tag['cat']??'';
        $cropper = $tag['crop']??'';
        $data = $tag['data']??'';

        if(empty($name)) return 'name属性不能为空';

        $html = '<div class="b5uploadmainbox b5uploadimgbox" data-type="img">
                    <button type="button" class="btn-b5upload btn btn-primary btn-sm" id="'.$name.'" data-multi="'.$multi.'" data-height="'.$height.'" data-width="'.$width.'" data-cat="'.$cat.'"><i class="fa fa-image"></i>'.$title.'</button>';
        if($link){
            $html .= ' 或 <div class="uploadimg_link">
                    <input type="text" class="form-control" id="'.$name.'_link"><a href="javascript:;" class="btn btn-primary btn-sm" id="'.$name.'_linkbtn"><i class="fa fa-link"></i>添加</a>
                </div>';
        }
        if($tips){
            $html .= '<span class="help-block m-b-none"><i class="fa fa-info-circle"></i> '.$tips.'</span>';
        }
        $html .='<div class="b5uploadlistbox '.$name.'_imglist" id="'.$name.'_imglist"></div>';
        $html .='</div>';

        $html.='<script>$(function () {';
        if($data){
            $html.='<?php $__LIST__ = explode(",",'.$data.');?>';
            $html.='{foreach $__LIST__ as $value}b5uploadhtmlshow("'.$name.'",b5uploadimghtml("{$value}","'.$name.'"));{/foreach}';
        }
        if($link){
            $html.='b5uploadImgLink("'.$name.'");';
        }
        if($cropper){
            $url = url('common/cropper')->build();
            $html.='$("#'.$name.'").click(function () { var url = "'.$url.'";var params = "id='.$name.'&cat='.$cat.'";url = urlcreate(url,params);$.modal.open("上传裁剪图片",url); });';
        }else{
            $html.=' b5uploadimginit("'.$name.'");';
        }
        if($multi>1){
            $html.=' dragula(['.$name.'_imglist]);';
        }
        $html.='});</script>';

        return $html;
    }

    public function tagVideo(array $tag):string{
        $name = $tag['name']??'';
        $title = $tag['title']??'本地上传';
        $place = $tag['place']??'';
        $tips = $tag['tips']??'';
        $cat = $tag['cat']??'';
        $data = $tag['data']??'';
        if(empty($name)) return 'name属性不能为空';

        $html = '<div style="display:flex;align-items: center;justify-content: flex-start">
            <button type="button" class="btn btn-primary btn-sm" id="videobtn_'.$name.'" style="flex-shrink: 0"><i class="fa fa-video-camera"></i>'.$title.'</button>
            &nbsp;&nbsp;
            <input type="text" id="videourl_'.$name.'" class="form-control videourl_input" name="'.$name.'" placeholder="'.$place.'"  value="{' . $data . '}">
            <a style="flex-shrink: 0" href="javascript:;" id="videoshow_'.$name.'">&nbsp;查看</a>
        </div>';
        if($tips){
            $html.=' <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> '.$tips.'</span>';
        }
        $url = url('common/uploadvideo')->build();
        $html.='<script>
    $(function () {
        layui.use("upload", function(){
            var upload = layui.upload;
            //执行实例
            upload.render({
                elem: "#videobtn_'.$name.'"
                ,url: "'.$url.'"
                ,field:"file"
                ,multiple:false
                ,number:1
                ,data:{cat:"'.$cat.'"}
                ,accept:"video"
                ,acceptMime:"video/mp4"
                ,done: function(res){
                    if(res.success && res.code===0){
                        $("#videourl_'.$name.'").val(res.data.path)
                    }else{
                        $.modal.msgError(res.msg)
                    }
                }
                ,error: function(){
                    $.modal.msgWarning("网络连接错误")
                }
            });
        });
        $("#videoshow_'.$name.'").click(function () {
            var url = $("#videourl_'.$name.'").val()
            if(url){
                window.open(url)
            }
        });
    });
</script>';
        return $html;
    }

    public function tagFile(array $tag):string
    {
        $name = $tag['name'] ?? '';
        $title = $tag['title'] ?? '上传文件';
        $tips = $tag['tips'] ?? '';
        $cat = $tag['cat'] ?? '';
        $exts = $tag['exts'] ?? '';
        $link = $tag['link'] ?? '';
        $multi = intval($tag['multi']??'1');
        $inputname = $tag['inputname']??'';
        $data = $tag['data']??'';
        if(empty($name)) return 'name属性不能为空';

        $html = '<div class="b5uploadmainbox b5uploadfilebox" data-type="file">
                   <button type="button" class="btn-b5upload btn btn-primary btn-sm" id="'.$name.'" data-exts="'.$exts.'"  data-multi="'.$multi.'"  data-cat="'.$cat.'" data-inputname="'.$inputname.'"><i class="fa fa-upload"></i> '.$title.'</button>';
        if($link){
            $html .= ' 或 <div class="uploadimg_link">
                    <input type="text" id="'.$name.'_link" class="form-control" value=""><a href="javascript:;" class="btn btn-primary btn-sm" id="'.$name.'_linkbtn"><i class="fa fa-link"></i>添加</a>
                </div>';
        }
        if($tips){
            $html.=' <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> '.$tips.'</span>';
        }
        $html .='<div class="b5uploadlistbox '.$name.'_filelist" id="'.$name.'_filelist"></div>';
        $html .='</div>';
        $html.='<script>$(function () {b5uploadfileinit("'.$name.'");';
        if($data){
            $html.='<?php $__LIST__ = explode(",",'.$data.');?>';
            $html.='{foreach $__LIST__ as $value}b5uploadhtmlshow("'.$name.'",b5uploadfilehtml("{$value}","'.$name.'"));{/foreach}';
        }
        if($link){
            $html.='b5uploadImgLink("'.$name.'");';
        }
        if($multi>1){
            $html.=' dragula(['.$name.'_filelist]);';
        }
        $html.='});</script>';
        return $html;
    }

}