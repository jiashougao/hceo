<?php
namespace Emmetltd\AliyunCore\Api\Sms\Request\V20170525;

class SendBatchSmsRequest extends \Emmetltd\AliyunCore\RpcAcsRequest
{
	function  __construct()
	{
		parent::__construct("Dysmsapi", "2017-05-25", "SendBatchSms");
		$this->setMethod("POST");
	}
	private  $PhoneNumberJson;
	private  $SignNameJson;
	private  $TemplateCode;
	private  $TemplateParamJson;
	private  $SmsUpExtendCodeJson;
	public function getPhoneNumberJson() {
		return $this->PhoneNumberJson;
	}
	public function setPhoneNumberJson($PhoneNumberJson) {
		$this->PhoneNumberJson = $PhoneNumberJson;
		$this->queryParameters["PhoneNumberJson"]=$PhoneNumberJson;
	}
	public function getSignNameJson() {
		return $this->SignNameJson;
	}
	public function setSignNameJson($SignNameJson) {
		$this->SignNameJson = $SignNameJson;
		$this->queryParameters["SignNameJson"]=$SignNameJson;
	}
	public function getTemplateCode() {
		return $this->TemplateCode;
	}
	public function setTemplateCode($TemplateCode) {
		$this->TemplateCode = $TemplateCode;
		$this->queryParameters["TemplateCode"]=$TemplateCode;
	}
	public function getTemplateParamJson() {
		return $this->TemplateParamJson;
	}
	public function setTemplateParamJson($TemplateParamJson) {
		$this->TemplateParamJson = $TemplateParamJson;
		$this->queryParameters["TemplateParamJson"]=$TemplateParamJson;
	}
	public function getSmsUpExtendCodeJson() {
		return $this->SmsUpExtendCodeJson;
	}
	public function setSmsUpExtendCodeJson($SmsUpExtendCodeJson) {
		$this->SmsUpExtendCodeJson = $SmsUpExtendCodeJson;
		$this->queryParameters["SmsUpExtendCodeJson"]=$SmsUpExtendCodeJson;
	}
}