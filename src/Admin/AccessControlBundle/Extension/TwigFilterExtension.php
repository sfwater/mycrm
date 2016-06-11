<?php

namespace Admin\AccessControlBundle\Extension;


class TwigFilterExtension extends \Twig_Extension{


	public function __construct(){
	}

	public function getFilters(){
		return array(
				new \Twig_SimpleFilter("groups",array($this,"groups")),
			);
	}


	public function groups($result, $max=2){
		$newGroups = array();
		for ($i = 0; $i < count($result); $i++){
			$mod = $i % $max;
			if( $mod == 0 ){
				$group = array();
				$newGroups[] = $group;
			}
			$newGroups[count($newGroups)-1][$key] = $item;
		}
		return $newGroups;
	}


	public function getName(){
		return "AcccessControlTwigFilterExtension";
	}
}

?>