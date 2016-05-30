<?php

namespace Admin\UserBundle\Extension;

defined('MASK_ADD') || define('MASK_ADD', 1);
defined('MASK_EDIT') || define('MASK_EDIT',2);
defined('MASK_DELETE') || define('MASK_DELETE', 4);

class TwigFilterExtension extends \Twig_Extension{


	public function __construct(){
	}

	public function getFilters(){
		return array(
				new \Twig_SimpleFilter("printUserMask",array($this,"printUserMask")),
			);
	}


	public function printUserMask($mask){
		$masks = array();
		if( ($mask & MASK_ADD) > 0 ){
			$masks[] = '新建';
		}

		if( ($mask & MASK_EDIT) > 0 ){
			$masks[] = '编辑';
		}

		if( ($mask & MASK_DELETE) > 0 ){
			$masks[] = '删除';
		}
		return implode(',', $masks);
	}




	public function getName(){
		return "TwigFilterExtension";
	}
}

?>