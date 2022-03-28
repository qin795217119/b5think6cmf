<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\extend\helpers;


use app\common\helpers\ExportHelper;
use app\common\helpers\Result;
use app\Request;
use think\db\Query;
use think\facade\Db;
use think\response\Json;

/**
 * 用于通用操作列表、添加、编辑、删除等通用封装
 * 若不符合需进行自己在controller中重写
 * Trait TraitActionHelper
 * @package app\admin\extend\helpers
 */
trait TraitActionHelper
{
    /**
     * 公共首页action
     * @param Request $request
     * @return string|\think\response\Json
     * @throws \Exception
     */
    public function indexAction(Request $request)
    {
        if ($request->isPost()) {
            $params = $request->post();
            if (method_exists($this, 'indexBefore')) {
                $params = $this->indexBefore($params);
            }
            //是否是树形tree，展示所有数据
            $isTree = $params['isTree'] ?? 0;

            //是否为导出excel，展示所有数据
            $isExport = $params['isExport'] ?? 0;

            $query = Db::table($this->model::tableName());
            $query = $this->indexWhere($query, $params);

            //操作查询对象，可以进行语句处理以及数据权限处理
            $extend = [];
            $queryResult = $this->indexQuery($query);
            if(is_array($queryResult)){
                $query = $queryResult['query'];
                $extend = $queryResult['extend']??[];
            }else{
                $query = $queryResult;
            }

            //是否分页
            if (!$isTree && !$isExport) {
                $pageSize = intval($params['pageSize'] ?? 10);
                $pageNum = intval($params['pageNum'] ?? 1);
                $pageNum = $pageNum < 1 ? 1 : $pageNum;
                $count = $query->count();
                $query = $query->page($pageNum, $pageSize);
            }
            $list = $query->select()->toArray();
            if ($isTree || $isExport) {
                $count = count($list);
            }
            //结果查询后的处理
            if (method_exists($this, 'indexAfter')) {
                $list = $this->indexAfter($list);
            }

            //导出操作
            if($isExport){
                //结果查询后的处理
                $export_data = $this->exportBefore($list);
				$excel_path = (new ExportHelper($export_data))->export();
				return Result::success($excel_path);
            }else{
                return Result::success('', $list, ['total' => $count,'extend'=>$extend]);
            }
        } else {
            return $this->indexRender($request);
        }
    }

    /**
     * 公共新增action
     * @param Request $request
     * @return string|\think\response\Json
     */
    public function addAction(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();

            //验证
            if ($this->validate ?? false) {
                //验证前数据处理
                $data = $this->validateBefore($data,'add');
                if(!is_array($data)){
                    return Result::error($data);
                }

                $res = validate($this->validate)->scene('add')->check($data);
                if ($res !== true) {
                    return Result::error($res);
                }
            }

            //数据处理
            $data = $this->saveBefore($data,'add');
            if(!is_array($data)){
                return Result::error($data);
            }
            $result = $this->model::bInsert($data,true);
            if (!$result) {
                return Result::error('保存失败');
            }

            $pk = $this->model::primaryKey();
            if ($pk) {
                $data[$pk] = $result;
            }
            $this->saveAfter($data, 'add');

            return Result::success('保存成功');
        } else {
            return $this->addRender($request);
        }
    }

    /**
     * 公共编辑action
     * @param Request $request
     * @return string|\think\response\Json
     */
    public function editAction(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            //验证
            if ($this->validate ?? false) {
                //验证前数据处理
                $data = $this->validateBefore($data,'edit');
                if(!is_array($data)){
                    return Result::error($data);
                }

                $res = validate($this->validate)->scene('edit')->check($data);
                if ($res !== true) {
                    return Result::error($res);
                }
            }
            //数据预处理
            $data = $this->saveBefore($data,'edit');
            if(!is_array($data)){
                return Result::error($data);
            }

            $result = $this->model::bUpdate($data);
            if ($result === false) {
                return Result::error('保存失败');
            }
            if ($result) {
                $this->saveAfter($data, 'edit');
            }
            return Result::success('保存成功');
        } else {
            $id = $request->get('id',0);
            if (!$id) {
                return $this->toError('参数错误');
            }
            $info = $this->model::bFind($id);
            if (!$info) {
                return $this->toError('信息不存在');
            }
            return $this->editRender($info,$request);
        }
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     */
    public function setstatusAction(Request $request): Json
    {
        if ($request->isPost()) {
            $data = $request->post();

            //数据预处理
            $data = $this->saveBefore($data,'status');
            if(!is_array($data)){
                return Result::error($data);
            }

            if (!$data || !isset($data['id']) || !isset($data['status']) || empty($data['id'])) {
                return Result::error('参数错误');
            }
            $status = intval($data['status']) ? 1 : 0;
            $title = $data['name'] ?? '';
            $info = $this->model::bFind($data['id']);
            if (!$info) {
                return Result::error('信息不存在');
            }
            $title = $title ?: ($status ? '启用' : '停用');
            if ($info['status'] == $status) {
                return Result::success($title . '成功');
            }

            $update = [];
            $update[$this->model::primaryKey()] = $data['id'];
            $update['status'] = $status;
            $result = $this->model::bUpdate($update);
            if ($result === false) {
                return Result::error('状态更新失败');
            }
            if ($result) {
                $info['status'] = $status;
                $this->saveAfter($info, 'status');
            }
            return Result::success($title . '成功');
        }
        return Result::error('请求类型错误');
    }

    /**
     * 公共删除单条数据
     * @param Request $request
     * @return \think\response\Json
     */
    public function dropAction(Request $request): Json
    {
        if ($request->isPost()) {
            $id = $request->post('id', '');
            if (!$id) {
                return Result::error('参数缺失');
            }
            $info = $this->model::bFind($id);
            if (!$info) {
                return Result::error('信息不存在或数据已删除');
            }
            //删除前
            $res = $this->deleteBefore($info);
            if ($res !== true) {
                return Result::error($res);
            }

            $result = $this->model::bDelete($id);
            if ($result) {
                //删除后操作
                $this->deleteAfter($info);

                return Result::success('删除成功');
            } else {
                return Result::error('删除失败');
            }
        }
        return Result::error('请求类型错误');
    }

    /**
     * 批量删除
     * @param Request $request
     * @return \think\response\Json
     */
    public function dropallAction(Request $request): Json
    {
        if ($request->isPost()) {
            $ids = $request->post('ids', '');
            if (!$ids) {
                return Result::error('参数缺失');
            }
            $ids = explode(',', $ids);
            foreach ($ids as $id) {
                if (!$id) continue;
                $info = $this->model::bFind($id);
                if ($info) {
                    //删除前
                    $res = $this->deleteBefore($info);
                    if ($res !== true) {
                        continue;
                    }

                    $result = $this->model::bDelete($id);
                    if ($result) {
                        $this->deleteAfter($info);
                    }
                }
            }
            return Result::success('批量删除操作完成');
        }
        return Result::error('请求类型错误');

    }

    /**
     * 将处理首页的过程 单独提取，便于自定义indexAction时使用
     * @param $query
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    protected function indexWhere($query, $params)
    {
        $orderBy = $params['orderBy']??[];//自定义的排序
        $orderByColumn = empty($params['orderByColumn']) ? '' : $params['orderByColumn'];
        $isAsc = empty($params['isAsc']) ? 'asc' : $params['isAsc'];
        $field = $params['field']??'';
        //表单的条件 where 的条件
        if (isset($params['where']) && is_array($params['where'])) {
            foreach ($params['where'] as $key => $value) {
                if ($key && trim($value) !== '') {
                    $query = $query->where($key, $value);
                }
            }
        }

        //表单的条件 in 的条件
        if (isset($params['in']) && is_array($params['in'])) {
            foreach ($params['in'] as $key => $value) {
                if ($key && $value) {
                    $query = $query->whereIn($key, $value);
                }
            }
        }

        //表单的条件 like 的条件
        if (isset($params['like']) && is_array($params['like'])) {
            foreach ($params['like'] as $key => $value) {
                if ($key && trim($value) !== '') {
                    $query = $query->where($key, 'like', '%' . $value . '%');
                }
            }
        }

        //表单的条件 between 的条件
        if (isset($params['between']) && is_array($params['between'])) {
            foreach ($params['between'] as $key => $value) {
                if ($key && is_array($value) && count($value) > 1) {
                    $start = $value['start'] ?? '';
                    $end = $value['end'] ?? '';
                    if ($end) {
                        $end = (new \DateTime($end))->modify('+1 day')->modify('-1 second')->format('Y-m-d H:i:s');
                    }
                    if ($start) {
                        $start = (new \DateTime($start))->format('Y-m-d H:i:s');
                    }
                    if ($start || $end) {
                        if ($start && $end) {
                            $query = $query->whereBetweenTime($key, $start, $end);
                        } elseif ($start) {
                            $query = $query->whereTime($key, '>=', $start);
                        } elseif ($end) {
                            $query = $query->whereTime($key, '<=', $end);
                        }
                    }
                }
            }
        }
        //表单的条件 findinset 的条件
        if (isset($params['findinset']) && is_array($params['findinset'])) {
            foreach ($params['findinset'] as $key => $value) {
                if ($key && trim($value) !== '') {
                    $query = $query->whereFindInSet($key, $value);
                }
            }
        }

        //处理字段
        if($field){
            $query = $query->field($field);
        }

        //处理排序
        if($orderByColumn){
            $query = $query->order($orderByColumn, $isAsc);
        }

        $hasId = false;
        if ($orderBy) { // 指定排序
            foreach ($orderBy as $key => $val) {
                if($key == $orderByColumn) continue;
                if($key == 'id') $hasId = true;
                $query = $query->order($key, $val);
            }
        }
        //默认最后加一个id asc
        if (!$hasId && $orderByColumn != 'id') {
            $query = $query->order('id', 'asc');
        }

        return $query;
    }

    /**
     * 首页渲染，方便重写
     * @param Request $request
     * @return string
     */
    protected function indexRender(Request $request):string{
        return $this->render('', ['input' => $request->get()]);
    }

    /**
     * 添加渲染，方便重写
     * @param Request $request
     * @return string
     */
    protected function addRender(Request $request):string{
        return $this->render('', ['input' => $request->get()]);
    }

    /**
     * 编辑渲染，方便重写
     * @param Request $request
     * @param array $info
     * @return string
     */
    protected function editRender(array $info,Request $request ):string{
        return $this->render('', ['input' => $request->get(), 'info' => $info]);
    }

    /**
     * 首页查询前的params处理，方便添加额外的条件或排序等
     * @param array $params
     * @return array
     */
    protected function indexBefore(array $params): array
    {
        return $params;
    }

    /**
     * 首页查询语句处理，可以用来自定义以及数据权限处理
     * @param Query $query
     * @return mixed 可以返回query对象，也可以一个数组['query'=>$query,'extend'=>[xxx]]  extend将会在ajax中返回
     */
    protected function indexQuery(Query $query){
        //进行权限处理
//        $query = \app\admin\extend\helpers\DataScopeHelper::queryDataScope($query,'struct_id','user_id');
        return $query;
    }
    /**
     * 首页列表查询完的操作，方便对列表进行处理
     * @param array $list
     * @return array
     */
    protected function indexAfter(array $list): array
    {
        return $list;
    }

    /**
     * 添加、编辑验证前的数据处理
     * @param array $data
     * @param string $type
     * @return array|string 正常返回参数数据，返回错误字符串则返回该错误
     */
    protected function validateBefore(array $data, string $type){
        return $data;
    }

    /**
     * 添加、编辑、状态修改前的数据处理
     * @param array $data
     * @param string $type
     * @return array|string 正常返回参数数据，返回错误字符串则返回该错误
     */
    protected function saveBefore(array $data,string $type){
        return $data;
    }

    /**
     * 添加、编辑、状态修改后的操作
     * @param array $data
     * @param string $type
     */
    protected function saveAfter(array $data, string $type): void
    {
    }

    /**
     * 删除后的操作
     * @param array $data
     */
    protected function deleteAfter(array $data): void
    {
    }

    /**
     * 删除前操作
     * @param array $data
     * @return bool|string
     */
    protected function deleteBefore(array $data)
    {
        return true;
    }
	
	/**
     * 导出配置，需配置导出字段 字段名=>标题
     * @param array $list
     * @return array[]
     */
    protected function exportBefore(array $list):array{
        return ['list'=>$list,'attributes'=>[]];
    }
}