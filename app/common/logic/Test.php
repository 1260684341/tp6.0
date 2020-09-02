<?php

namespace app\common\logic;
use app\common\base\BaseLogic;
use app\common\model\Test as TestModel;
class Test extends BaseLogic
{


    public function add()
    {
        $test = new TestModel();
        $test->save(['test' => '1']);
        $test->save(['test' => '2']);
        $test->save(['test' => '3']);
        $test->save(['test' => '4']);
        return $test->save(['test' => '1']);
    }

    public function addAll()
    {
        $test = new TestModel();
        $lst_data = [];
        for ($i = 0; $i < 10; $i++) {
            $lst_data[] = [
                'test' => $i + 5
            ];
        }
        return $test->saveAll($lst_data);
    }

    public function edit()
    {
        $test = new TestModel();
        $res1 = $test->update(['test' => '1'], ['id' => 4]);
        $res2 = $test->update(['test' => '2'], ['id' => 5]);

        // 用了where 就不是模型对象了
        $res3 = $test->where('id', 1)->save(['test' => '2']); // 这种不能
        $res4 = $test->where('id', 2)->save(['test' => '3']); // 这种不能
        $res5 = $test->where('id', 3)->update(['test' => '4']);// 这种不能
        $res6 = $test->where('id', 4)->update(['test' => '5']);// 这种不能

        return [
            $res1,
            $res2,
            $res3,
            $res4,
            $res5,
            $res6,
        ];
    }

    public function del()
    {
        $test = new TestModel();
        $condition = [
            ['id', '<=', 1],
        ];
        return $test->destroy($condition);
    }
}