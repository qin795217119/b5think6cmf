<?php
// +----------------------------------------------------------------------
// | B5ThinkCMF [快捷通用基础管理开发平台]
// +----------------------------------------------------------------------
// | Author: 冰舞 <357145480@qq.com>
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\common;


use think\facade\Db;

/**
 * 默认只用查询构造器进行数据库操作，该基类只是对该系统常用的DB操作的一部分封装
 * 若想使用原来的model 可以让其继承think\Model
 */
class BaseModel
{
    /**
     * 表的名称，全名称
     * @var string
     */
    protected $table;

    /**
     * 主键字段
     * @var string
     */
    protected $pk = 'id';

    /**
     * 是否需要自动写入时间戳
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 初始化过的模型.
     * @var array
     */
    protected static $instance = [];

    /**
     * 静态方法获取表名
     * @return string
     */
    public static function tableName(): string
    {
        return static::getInstance()->table;
    }

    /**
     * 静态方法获取是否需要自动写入时间戳
     * @return string
     */
    public static function autoTimestamp(): bool
    {
        return (static::getInstance())->autoWriteTimestamp;
    }

    /**
     * 静态方法获取主键字段
     * @return string
     */
    public static function primaryKey(): string
    {
        return (static::getInstance())->pk;
    }

    /**
     * 单例模式获取当前对象
     * @return mixed
     */
    public static function getInstance(): object
    {
        if (!isset(static::$instance[static::class])) {
            static::$instance[static::class] = new static();
        }
        return static::$instance[static::class];
    }

    /**
     * 插入方法
     * @param array $data
     * @param bool $getLastInsID
     * @return int
     */
    public static function bInsert(array $data,bool $getLastInsID = false):int
    {
        if(!$data) return 0;

        //主键删除可能含有的主键
        $pk = static::primaryKey();
        if($pk && isset($data[$pk])){
            unset($data[$pk]);
        }

        //判断是否开启自动插入时间
        if(static::autoTimestamp()){
            $data['create_time'] = $data['update_time'] = (new \DateTime())->format('Y-m-d H:i:s');
        }
        $result = Db::table(static::tableName())->strict(false)->insert($data,$getLastInsID);
        return $result?intval($result):0;
    }

    /**
     * 更新数据
     * @param array $data
     * @param array $where
     * @return false|int|string
     */
    public static function bUpdate(array $data,array $where = [])
    {
        if(!$data) return false;

        //判断是否含有主键或条件
        $pk = static::primaryKey();
        if(($pk && (!isset($data[$pk]) || !$data[$pk])) && !$where){
            return false;
        }

        //判断是否开启自动插入时间
        if(static::autoTimestamp()){
            $data['update_time'] = (new \DateTime())->format('Y-m-d H:i:s');
        }
        $query = Db::table(static::tableName());
        if($where){
            $query = $query->where($where);
        }
        try {
            $result = $query->strict(false)->save($data);
        } catch (\Exception $exception){
            $result = false;
        }
        return $result;
    }

    /**
     * 删除操作
     * @param string|int $id
     * @param string $field
     * @return int|bool
     * @throws \think\db\exception\DbException
     */
    public static function bDelete($id, string $field = '')
    {
        if(!$id) return false;
        if(!$field){
            $pk = static::primaryKey();
            if(!$pk) return false;
            $field = $pk;
        }
        return Db::table(static::tableName())->where($field,$id)->delete();
    }


    /**
     * 获取一条数据
     * @param $id
     * @param string|array $where 数组格式为[['field','=','xxxx'],xxxx]
     * @return array|mixed|Db|Model|null
     */
    public static function bFind($id,$where = null){
        $query = Db::table(static::tableName());
        if($id){
            $query = $query->where(static::primaryKey(),$id);
        }
        if($where){
            $query = $query->where($where);
        }
        try {
            return $query->find();
        }catch (\Exception $exception){
            return null;
        }
    }

    /**
     * 获取多条数据
     * @param array $where 二维数组形式，复杂查询请使用db查询
     * @param array $order ['id'=>'asc',xxx]
     * @param string|array $field
     * @return array
     */
    public static function bSelect(array $where = [],array $order = [],$field = null):array{
        $query = Db::table(static::tableName());
        if($where){
            $query = $query->where($where);
        }
        if($field){
            $query = $query->field($field);
        }
        if($order){
            $query = $query->order($order);
        }
        try {
            return $query->select()->toArray();
        }catch (\Exception $exception){
            return [];
        }
    }
}