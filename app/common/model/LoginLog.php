<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\common\model;

use app\common\BaseModel;
use app\common\helpers\Functions;
use app\common\helpers\IpLocation\IpLocation;
use app\common\helpers\UserAgent\Agent;

class LoginLog extends BaseModel
{
    protected $table = 'b5net_loginlog';


    //添加日志
    public static function logAdd($login_name, $status, $msg)
    {
        $agent = new Agent();
        $os = $agent->platform() . ' ' . $agent->version($agent->platform());
        $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
        $ip_addr = Functions::getClientIp();
        $login_location = '';
        $net = '';
        if ($ip_addr) {
            $ipLocation = new IpLocation();
            $location = $ipLocation->getlocation($ip_addr);
            if ($location) {
                if ($location['country']) {
                    $login_location = iconv('GBK', 'UTF-8', $location['country']);
                }
                if ($location['area']) {
                    $net = iconv('GBK', 'UTF-8', $location['area']);
                }
            }
        }
        $data= ['login_name' => $login_name, 'ipaddr' => $ip_addr, 'browser' => $browser, 'os' => $os, 'status' => $status, 'msg' => $msg, 'login_location' => $login_location,'net'=>$net];
        static::bInsert($data);
    }
}
