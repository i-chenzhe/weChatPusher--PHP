# wxpusher ThinkPHP 自用工具类
------
[![GitHub issues](https://img.shields.io/github/issues/i-chenzhe/weChatPusher--PHP)](https://github.com/wxpusher/wxpusher-sdk-php/issues)
[![GitHub stars](https://img.shields.io/github/stars/i-chenzhe/weChatPusher--PHP)](https://github.com/meloncn/wxpusher-sdk-php/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/i-chenzhe/weChatPusher--PHP)](https://github.com/wxpusher/wxpusher-sdk-php/network)

## 简介

* 一个基于ThinkPHP 6对 [Wxpusher](http://wxpusher.zjiecode.com) 微信推送服务的快速开发工具类。
* 完整基于 [Wxpusher 文档](http://wxpusher.zjiecode.com/docs/) 实现。
* 优化部分参数调用方式，

## 主要实现功能

> * 快速短文本信息发送
> * 标准文本格式信息，HTML以及markdown文本
> * 创建自定义二维码请求
> * 检查消息发送状态
> * 获取已关注者信息

## 注意事项
*  PHP Version >= 7.1 的系统环境下。
* 发送标准信息，创建二维码操作需启用CURL支持。

## 基本使用方法

#### 使用前准备
引入本类库文件，放置于TP6项目extend文件夹中，可自定义命名空间于您的项目中。

修改类库文件构造函数中Token变量的值
```php
$Token = 'XXXXXXX'
```
#### 1、快速发送消息

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
     
实例代码：
```
\weChatPush::quickSend('用户ID','主题ID','推送内容','http://example.com',true);
```


#### 2、标准信息发送消息

     *--------------------------------
     * 使用方法
     * \weChatPush::send('推送内容','发送类型',true,[1,2,3]'http://example.com',true);
     * 调用成功返回信息ID
     * 调用失败返回服务器信息
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
 
 实例代码：
 
 ```
\weChatPush::send('推送内容','发送类型',true,[1,2,3]'http://example.com',true);
 ```

#### 3、创建参数二维码

     *--------------------------------
     * 使用方法
     * \weChatPush::creatQr(3600,'hello word');
     *--------------------------------
     * $validTime int类型
     * 二维码有效期 单位 秒
     * 默认为30分钟有效
     *--------------------------------
     * $extra String类型
     * 需要携带在二维码上的参数
     * 没有传入参数时默认使用时间戳

实例代码：

```
\weChatPush::creatQr(3600,'hello word');
```


#### 4、检查远程消息发送状态

     *--------------------------------
     * $messageId int类型
     *
     *  返回true或错误信息
     *

实例代码：

```
\weChatPush::checkStatus(123456);
```

#### 5、获取关注用户详情

     *--------------------------------
     * $uid String
     * 若传入uid则查询这一具体用户的相关信息
     * 若未传入则查询用户列表内的所有用户信息

实例代码：
```
\weChatPush::queryUser(1,50);
```



遵循Apache2开源协议发布，并提供免费使用。

[2020][i-chenzhe]


