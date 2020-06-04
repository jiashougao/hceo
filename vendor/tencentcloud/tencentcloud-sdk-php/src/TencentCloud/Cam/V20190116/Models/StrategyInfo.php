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
namespace TencentCloud\Cam\V20190116\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method integer getPolicyId() 获取策略ID。
 * @method void setPolicyId(integer $PolicyId) 设置策略ID。
 * @method string getPolicyName() 获取策略名称。
 * @method void setPolicyName(string $PolicyName) 设置策略名称。
 * @method string getAddTime() 获取策略创建时间。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setAddTime(string $AddTime) 设置策略创建时间。
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getType() 获取策略类型。1 表示自定义策略，2 表示预设策略。
 * @method void setType(integer $Type) 设置策略类型。1 表示自定义策略，2 表示预设策略。
 * @method string getDescription() 获取策略描述。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setDescription(string $Description) 设置策略描述。
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getCreateMode() 获取创建来源，1 通过控制台创建, 2 通过策略语法创建。
 * @method void setCreateMode(integer $CreateMode) 设置创建来源，1 通过控制台创建, 2 通过策略语法创建。
 * @method integer getAttachments() 获取关联的用户数
 * @method void setAttachments(integer $Attachments) 设置关联的用户数
 * @method string getServiceType() 获取策略关联的产品
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setServiceType(string $ServiceType) 设置策略关联的产品
注意：此字段可能返回 null，表示取不到有效值。
 */

/**
 *策略信息
 */
class StrategyInfo extends AbstractModel
{
    /**
     * @var integer 策略ID。
     */
    public $PolicyId;

    /**
     * @var string 策略名称。
     */
    public $PolicyName;

    /**
     * @var string 策略创建时间。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $AddTime;

    /**
     * @var integer 策略类型。1 表示自定义策略，2 表示预设策略。
     */
    public $Type;

    /**
     * @var string 策略描述。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Description;

    /**
     * @var integer 创建来源，1 通过控制台创建, 2 通过策略语法创建。
     */
    public $CreateMode;

    /**
     * @var integer 关联的用户数
     */
    public $Attachments;

    /**
     * @var string 策略关联的产品
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ServiceType;
    /**
     * @param integer $PolicyId 策略ID。
     * @param string $PolicyName 策略名称。
     * @param string $AddTime 策略创建时间。
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $Type 策略类型。1 表示自定义策略，2 表示预设策略。
     * @param string $Description 策略描述。
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $CreateMode 创建来源，1 通过控制台创建, 2 通过策略语法创建。
     * @param integer $Attachments 关联的用户数
     * @param string $ServiceType 策略关联的产品
注意：此字段可能返回 null，表示取不到有效值。
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
        if (array_key_exists("PolicyId",$param) and $param["PolicyId"] !== null) {
            $this->PolicyId = $param["PolicyId"];
        }

        if (array_key_exists("PolicyName",$param) and $param["PolicyName"] !== null) {
            $this->PolicyName = $param["PolicyName"];
        }

        if (array_key_exists("AddTime",$param) and $param["AddTime"] !== null) {
            $this->AddTime = $param["AddTime"];
        }

        if (array_key_exists("Type",$param) and $param["Type"] !== null) {
            $this->Type = $param["Type"];
        }

        if (array_key_exists("Description",$param) and $param["Description"] !== null) {
            $this->Description = $param["Description"];
        }

        if (array_key_exists("CreateMode",$param) and $param["CreateMode"] !== null) {
            $this->CreateMode = $param["CreateMode"];
        }

        if (array_key_exists("Attachments",$param) and $param["Attachments"] !== null) {
            $this->Attachments = $param["Attachments"];
        }

        if (array_key_exists("ServiceType",$param) and $param["ServiceType"] !== null) {
            $this->ServiceType = $param["ServiceType"];
        }
    }
}
