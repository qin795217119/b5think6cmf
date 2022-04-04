<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础开发管理平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\common\helpers\Result;
use app\common\model\Menu;
use think\facade\Config;
use think\facade\Db;
use think\helper\Str;


class ToolController extends BaseController
{
    /**
     * 表单构建
     * @return string
     */
    public function buildAction(){

        return $this->render();
    }

    /**
     * 代码生成
     */
    public function createAction(){
        if($this->request->isPost()){
            $params = $this->request->post();
            $table = $params['table']??'';
            $class = $params['class']??'';
            $dir = $params['dir']??'';
            if(empty($table)) return Result::error('请选择表名');
            if(empty($class)) return Result::error('请输入类名称');

            $table_exists = Db::query("show tables like '".$table."'");
            if(!$table_exists) return Result::error('表'.$table.'不存在');

            return $this->genCode($table,$class,$dir);

        }else{
            $systemList = ['b5net_admin','b5net_admin_role','b5net_admin_struct','b5net_config','b5net_loginlog','b5net_menu','b5net_notice','b5net_role','b5net_role_menu','b5net_role_struct','b5net_struct','b5net_wechat_users','b5net_wechat_access','b5net_smscode','demo_media'];
            $tables = Db::query("show tables");
            $tableList = [];
            foreach ($tables as $value){
                $table = current($value);
                if(!in_array($table,$systemList)){
                    $tableList[]=$table;
                }
            }


            return $this->render('',['tableList'=>$tableList]);
        }

    }

    private function genCode($table,$class,$dir){
        $class_name = Str::studly($class);//生成类的名称大驼峰
        $path_name = strtolower($class_name);//生成文件夹名称 小写
        $root = $this->app->getBasePath();//app目录地址

        $class_path_name = $class_name;
        $path_path_name = $path_name;

        if($dir) {
            $class_path_name = $dir.'/'.$class_name;
            $path_path_name = $dir.'/'.$path_name;

            $model_path = str_replace('/',DIRECTORY_SEPARATOR,$root."common/model/".$dir);
            $controller_path = str_replace('/',DIRECTORY_SEPARATOR,$root."admin/controller/".$dir);
            if(true !== $res = $this->mkdir($model_path)){
                return $res;
            }
            if(true !== $res = $this->mkdir($controller_path)){
                return $res;
            }
            $dir='\\'.$dir;
        }


        //生成model
        $temp_model_path =  str_replace('/',DIRECTORY_SEPARATOR,$root.'admin/view/tool/create/model.tpl');//模型的示例代码
        $gen_model_path =  str_replace('/',DIRECTORY_SEPARATOR,$root."common/model/".$class_path_name.".php");//生成的模型地址
        $tem_model_f = fopen($temp_model_path,"r");
        $temp_model_str = fread($tem_model_f,filesize($temp_model_path));
        $temp_model_str = str_replace(['{$model}','{$table}','{$dir}'],[$class_name,$table,$dir],$temp_model_str);
        $gen_model=fopen($gen_model_path,'w');
        fwrite($gen_model,$temp_model_str);

        //生成controller
        $temp_controller_path =  str_replace('/',DIRECTORY_SEPARATOR,$root.'admin/view/tool/create/controller.tpl');//控制器的示例代码
        $gen_controller_path =  str_replace('/',DIRECTORY_SEPARATOR,$root."admin/controller/".$class_path_name."Controller.php");//生成的控制器地址
        $tem_controller_f = fopen($temp_controller_path,"r");
        $temp_controller_str = fread($tem_controller_f,filesize($temp_controller_path));
        $temp_controller_str = str_replace(['{$model}','{$controller}','{$dir}'],[$class_name,$class_name,$dir],$temp_controller_str);
        $gen_controller=fopen($gen_controller_path,'w');
        fwrite($gen_controller,$temp_controller_str);

        //获取表的字段
        $dbname = env('database.database');
        $result = Db::query("select COLUMN_NAME,COLUMN_COMMENT,DATA_TYPE from INFORMATION_SCHEMA.Columns where table_name='".$table."' and table_schema='".$dbname."'");

        //创建视图文件夹
        $view_path = str_replace('/',DIRECTORY_SEPARATOR,$root.'admin/view/'.$path_path_name.'/');
        if(true !== $res = $this->mkdir($view_path)){
            return $res;
        }

        //生成index.html
        $temp_index_path = str_replace('/',DIRECTORY_SEPARATOR,$root.'admin/view/tool/create/index.tpl');//index.html的示例代码
        $gen_index_path = $view_path."index.html";//生成的index.html地址
        $tem_index_f = fopen($temp_index_path,"r");
        $temp_index_str = fread($tem_index_f,filesize($temp_index_path));
        $html='';
        foreach ($result as $value){
            if($value['COLUMN_NAME'] =='id' || $value['COLUMN_NAME'] =='create_time' || $value['COLUMN_NAME'] =='update_time'){
                continue;
            }
            $html.="                    {field: '".$value['COLUMN_NAME']."', title: '".($value['COLUMN_COMMENT']?:$value['COLUMN_NAME'])."', align: 'center'},\r\n";
        }
        $temp_index_str = str_replace('___REPLACE___',$html,$temp_index_str);
        $gen_index=fopen($gen_index_path,'w');
        fwrite($gen_index,$temp_index_str);

        //生成add.html
        $temp_add_path = str_replace('/',DIRECTORY_SEPARATOR,$root.'admin/view/tool/create/add.tpl');//add.html的示例代码
        $gen_add_path = $view_path."add.html";//生成的add.html地址
        $tem_add_f = fopen($temp_add_path,"r");
        $temp_add_str = fread($tem_add_f,filesize($temp_add_path));
        $html='';
        foreach ($result as $value){
            if($value['COLUMN_NAME'] =='id' || $value['COLUMN_NAME'] =='create_time' || $value['COLUMN_NAME'] =='update_time'){
                continue;
            }
            $html.='    <div class="form-group">
        <label class="col-sm-3 control-label is-required">'.($value['COLUMN_COMMENT']?:$value['COLUMN_NAME']).'：</label>
        <div class="col-sm-8">
            <input type="text" name="'.$value['COLUMN_NAME'].'" value="" class="form-control" required autocomplete="off"/>
        </div>
    </div>'."\r\n";
        }
        $temp_add_str = str_replace('___REPLACE___',$html,$temp_add_str);
        $gen_add=fopen($gen_add_path,'w');
        fwrite($gen_add,$temp_add_str);

        //生成edit.html
        $temp_edit_path = str_replace('/',DIRECTORY_SEPARATOR,$root.'admin/view/tool/create/edit.tpl');//edit.html的示例代码
        $gen_edit_path = $view_path."edit.html";//生成的edit.html地址
        $tem_edit_f = fopen($temp_edit_path,"r");
        $temp_edit_str = fread($tem_edit_f,filesize($temp_edit_path));
        $html='';
        foreach ($result as $value){
            if($value['COLUMN_NAME'] =='id' || $value['COLUMN_NAME'] =='create_time' || $value['COLUMN_NAME'] =='update_time'){
                continue;
            }
            $html.='    <div class="form-group">
        <label class="col-sm-3 control-label is-required">'.($value['COLUMN_COMMENT']?:$value['COLUMN_NAME']).'：</label>
        <div class="col-sm-8">
            <input type="text" name="'.$value['COLUMN_NAME'].'" value="{$info.'.$value['COLUMN_NAME'].'}" class="form-control" required autocomplete="off"/>
        </div>
    </div>'."\r\n";
        }
        $temp_edit_str = str_replace('___REPLACE___',$html,$temp_edit_str);
        $gen_edit=fopen($gen_edit_path,'w');
        fwrite($gen_edit,$temp_edit_str);

        return Result::success('生成完成');
    }

    private function mkdir($path){
        if (!is_dir($path)) {
            if (false === @mkdir($path, 0777, true) && !is_dir($path)) {
                return Result::error('创建文件夹失败:'.$path);
            }
        }
        return true;
    }
}
