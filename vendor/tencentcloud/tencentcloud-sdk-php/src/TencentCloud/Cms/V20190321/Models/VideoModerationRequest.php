<?php
/*
 * Copyright (c) 2017-2018 THL A29 Limited, a Tencent company. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace TencentCloud\Cms\V20190321\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getCallbackUrl() 获取回调URL，音频识别结果将以POST请求方式发送到此地址
 * @method void setCallbackUrl(string $CallbackUrl) 设置回调URL，音频识别结果将以POST请求方式发送到此地址
 * @method string getFileMD5() 获取视频文件MD5
 * @method void setFileMD5(string $FileMD5) 设置视频文件MD5
 * @method string getFileContent() 获取视频内容base64
 * @method void setFileContent(string $FileContent) 设置视频内容base64
 * @method string getFileUrl() 获取视频内容Url,其中FileUrl与FileContent二选一
 * @method void setFileUrl(string $FileUrl) 设置视频内容Url,其中FileUrl与FileContent二选一
 */

/**
 *VideoModeration请求参数结构体
 */
class VideoModerationRequest extends AbstractModel
{
    /**
     * @var string 回调URL，音频识别结果将以POST请求方式发送到此地址
     */
    public $CallbackUrl;

    /**
     * @var string 视频文件MD5
     */
    public $FileMD5;

    /**
     * @var string 视频内容base64
     */
    public $FileContent;

    /**
     * @var string 视频内容Url,其中FileUrl与FileContent二选一
     */
    public $FileUrl;
    /**
     * @param string $CallbackUrl 回调URL，音频识别结果将以POST请求方式发送到此地址
     * @param string $FileMD5 视频文件MD5
     * @param string $FileContent 视频内容base64
     * @param string $FileUrl 视频内容Url,其中FileUrl与FileContent二选一
     */
    function __construct()
    {

    }
    /**
     * 内部实现，用户禁止调用
     */
    public function deserialize($param)
    {
        if ($param === null) {
            return;
        }
        if (array_key_exists("CallbackUrl",$param) and $param["CallbackUrl"] !== null) {
            $this->CallbackUrl = $param["CallbackUrl"];
        }

        if (array_key_exists("FileMD5",$param) and $param["FileMD5"] !== null) {
            $this->FileMD5 = $param["FileMD5"];
        }

        if (array_key_exists("FileContent",$param) and $param["FileContent"] !== null) {
            $this->FileContent = $param["FileContent"];
        }

        if (array_key_exists("FileUrl",$param) and $param["FileUrl"] !== null) {
            $this->FileUrl = $param["FileUrl"];
        }
    }
}
