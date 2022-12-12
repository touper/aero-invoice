<?php

namespace AeroInvoice\Http;

use AeroInvoice\ClientInterface;
use GuzzleHttp\Client as HttpClient;

class Client implements ClientInterface
{
    protected $client, $config;

    public function __construct($config)
    {
        $uri = rtrim($config['domain'],'/');
        $this->client = new HttpClient([
            'base_uri' => 'http://' . $uri . ':' . intval($config['port']) . '/' . $config['program'] . '/',
            'time_out' => 2.0
        ]);
        $this->config = $config;
    }

    /**
     * 开具发票
     *
     * @param $swno
     * @param $saleTax
     * @param $custName
     * @param $custType
     * @param $invType
     * @param $billType
     * @param $specialRedFlag
     * @param $operationCode
     * @param $kpy
     * @param array $orders
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function buildInvoice($swno,$saleTax,$custName,$custType,$invType,$billType,
                                 $specialRedFlag,$operationCode,$kpy,array $orders,array $options = [])
    {
        $this->_checkSaleTax();
        $totalAmount = 0;
        foreach ($orders as $order) {
            if (!isset($order['billNo']) || empty($order['billNo'])) {
                throw new \Exception('缺少订单号');
            }
            if (strlen($order['billNo']) > 40) {
                throw new \Exception('订单号过长');
            }
            if (!isset($order['items']) || empty($order['items'])) {
                throw new \Exception('缺少订单明细');
            }
            foreach ($order['items'] as $item) {
                if (!isset($item['name']) || empty($item['name'])) {
                    throw new \Exception('明细缺少商品名称');
                }
                if (strlen($item['name']) > 90) {
                    throw new \Exception('商品名称过长');
                }
                if (!isset($item['code']) || empty($item['code'])) {
                    throw new \Exception('明细缺少商品编号');
                }
                if (strlen($item['code']) != 19) {
                    throw new \Exception('商品编号有误');
                }
                if (!isset($item['lineType']) || empty($item['lineType'])) {
                    throw new \Exception('明细缺少发票行性质');
                }
                if (!isset($item['taxRate']) || empty($item['taxRate'])) {
                    throw new \Exception('明细缺少税率');
                }
                if (!isset($item['quantity']) || empty($item['quantity'])) {
                    throw new \Exception('明细缺少数量');
                }
                if (!isset($item['taxPrice']) || empty($item['taxPrice'])) {
                    throw new \Exception('明细缺少单价');
                }
                if (!isset($item['totalAmount']) || empty($item['totalAmount'])) {
                    throw new \Exception('明细缺少含税金额');
                }
                if (!isset($item['yhzcbs'])) {
                    throw new \Exception('明细缺少税收优惠政策标志');
                }
                $totalAmount += $item['totalAmount'];
            }
        }
        $requestData = [
            'swno' => $swno,
            'saleTax' => $this->config['sale_tax'],
            'store' => $options['store']??'',
            'custName' => $custName,
            'custTaxNo' => $options['custTaxNo']??'',
            'custAddr' => $options['custAddr']??'',
            'custTelephone' => $options['custTelephone']??'',
            'custPhone' => $options['custPhone']??'',
            'custEmail' => $options['custEmail']??'',
            'custBankAccount' => $options['custBankAccount']??'',
            'custType' => $custType,
            'invoMemo' => $options['invoMemo']??'',
            'invType' => $invType,
            'oilIdentification' => $options['oilIdentification']??'',
            'billDate' => $options['billDate']??'',
            'thdh' => $options['thdh']??'',
            'billType' => $billType,
            'specialRedFlag' => $specialRedFlag,
            'operationCode' => $operationCode,
            'kpy' => $kpy,
            'sky' => $options['sky']??'',
            'fhr' => $options['fhr']??'',
            'yfpdm' => $options['yfpdm']??'',
            'yfphm' => $options['yfphm']??'',
            'chyy' => $options['chyy']??'',
            'saleAddr' => $options['saleAddr']??'',
            'salePhone' => $options['salePhone']??'',
            'saleBankAddr' => $options['saleBankAddr']??'',
            'saleBankAccount' => $options['saleBankAccount']??'',
            'spare1' => $options['spare1']??'',
            'spare2' => $options['spare2']??'',
            'spare3' => $options['spare3']??'',
            'spare4' => $options['spare4']??'',
            'spare5' => $options['spare5']??'',
            'orders' => $orders
        ];
        $config = $this->config;
        if (!empty($config['verified'])) {
            if (empty($config['secretKey'])) {
                throw new \Exception('未配置秘钥');
            }
            $requestData['verified'] = 1;
            $requestData['secretKey'] = base64_encode(md5($saleTax.'|'.$swno.'|'.$config['secretKey'].'|'.$totalAmount));
        } else {
            $requestData['verified'] = 0;
            $requestData['secretKey'] = '';
        }

        return $this->client->request('post','jsonToBillEntityController.do?build_invoice',[
            'json' => $requestData
        ]);
    }

    /**
     * 发票下载
     *
     * @param $swno
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getInvoice($swno)
    {
        $this->_checkSaleTax();

        return $this->client->request('post','jsonToBillEntityController.do?get_invoice',[
            'json' => [
                'swno' => $swno,
                'saleTax' => $this->config['sale_tax']
            ]
        ]);
    }

    /**
     * 发票余量查询
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getKPYL()
    {
        $this->_checkSaleTax();

        return $this->client->request('post','jsonToBillEntityController.do?getKPYL',[
            'json' => [
                'saleTax' => $this->config['sale_tax']
            ]
        ]);
    }

    /**
     * 发票冲红
     *
     * @param $fpdm
     * @param $fphm
     * @param $redcode
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function redSubmit($fpdm, $fphm, $redcode)
    {
        return $this->client->request('post','jsonToBillEntityController.do?redSubmitEInvoiceInfo',[
            'json' => [
                'fpdm' => $fpdm,
                'fphm' => $fphm,
                'redcode' => $redcode
            ]
        ]);
    }

    /**
     * 单据删除
     *
     * @param $swno
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteBill($swno)
    {
        return $this->client->request('post','jsonToBillEntityController.do?deletebill',[
            'json' => [
                'swno' => $swno
            ]
        ]);
    }

    /**
     * 更改交付信息
     *
     * @param $invCode
     * @param $reSend
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function changeInvoiceInfo($invCode, $reSend, $options = [])
    {
        return $this->client->request('post','jsonToBillEntityController.do?deletebill',[
            'json' => [
                'invCode' => $invCode,
                'reSend' => $reSend,
                'invNo' => $options['invNo']??'',
                'phone' => $options['phone']??'',
                'email' => $options['email']??''
            ]
        ]);
    }

    private function _checkSaleTax()
    {
        if (empty($this->config['sale_tax'])) throw new \Exception('配置文件未配置税号');
    }
}