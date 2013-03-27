<?php 

namespace Mouf\Mvc\Splash\Validators;

use Mouf\Validator\MoufValidatorResult;

use Mouf\Validator\MoufStaticValidatorInterface;

class SplashHtaccessValidator implements MoufStaticValidatorInterface {
	/**
	 * 
	 */
	public static function getStaticValidatorTitle() {
		return "Validating .htaccess file availability";
	}
	
	/**
	 * @return \Mouf\Validator\MoufValidatorResult
	 */
	public static function validateClass() {
		
		if (file_exists(ROOT_PATH.".htaccess")) {
			return new MoufValidatorResult(MoufValidatorResult::WARN, "Unable to find Splash .htaccess file. You should <a href='".MOUF_URL."splashApacheConfig/'>configure the Apache redirection</a>.");
		} else {
			return new MoufValidatorResult(MoufValidatorResult::SUCCESS, ".htaccess file found.");
		}
	}

}