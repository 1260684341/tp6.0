<?php
namespace app\common\logic;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use app\common\base\BaseModel;
use app\common\easyAlipay\EasyAlipayFactory;
use app\common\interfaceObj\ITrade;
use app\common\kit\StringKit;
use app\common\model\ZfbPay as ZfbPayModel;

class ZfbPay extends BaseModel
{
    private $expire_min = 5;


    // 支付类型
    const PAY_TYPE_H5 = 1;// 手机H5页面支付
    const PAY_TYPE_PC_PAGE = 2;// PC页面支付
    const PAY_TYPE_APP = 3;// App支付

    /**
     * @param $order_id
     * @param $total_amount 单位元
     * @param string $pay_type
     * @param array $datas [ ... ] 其他支付非必传参数
     */
    public function unifyPay(ITrade $trade, $out_trade_no, $total_amount, $pay_type = self::PAY_TYPE_H5, array $datas = [])
    {
        $data = [
            'pay_type' => $pay_type,
            'callback_class' => get_class($trade),

            'subject' => '商城下单',
            'out_trade_no' => $out_trade_no,
            'total_amount' => $total_amount, // 单位为元，精确到小数点后两位
            'product_code' => 'QUICK_WAP_WAY', // 销售产品码，商家和支付宝签约的产品码

            'timeout_express' => "$this->expire_min}min",
            'time_expire' => date("Y-m-d H:i:s", strtotime("+{$this->expire_min}min")),
            // 下面的非必选参数 自行扩展 https://opendocs.alipay.com/apis/api_1/alipay.trade.wap.pay
        ];


        if (empty($row_alipay)) {
            $this->save($data);
        }
        else {
            $this->save($data, ['id' => $row_alipay['id']]);
        }
        $data = array_merge($data, $datas);

        unset($data['pay_type']);
        unset($data['callback_class']);

        $easy_alipay_factory = EasyAlipayFactory::instance();
        $result = [];

        if ($pay_type == self::PAY_TYPE_H5) {
            $result = $easy_alipay_factory::payment()
                ->wap()
                ->pay($data['subject'], $out_trade_no, $total_amount, $datas['quit_url'], $datas['return_url']);
        }

        $responseChecker = new ResponseChecker();
        if (!empty($result) && $responseChecker->success($result)) {
            return $result;
        } else {
            $msg = isset($result->msg) && isset($result->subMsg)  ? $result->msg."，".$result->subMsg : '未知错误';
            e($msg);
        }
    }


    public function whenPaySuccess($response)
    {
        $model_alipay = new ZfbPayModel();
        $row_alipay = $model_alipay->getByOutTradeNo($response['out_trade_no']);

        if (empty($row_alipay)) {
            return false;
        }

        // 修改支付状态
        $res = $this->save([
            'transaction_id' => $response['transaction_id'],
            'pay_status' => ZfbPayModel::PAY_STATUS_SUCCESS,
            'pay_time' => StringKit::getCurrentDatetime(),
        ], ['id' => $row_alipay['id']]);

        if ($res === false) {
            return false;
        }

        $callback_class = new $row_alipay['callback_class']();
        $res = $callback_class->whenPaySuccess($row_alipay['out_trade_no']);
        return $res;
    }
}