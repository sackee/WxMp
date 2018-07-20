<?php

/**
 * 
 * 微信异常类
 * @author widyhu
 *
 */
class WxException extends \Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
