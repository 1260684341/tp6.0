<?php
namespace app\common\base;

/**
 * 逻辑基础类
 */
abstract class BaseLogic
{


    public function __call($name, $args)
    {
        $model_class_name = str_replace("app\common\logic", 'app\common\model', get_class($this));
        if (class_exists($model_class_name)) {
            if (empty($this->model)) {
                $this->model = new $model_class_name();
            }
            return call_user_func_array([$this->model, $name], $args);
        }
    }
}
