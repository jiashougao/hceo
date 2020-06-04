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
namespace Emmetltd\AliyunCore\Api\Cdn\Request\V20141111;

class UpdateFCTriggerRequest extends \Emmetltd\AliyunCore\RpcAcsRequest
{
	function  __construct()
	{
		parent::__construct("Cdn", "2014-11-11", "UpdateFCTrigger");
		$this->setMethod("POST");
	}

	private  $notes;

	private  $triggerARN;

	private  $sourceARN;

	private  $ownerId;

	private  $roleARN;

	private  $functionARN;

	public function getNotes() {
		return $this->notes;
	}

	public function setNotes($notes) {
		$this->notes = $notes;
		}

	public function getTriggerARN() {
		return $this->triggerARN;
	}

	public function setTriggerARN($triggerARN) {
		$this->triggerARN = $triggerARN;
		$this->queryParameters["TriggerARN"]=$triggerARN;
	}

	public function getSourceARN() {
		return $this->sourceARN;
	}

	public function setSourceARN($sourceARN) {
		$this->sourceARN = $sourceARN;
		}

	public function getOwnerId() {
		return $this->ownerId;
	}

	public function setOwnerId($ownerId) {
		$this->ownerId = $ownerId;
		$this->queryParameters["OwnerId"]=$ownerId;
	}

	public function getRoleARN() {
		return $this->roleARN;
	}

	public function setRoleARN($roleARN) {
		$this->roleARN = $roleARN;
		}

	public function getFunctionARN() {
		return $this->functionARN;
	}

	public function setFunctionARN($functionARN) {
		$this->functionARN = $functionARN;
		}
	
}