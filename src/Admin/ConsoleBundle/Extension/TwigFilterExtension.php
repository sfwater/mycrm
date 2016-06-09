<?php

namespace Admin\ConsoleBundle\Extension;


class TwigFilterExtension extends \Twig_Extension{


	public function __construct(){
	}

	public function getFilters(){
		return array(
				new \Twig_SimpleFilter("showRoutes",array($this,"showRoutes")),
				new \Twig_SimpleFilter("notInGroup",array($this,"notInGroup")),
				new \Twig_SimpleFilter("inGroup",array($this,"inGroup")),
			);
	}


	public function showRoutes($routes, $type=''){
		$newRoutes = array();
		foreach ($routes as $item) {
			if( $item->show && ($type == '' ||  $item->type == $type) ){
				$newRoutes[] = $item;
			}
		}
		return $newRoutes;
	}

	public function notInGroup($routes, $group='console'){
		$newRoutes = array();
		foreach ($routes as $key=>$item) {
			if( $key != $group ){
				$newRoutes[$key] = $item;
			}
		}
		return $newRoutes;
	}

	public function inGroup($routes, $group='console'){
		$newRoutes = array();
		foreach ($routes as $key=>$item) {
			if( $key == $group ){
				$newRoutes[$key] = $item;
			}
		}
		return $newRoutes;
	}




	public function getName(){
		return "ConsoleTwigFilterExtension";
	}
}

?>