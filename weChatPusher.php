<?php
/*
 * ThinkPHP中用于WeChatPusher的类
 * TP6中将类文件放置与extend文件夹中即可使用
 * 需在构造函数中设置好Token值
 *
 * WeChatPusher官方文档
 * https://wxpusher.zjiecode.com/docs
 * created by PhpStorm
 * Author @zhe 
 * 
 * 2020/12/8 16:54
 */


class weChatPusher
{
    protected $appToken;
    protected $appMsgCheckGate;
    protected $appMsgGate;
    protected $appUserFunGate;
    protected $appQrCreatGate;

    function __construct($Token = '')
    {
        $this->appToken = $Token;
        $this->appMsgGate = 'http://wxpusher.zjiecode.com/api/send/message';
        $this->appMsgCheckGate = 'http://wxpusher.zjiecode.com/api/send/query';
        $this->appUserFunGate = 'http://wxpusher.zjiecode.com/api/fun/wxuser';
        $this->appQrCreatGate = 'http://wxpusher.zjiecode.com/api/fun/create/qrcode';
    }

    /**
     * 快速发送文本信息
     *
     * @param String|null $uid 接收消息的用户ID
     * @param String|null $topicId 接收消息的主题ID
     * @param string $content 需要发送的内容
     * @param String|null $url 接收到推送时原文链接的地址
     * @param false $debug 是否需要查看发送状态
     * @return bool|mixed
     *--------------------------------
     *  使用方法
     *  \weChatPush::quickSend('用户ID','主题ID','推送内容','http://example.com',true);
     *  调用成功返回 true
     *  调用失败返回 服务器提示信息
     *--------------------------------
     *  $debug参数为bool
     *  true 发生错误返回服务器提示
     *  false 发生错误返回false
     *--------------------------------
     */
    public static function quickSend(string $uid = null, string $topicId = null, $content = '', string $url = null, bool $debug = false)
    {
        $push = new weChatPusher();
        if ($uid == null && $topicId == null) {
            return 'uid和topicId不能同时为空';
        }
        /*用于兼容数组内容*/
        if (is_array($content)) {
            $content = json_encode($content, 256);
        }
        $data = http_build_query(
            [
                'appToken' => $push->appToken,
                'uid' => $uid,
                'topicId' => $topicId,
                'content' => $content,
                'url' => $url
            ]
        );
        $url = $push->appMsgGate . '/?' . $data;
        $result = json_decode($push->http_curl($url), true);
        if ($result['success']) {
            return true;
        } else {
            if ($debug) {
                return $result['msg'];
            } else {
                return false;
            }
        }
    }

    /**
     * 发送信息
     * @param null $contents 需要发送的内容
     * @param int $contentType 发送类型
     * @param bool $isUids 是否为uid
     * @param array $array_id uid或topicId数组
     * @param string $url 接收到推送时原文链接的地址
     * @param false $getMessageId 是否需要返回消息ID
     * @return array|array[]|bool|mixed
     *--------------------------------
     * $contents String类型
     * 需要发送的内容 \n 可以换行
     * 有专门为一维、二维数组优化过
     *--------------------------------
     * $contentType int类型
     *  ｜-  1 文本消息
     *  ｜-  2 html消息
     *  ｜-  3 markdown消息
     *--------------------------------
     * $isUids bool类型
     *  ｜-  true 发送信息给用户
     *  ｜-  false 发送信息给主题
     *--------------------------------
     * $url String类型
     * 需要添加协议头 http://或https://
     *--------------------------------
     * $getMessageId bool类型
     *  ｜-  true 接收消息ID及错误信息的多维数组
     *  ｜-  false 仅返回错误消息，若无错误消息则返回true
     *
     */
    public static function send($contents = null, $contentType = 1, $isUids = true, $array_id = [], $url = '', $getMessageId = false)
    {
        $push = new weChatPusher();
        /*兼容数组内容*/
        if (is_array($contents)) {
            if ($contentType == 1) {
                $content = $push->arrayConversion($contents);
                $contentType = 3;
            }
        } else {
            $content = $contents;
        }
        /*处理ID数组*/
        if (!is_array($array_id)) {
            $array_id = ["$array_id"];
        }
        $type = $isUids ? 'uids' : 'topicIds';
        $postData = [
            'appToken' => $push->appToken,
            'contentType' => $contentType,
            'content' => $content,
            $type => $array_id,
            'url' => $url
        ];
        $res = json_decode($push->http_curl($push->appMsgGate, 'POST', $postData), true);
        if ($res['success']) {
            $done = [];
            $error = [];
            $messageId = [];
            foreach ($res['data'] as $k => $key) {
                if ($key['code'] !== 1000) {
                    $error[] = $key;
                } else {
                    $done[] = $key;
                }
            }
            foreach ($done as $key => $value) {
                $messageId[] = $value['messageId'];
            }
            if (empty($error)) {
                if ($getMessageId) {
                    return $messageId;
                } else {
                    return true;
                }
            } else {
                if ($getMessageId) {
                    return ['done' => $messageId, 'error' => $error];
                } else {
                    return $error;
                }
            }
        } else {
            return $res['msg'];
        }
    }

    /**
     * 查询信息发送状态
     * @param $messageId
     * @return bool|mixed
     *--------------------------------
     * $messageId int类型
     *
     *  返回true或错误信息
     *
     */
    public static function checkStatus($messageId)
    {
        $push = new weChatPusher();
        $res = json_decode($push->http_curl($push->appMsgCheckGate . '/' . $messageId), true);
        if ($res['code'] == 1000) {
            return true;
        } else {
            return $res['msg'];
        }
    }

    /**
     * 创建带参数的二维码
     * @param int $validTime
     * @param string $extra
     * @return mixed
     *--------------------------------
     * $validTime int类型
     * 二维码有效期 单位 秒
     * 默认为30分钟有效
     *--------------------------------
     * $extra String类型
     * 需要携带在二维码上的参数
     * 没有传入参数时默认使用时间戳
     */
    public static function creatQr($validTime = 1800, $extra = '')
    {
        $push = new weChatPusher();
        if ($extra == '') {
            $extra = md5(time() . microtime() . 'wxpusher');
        }
        $postData = [
            'appToken' => $push->appToken,
            'extra' => $extra,
            'validTime' => $validTime,
        ];
        $res = json_decode($push->http_curl($push->appQrCreatGate, 'POST', $postData), true);
        if ($res['success']) {
            return $res['data'];
        } else {
            return $res['msg'];
        }
    }

    /**
     * 查询关注应用的用户相关信息
     * @param int $page
     * @param int $pageSize
     * @param string $uid
     * @return mixed
     *--------------------------------
     * $uid String
     * 若传入uid则查询这一具体用户的相关信息
     * 若未传入则查询用户列表内的所有用户信息
     */
    public static function queryUser($page = 1, $pageSize = 100, $uid = '')
    {
        $push = new weChatPusher();
        $queryData = http_build_query(
            [
                'appToken' => $push->appToken,
                'page' => $page,
                'pageSize' => $pageSize,
                'uid' => $uid
            ]
        );
        $res = json_decode($push->http_curl($push->appUserFunGate . '/?' . $queryData), true);
        if ($res['success']) {
            return $res['records'];
        } else {
            return $res['msg'];
        }

    }

    /**
     * 内部函数 用于处理数组信息
     * @param $array
     * @return string|null
     */
    private function arrayConversion($array)
    {
        $content = null;
        $array_value = null;
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        return "最多处理两层数组数据，请检查推送内容。";
                    } else {
                        $array_value .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;" . $k . "&nbsp;→&nbsp;" . $v;
                    }
                }
                $content .= $key . "&nbsp;→&nbsp;" . $array_value . "<br/>";
            } else {
                $content .= $key . "&nbsp;→&nbsp;" . $value . "<br/>";
            }
        }
        return $content;
    }

    /**
     * 内部函数 用于发送http请求
     * @param string $url
     * @param string $type
     * @param null $arr
     * @return bool|string
     */
    private function http_curl(string $url, string $type = 'GET', $arr = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arr, 256));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
            ));
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}
