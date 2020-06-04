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
namespace Emmetltd\AliyunCore\Api\EHPC\Request\V20180412;

class AddUsersRequest extends \Emmetltd\AliyunCore\RpcAcsRequest
{
	function  __construct()
	{
		parent::__construct("EHPC", "2018-04-12", "AddUsers", "ehs", "openAPI");
	}

	private  $clusterId;

	private  $Users;

	public function getClusterId() {
		return $this->clusterId;
	}

	public function setClusterId($clusterId) {
		$this->clusterId = $clusterId;
		$this->queryParameters["ClusterId"]=$clusterId;
	}

	public function getUsers() {
		return $this->Users;
	}

	public function setUsers($Users) {
		$this->Users = $Users;
		for ($i = 0; $i < count($Users); $i ++) {	
			$this->queryParameters['User.' . ($i + 1) . '.Password'] = $Users[$i]['Password'];
			$this->queryParameters['User.' . ($i + 1) . '.Name'] = $Users[$i]['Name'];
			$this->queryParameters['User.' . ($i + 1) . '.Group'] = $Users[$i]['Group'];

		}
	}
	
}