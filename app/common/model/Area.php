<?php
namespace app\common\model;

use app\common\base\BaseModel;

class Area extends BaseModel
{

	public function getProvince()
	{
		return $this
			->field(['id', 'province', 'province_code'])
            ->group('province_code')
            ->select();
	}

	public function getCity($province_code)
	{
		return $this
            ->where('province_code', $province_code)
			->group('city_code')
			->field(['id', 'city', 'city_code'])
			->select();
	}

	public function getCounty($province_code, $city_code)
	{
		return $this
            ->where('province_code', $province_code)
			->where('city_code', $city_code)
			->group('county_code')
            ->select();
	}


    public function findByAddCode($add_code)
    {
        return $this
            ->where('add_code', $add_code)
            ->find();
    }
}