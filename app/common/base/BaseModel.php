<?php
namespace app\common\base;

use app\common\kit\StringKit;
use think\db\Query;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * 模型基础类
 */
abstract class BaseModel extends Model
{

    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $hidden = ['create_time', 'update_time', 'delete_time'];

    public function findById($id)
    {
        return $this->where('id', $id)->find();
    }

    public function selectByIds($ids)
    {
        if (is_array($ids)) {
            return $this->where('id', 'in', $ids)->select();
        }
        elseif (is_string($ids)) {
            return $this->where('id', $ids)->select();
        }
        else {
            return [];
        }
    }

    public static function onBeforeWrite(Model $model)
    {
        self::targetDbTaskStart();
    }

    public static function onBeforeUpdate(Model $model)
    {
        self::targetDbTaskStart();
        $data = array_merge($model->getData(), [$model->updateTime => StringKit::getCurrentDatetime()]);
        $model->data($data);
    }

    public static function onBeforeInsert(Model $model)
    {
        self::targetDbTaskStart();
        $data = array_merge($model->getData(), [$model->createTime => StringKit::getCurrentDatetime()]);
        $model->data($data);
    }

    public static function onBeforeRestore(Model $model)
    {
        self::targetDbTaskStart();
    }

    public static function onBeforeDelete(Model $model)
    {
        self::targetDbTaskStart();
    }

    private static function targetDbTaskStart()
    {
        event('dbStartTask');
    }


    /**
     * 删除记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @param  bool  $force 是否强制删除
     * @return bool
     */
    public static function destroy($data, bool $force = false): bool
    {
        // 包含软删除数据
        $query = (new static())->withTrashedData(true)->db(false);
        $resultSet = [];
        if (is_array($data)) {
            $resultSet = $query->where($data)->select();
        } elseif ($data instanceof \Closure) {
            call_user_func_array($data, [ & $query]);
            $resultSet = $query->select();
        } elseif (is_null($data)) {
            return false;
        }

        foreach ($resultSet as $result) {
            $result->force($force)->delete();
        }

        return true;
    }

    public function __call($method, $args)
    {
        return parent::__call($method, $args);
    }
}
