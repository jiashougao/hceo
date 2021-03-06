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
namespace Emmetltd\AliyunCore\Api\Domain_intl\Request\V20171218;

class SaveSingleTaskForCreatingOrderRedeemRequest extends \Emmetltd\AliyunCore\RpcAcsRequest
{
	function  __construct()
	{
		parent::__construct("Domain-intl", "2017-12-18", "SaveSingleTaskForCreatingOrderRedeem", "domain", "openAPI");
		$this->setMethod("POST");
	}

	private  $promotionNo;

	private  $currentExpirationDate;

	private  $userClientIp;

	private  $domainName;

	private  $couponNo;

	private  $useCoupon;

	private  $lang;

	private  $usePromotion;

	public function getPromotionNo() {
		return $this->promotionNo;
	}

	public function setPromotionNo($promotionNo) {
		$this->promotionNo = $promotionNo;
		$this->queryParameters["PromotionNo"]=$promotionNo;
	}

	public function getCurrentExpirationDate() {
		return $this->currentExpirationDate;
	}

	public function setCurrentExpirationDate($currentExpirationDate) {
		$this->currentExpirationDate = $currentExpirationDate;
		$this->queryParameters["CurrentExpirationDate"]=$currentExpirationDate;
	}

	public function getUserClientIp() {
		return $this->userClientIp;
	}

	public function setUserClientIp($userClientIp) {
		$this->userClientIp = $userClientIp;
		$this->queryParameters["UserClientIp"]=$userClientIp;
	}

	public function getDomainName() {
		return $this->domainName;
	}

	public function setDomainName($domainName) {
		$this->domainName = $domainName;
		$this->queryParameters["DomainName"]=$domainName;
	}

	public function getCouponNo() {
		return $this->couponNo;
	}

	public function setCouponNo($couponNo) {
		$this->couponNo = $couponNo;
		$this->queryParameters["CouponNo"]=$couponNo;
	}

	public function getUseCoupon() {
		return $this->useCoupon;
	}

	public function setUseCoupon($useCoupon) {
		$this->useCoupon = $useCoupon;
		$this->queryParameters["UseCoupon"]=$useCoupon;
	}

	public function getLang() {
		return $this->lang;
	}

	public function setLang($lang) {
		$this->lang = $lang;
		$this->queryParameters["Lang"]=$lang;
	}

	public function getUsePromotion() {
		return $this->usePromotion;
	}

	public function setUsePromotion($usePromotion) {
		$this->usePromotion = $usePromotion;
		$this->queryParameters["UsePromotion"]=$usePromotion;
	}
	
}