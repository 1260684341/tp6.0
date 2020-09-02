<?php
namespace app\common\kit;


class MapKit
{

    // 经纬度转详细地址


    // 经纬度转addcode
    public static function getAddCode($lat, $lng)
    {
        $ak = config('baidu_map_ak');
        $url = "http://api.map.baidu.com/reverse_geocoding/v3/?ak={$ak}&output=json&coordtype=gcj02ll&location={$lat},{$lng}";
        $result = HttpKit::get($url);
        $result = json_decode($result,true);

        if (!isset($result['result']['addressComponent']['adcode'])) {
            return false;
        }
        return $result['result']['addressComponent']['adcode'];
    }

    // 详细地址转经纬度
	public static function getCoordinateByAddress($address)
	{
		$ak = config('baidu_map_ak');
		$url = "http://api.map.baidu.com/geocoding/v3/?address={$address}&output=json&ak={$ak}";
		$result = HttpKit::get($url);
		$coordinate_res = json_decode($result, true);
		if ($coordinate_res['status'] !== 0) {
			return false;
		}

		return [
			'lng' => $coordinate_res['result']['location']['lng'],
			'lat' => $coordinate_res['result']['location']['lat'],
		];
	}
}