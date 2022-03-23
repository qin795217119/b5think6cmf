<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\admin\extend\taglib;

use think\template\TagLib;

class Asset extends TagLib
{
    protected $tags = [
        'css' => ['attr' => 'name', 'close' => 0],
        'js' => ['attr' => 'name', 'close' => 0],
    ];

    public function tagCss(array $tag): string
    {
        $name = $tag['name'] ?? '';
        if (!$name) return '';
        $nameList = explode(',', $name);
        $cssList = [];
        foreach ($nameList as $key) {
            $asset = $this->assetList($key);
            if (empty($asset)) continue;
            $list = $asset['css'] ?? [];
            $cssList = array_merge($cssList, $list);
        }
        $cssList = array_unique($cssList);
        if (empty($cssList)) return '';
        $html = '';
        foreach ($cssList as $css) {
            if (!$css) continue;
            $html .= ' <link href="__STATIC__/plugins/' . $css . '" rel="stylesheet"/>';
        }
        return $html;
    }

    public function tagJs(array $tag): string
    {
        $name = $tag['name'] ?? '';
        if (!$name) return '';
        $nameList = explode(',', $name);
        $jsList = [];
        foreach ($nameList as $key) {
            $asset = $this->assetList($key);
            if (empty($asset)) continue;
            $list = $asset['js'] ?? [];
            $jsList = array_merge($jsList, $list);
        }
        $jsList = array_unique($jsList);
        if (empty($jsList)) return '';
        $html = '';
        foreach ($jsList as $js) {
            if (!$js) continue;
            $html .= '<script src="__STATIC__/plugins/' . $js . '"></script>';
        }
        return $html;

    }

    /**
     * 资源插件列表
     * @param string $key
     * @return array|mixed
     */
    private function assetList($key = '')
    {
        $list = [
            'beautifyhtml' => [
                'css' => [],
                'js' => ['beautifyhtml/beautifyhtml.js']
            ],
            'export' => [
                'css' => [],
                'js' => ['bootstrap-table/extensions/export/tableExport.js', 'bootstrap-table/extensions/export/bootstrap-table-export.js']
            ],
            'dragula' => [
                'css' => ['dragula/dragula.min.css'],
                'js' => ['dragula/dragula.min.js']
            ],
            'select2' => [
                'css' => ['select2/select2.min.css', 'select2/select2-bootstrap.css'],
                'js' => ['select2/select2.min.js']
            ],
            'summernote' => [
                'css' => ['summernote/summernote.min.css'],
                'js' => ['summernote/summernote.min.js', 'summernote/lang/summernote-zh-CN.min.js']
            ],
            'treetable' => [
                'css' => [],
                'js' => ['bootstrap-treetable/bootstrap-treetable.js']
            ],
            'ztree' => [
                'css' => ['ztree/css/metroStyle/metroStyle.css'],
                'js' => ['ztree/js/jquery.ztree.all.min.js', 'ztree/js/jquery.ztree.exhide.min.js']
            ],
            'jquery-layout' => [
                'css' => ['jquery-layout/layout-default.css'],
                'js' => ['jquery-layout/jquery.layout.min.js']
            ],
            'mypicker' => [
                'css' => [],
                'js' => ['My97DatePicker/WdatePicker.js']
            ],
            'viewer' => [
                'css' => ['viewerjs/viewer.min.css'],
                'js' => ['viewerjs/viewer.min.js']
            ],
            'echarts' => [
                'css' => [],
                'js' => ['echarts.min.js']
            ],
            'cropper' => [
                'css' => ['cropper/cropper.min.css'],
                'js' => ['cropper/cropper.min.js', 'cropper/jquery-cropper.min.js']
            ]
        ];
        return $key ? ($list[$key] ?? []) : [];
    }
}