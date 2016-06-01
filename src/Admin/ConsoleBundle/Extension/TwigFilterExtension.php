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


	public function showRoutes($routes, $type='menu'){
		$newRoutes = array();
		foreach ($routes as $item) {
			if( $item->category != 'console' && $item->show && $item->type == $type ){
				$newRoutes[] = $item;
			}
		}
		return $newRoutes;
	}




	public function getName(){
		return "ConsoleTwigFilterExtension";
	}
}

?>