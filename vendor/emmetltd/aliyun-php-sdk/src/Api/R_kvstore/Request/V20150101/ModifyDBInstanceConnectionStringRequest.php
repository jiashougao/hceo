<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
namespace Emmetltd\AliyunCore\Api\R_kvstore\Request\V20150101;

class ModifyDBInstanceConnectionStringRequest extends \Emmetltd\AliyunCore\RpcAcsRequest
{
	function  __construct()
	{
		parent::__construct("R-kvstore", "2015-01-01", "ModifyDBInstanceConnectionString", "redisa", "openAPI");
		$this->setMethod("POST");
	}

	private  $resourceOwnerId;

	private  $securityToken;

	private  $resourceOwnerAccount;

	private  $ownerAccount;

	private  $dBInstanceId;

	private  $newConnectionString;

	private  $ownerId;

	private  $currentConnectionString;

	public function getResourceOwnerId() {
		return $this->resourceOwnerId;
	}

	public function setResourceOwnerId($resourceOwnerId) {
		$this->resourceOwnerId = $resourceOwnerId;
		$this->queryParameters["ResourceOwnerId"]=$resourceOwnerId;
	}

	public function getSecurityToken() {
		return $this->securityToken;
	}

	public function setSecurityToken($securityToken) {
		$this->securityToken = $securityToken;
		$this->queryParameters["SecurityToken"]=$securityToken;
	}

	public function getResourceOwnerAccount() {
		return $this->resourceOwnerAccount;
	}

	public function setResourceOwnerAccount($resourceOwnerAccount) {
		$this->resourceOwnerAccount = $resourceOwnerAccount;
		$this->queryParameters["ResourceOwnerAccount"]=$resourceOwnerAccount;
	}

	public function getOwnerAccount() {
		return $this->ownerAccount;
	}

	public function setOwnerAccount($ownerAccount) {
		$this->ownerAccount = $ownerAccount;
		$this->queryParameters["OwnerAccount"]=$ownerAccount;
	}

	public function getDBInstanceId() {
		return $this->dBInstanceId;
	}

	public function setDBInstanceId($dBInstanceId) {
		$this->dBInstanceId = $dBInstanceId;
		$this->queryParameters["DBInstanceId"]=$dBInstanceId;
	}

	public function getnewConnectionString() {
		return $this->newConnectionString;
	}

	public function setnewConnectionString($newConnectionString) {
		$this->newConnectionString = $newConnectionString;
		$this->queryParameters["newConnectionString"]=$newConnectionString;
	}

	public function getOwnerId() {
		return $this->ownerId;
	}

	public function setOwnerId($ownerId) {
		$this->ownerId = $ownerId;
		$this->queryParameters["OwnerId"]=$ownerId;
	}

	public function getcurrentConnectionString() {
		return $this->currentConnectionString;
	}

	public function setcurrentConnectionString($currentConnectionString) {
		$this->currentConnectionString = $currentConnectionString;
		$this->queryParameters["currentConnectionString"]=$currentConnectionString;
	}
	
}