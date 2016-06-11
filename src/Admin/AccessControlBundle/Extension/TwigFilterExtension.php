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
		foreach ($result as $index=>$item) {
			$mod = $index % $max;
			if( $mod == 0 ){
				$group = array();
				$newGroups[] = $group;
			}
			$newGroups[count($newGroups)-1][] = $item;
		}
		return $newGroups;
	}


	public function getName(){
		return "AcccessControlTwigFilterExtension";
	}
}

?>