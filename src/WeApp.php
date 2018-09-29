<?php
/**
 * Created by PhpStorm.
 * User: Jiawei
 * Date: 2017/7/29
 * Time: 10:04
 */

namespace JiaweiXS\WeApp;

use JiaweiXS\WeApp\Api\CustomMsg;
use JiaweiXS\WeApp\Api\QRCode;
use JiaweiXS\WeApp\Api\SessionKey;
use JiaweiXS\WeApp\Api\Statistic;
use JiaweiXS\WeApp\Api\TemplateMsg;
use JiaweiXS\SimpleCache;
use JiaweiXS\WeApp\Common\ErrorCode;

class WeApp
{
    private $appid;
    private $secret;
    private $instance;

    public function __construct($appid, $secret, $token_cache_dir)
    {
        $this->appid = $appid;
        $this->secret = $secret;
        $this->instance = [];
        SimpleCache::init($token_cache_dir);
    }

    /**
     * @param $code
     * @return array sessionkey相关数组
     */
    public function getSessionKey($code)
    {
        if (!isset($this->instance['sessionkey'])) {
            $this->instance['sessionkey'] = new SessionKey($this->appid, $this->secret);
        }
        return $this->instance['sessionkey']->get($code);
    }

    /**
     * @return TemplateMsg 模板消息对象
     */
    public function getTemplateMsg()
    {
        if (!isset($this->instance['template'])) {
            $this->instance['template'] = new TemplateMsg($this->appid, $this->secret);
        }
        return $this->instance['template'];
    }

    /**
     * @return QRCode 二维码对象
     */
    public function getQRCode()
    {
        if (!isset($this->instance['qrcode'])) {
            $this->instance['qrcode'] = new QRCode($this->appid, $this->secret);
        }
        return $this->instance['qrcode'];
    }

    /**
     * @return Statistic 数据统计对象
     */
    public function getStatistic()
    {
        if (!isset($this->instance['statistic'])) {
            $this->instance['statistic'] = new Statistic($this->appid, $this->secret);
        }
        return $this->instance['statistic'];
    }

    /**
     * @return CustomMsg 客户消息对象
     */
    public function getCustomMsg()
    {
        if (!isset($this->instance['custommsg'])) {
            $this->instance['custommsg'] = new CustomMsg($this->appid, $this->secret);
        }
        return $this->instance['custommsg'];
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $sessionKey
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($sessionKey, $encryptedData, $iv, &$data)
    {
        if (strlen($sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result);
        if ($dataObj == null) {
            return ErrorCode::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result;
        return ErrorCode::$OK;
    }
//
//    const QRCODE_UNLIMIT_URL = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=';
//
//    /**
//     * 获取QrCode
//     * @author godtoy@oeynet.com
//     * @date   2018/9/29
//     * @param $scene
//     * @param $page
//     * @param array $options
//     */
//    public function qrcodeUnlimit($scene, $page, $options = [])
//    {
//
//
//    }
}
