<?php
namespace LouisLun\LaravelLinepay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LouisLun\LaravelLinepay\Response request($params) 向LINE Pay請求付款資訊
 * @method static \LouisLun\LaravelLinepay\Response confirm($transactionId, $params) 完成交易
 * @method static \LouisLun\LaravelLinepay\Response capture($transactionId, $params) 完成請款
 * @method static \LouisLun\LaravelLinepay\Response void($transactionId, $params) 取消請款
 * @method static \LouisLun\LaravelLinepay\Response refund($transactionId, $params) 退款
 * @method static \LouisLun\LaravelLinepay\Response details($params) 查詢交易紀錄
 * @method static \LouisLun\LaravelLinepay\Response check($transactionId, $params) 主動判斷交易是否完成
 * @method static \LouisLun\LaravelLinepay\Response preapproved($regKey, $params) 自動付款(需要在request與confirm設定自動付款, regkey可以從confirm的response取得)
 * @method static \LouisLun\LaravelLinepay\Response preapprovedCheck($regKey, $params) 檢查自動付款授權
 * @method static \LouisLun\LaravelLinepay\Response preapprovedCheck($regKey, $params) 自動付款過期
 *
 * @see \LouisLun\LaravelLinepay\Linepay
 */
class Linepay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \LouisLun\LaravelLinepay\Linepay::class;
    }
}
