<?php

namespace Admin\ConsoleBundle\Extension;


class TwigFilterExtension extends \Twig_Extension{


	public function __construct(){
	}

	public function getFilters(){
		return array(
				new \Twig_SimpleFilter("showRoutes",array($this,"showRoutes")),
			);
	}


	public function showRoutes($routes){
		$newRoutes = array();
		foreach ($routes as $item) {
			if( $item->show ){
				$newRoutes[] = $item;
			}
		}
		return $newRoutes;
	}




	public function getName(){
		return "TwigFilterExtension";
	}
}

?>