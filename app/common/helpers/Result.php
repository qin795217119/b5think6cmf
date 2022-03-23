<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\common\helpers;

use think\response\Json;

class Result
{
    /**
     * 成功的JSON
     * @param array $data
     * @param string $msg
     * @param array $extend
     * @return \think\response\Json
     */
    public static function success(string $msg = '', array $data = [], array $extend = []): Json
    {
        return static::message($msg, true, $data, 0, $extend);
    }

    /**
     * 失败JSON
     * @param string $msg
     * @param int $code
     * @return \think\response\Json
     */
    public static function error(string $msg = '', int $code = 500): Json
    {
        return static::message($msg, false, [], $code);
    }

    /**
     * @param string $msg
     * @param bool $success
     * @param array $data
     * @param int $code
     * @param array $extend
     * @return \think\response\Json
     */
    public static function message(string $msg, bool $success, array $data, int $code = -1, array $extend = []): Json
    {
        $rs = [
            'code' => $code < 0 ? ($success ? 0 : 500) : $code,
            'msg' => $msg ?: ($success ? '操作成功' : '操作失败'),
            'data' => $data,
            'success' => $success
        ];
        if ($extend) {
            foreach ($extend as $key => $value) {
                $rs[$key] = $value;
            }
        }
        return json($rs);
    }
}