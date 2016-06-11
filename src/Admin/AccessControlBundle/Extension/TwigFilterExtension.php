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


	public function groups($result, $columns=5){
		$max = (count($result) % $columns) == 0 ? (count($result) / $columns) : (count($result) / $columns + 1);
		$newGroups = array();
		$keys = array_keys($result);
		$values = array_values($result);
		for ($i = 0; $i < count($result); $i++){
			$mod = $i % $max;
			if( $mod == 0 ){
				$group = array();
				$newGroups[] = $group;
			}
			$key = $keys[$i];
			$item = $values[$i];
			$newGroups[count($newGroups)-1][$key] = $item;
		}
		return $newGroups;
	}


	public function getName(){
		return "AcccessControlTwigFilterExtension";
	}
}

?>