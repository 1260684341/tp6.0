<?php
namespace app\common\model;

use app\common\base\BaseModel;

class UserToken extends BaseModel
{

	public function clean($user_id)
	{
		return $this->where('user_id', $user_id)
			->delete();
	}

    public function saveToken($user_id, $request_code)
    {
        // 清理原来的 token
        $this->clean($user_id);

        return $this->save([
            'user_id' => $user_id,
            'request_code' => $request_code,
        ]);
    }
}