<?php
namespace Emmetltd\AliyunCore\Api\ActionTrail\Request\V20181122;
class LookupEventsRequest extends \Emmetltd\AliyunCore\RpcAcsRequest
{
	function  __construct()
	{
		parent::__construct("ActionTrail", "2017-12-04", "LookupEvents");
		$this->setMethod("GET");
	}
	private  $Action;
	private  $RegionId;
	private  $Request;
	public function getAction() {
		return $this->Action;
	}
	public function setAction($Action) {
		$this->resourceOwnerAccount = $resourceOwnerAccount;
		$this->queryParameters["ResourceOwnerAccount"]=$resourceOwnerAccount;
	}
	public function getRegionId() {
		return $this->RegionId;
	}
	public function setRegionId($RegionId) {
		$this->RegionId = $RegionId;
		$this->queryParameters["RegionId"]=$RegionId;
	}
	public function getRequest() {
		return $this->Request;
	}
	public function setRequest($Request) {
		$this->Request = $Request;
		$this->queryParameters["Request"]=$Request;
	}
	
	
	public function getEvent() {
		return $this->Event;
	}
	public function setEvent($Event) {
		$this->Event = $Event;
		$this->queryParameters["Event"]=$Event;
	}
	
	
	public function getEventType() {
		return $this->EventType;
	}
	public function setEventType($EventType) {
		$this->EventType = $EventType;
		$this->queryParameters["EventType"]=$EventType;
	}
	
	
	public function getServiceName() {
		return $this->ServiceName;
	}
	public function setServiceName($ServiceName) {
		$this->ServiceName = $ServiceName;
		$this->queryParameters["ServiceName"]=$ServiceName;
	}
	
	
	public function getEventName() {
		return $this->EventName;
	}
	public function setEventName($EventName) {
		$this->EventName = $EventName;
		$this->queryParameters["EventName"]=$EventName;
	}
	
	
	public function getUser() {
		return $this->User;
	}
	public function setUser($User) {
		$this->User = $User;
		$this->queryParameters["User"]=$User;
	}
	
	
	public function getResourceType() {
		return $this->ResourceType;
	}
	public function setResourceType($ResourceType) {
		$this->ResourceType = $ResourceType;
		$this->queryParameters["ResourceType"]=$ResourceType;
	}
	
	public function getResourceName() {
		return $this->ResourceName;
	}
	public function setResourceName($ResourceName) {
		$this->ResourceName = $ResourceName;
		$this->queryParameters["ResourceName"]=$ResourceName;
	}
	
	
	public function getEventRW() {
		return $this->EventRW;
	}
	public function setEventRW($EventRW) {
		$this->EventRW = $EventRW;
		$this->queryParameters["EventRW"]=$EventRW;
	}
	
	
	public function getNextToken() {
		return $this->NextToken;
	}
	public function setNextToken($NextToken) {
		$this->NextToken = $NextToken;
		$this->queryParameters["NextToken"]=$NextToken;
	}
	
	public function getMaxResults() {
		return $this->MaxResults;
	}
	public function setMaxResults($MaxResults) {
		$this->MaxResults = $MaxResults;
		$this->queryParameters["MaxResults"]=$MaxResults;
	}	
	
	public function getStartTime() {
		return $this->StartTime;
	}
	public function setStartTime($StartTime) {
		$this->StartTime = $StartTime;
		$this->queryParameters["StartTime"]=$StartTime;
	}	
	
	public function getEndTime() {
		return $this->EndTime;
	}
	public function setEndTime($EndTime) {
		$this->EndTime = $EndTime;
		$this->queryParameters["EndTime"]=$EndTime;
	}	
}