<?php

namespace sms;

use app\api\exception\ApiException;
use GuzzleHttp;

class FeiGe
{
	const ACCOUNT = '';
	const PWD = '';
	const SIGN_ID = '169945';
	const SMS_CODE_TEMPLATE_ID = '54074';

	// 模版短信请求地址
	const URL_SINGLE_SEND_MB = 'http://api.feige.ee/SmsService/Template';

	static public function sendSmsCode($phone, $code)
	{
		$headers = [
			'Accept:application/json;charset=utf-8',
			'Content-Type:application/x-www-form-urlencoded;charset=utf-8',
		];

		$data = [
			'Account' => self::ACCOUNT,
			'Pwd' => self::PWD,
			'Content' => $code,
			'Mobile' => $phone,
			'SignId' => self::SIGN_ID,
			'TemplateId' => self::SMS_CODE_TEMPLATE_ID
		];

		$http = new GuzzleHttp\Client();

		$response = $http->post(self::URL_SINGLE_SEND_MB, [
			'form_params' => $data,
		]);

		$res = json_decode($response->getBody(), true);

		if (empty($res)) {
			e('send_sms_code_fail');
		}

		if ($res['Code'] != 0) {
			// dd($res['Message']);
			e($res['Message']);
		}
	}
}