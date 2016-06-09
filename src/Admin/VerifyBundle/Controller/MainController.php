<?php

namespace Admin\VerifyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class MainController extends Controller
{
	private $defaultOptions = array(
		"width" => 100,
		"height" => 40,
		"codelen" => 4,
		"fontsize" => 20,
		"charset" => "0123456789",
		"font"	=> "fonts/georgia.ttf",
		"authkey" => "authcode",
		);
    public function indexAction(Request $request)
    {
    	$options = $this->container->getParameter("admin_verify.options");

    	$reqOptions = array();

    	foreach (array(
    		"width"=>"width",
    		"height"=>"height",
    		"size"=>"fontsize",
    		"len"=>"codelen"
    		) as $key=>$value) {
    		if( $request->query->has($key) ){
    			$reqOptions[$value] = $request->query->get($key);
    		}
    	}

    	$this->defaultOptions = array_merge($this->defaultOptions, $options, $reqOptions);




    	$imgwidth = $this->defaultOptions["width"]; //图片宽度
		$imgheight = $this->defaultOptions["height"]; //图片高度
		$codelen = $this->defaultOptions["codelen"]; //验证码长度
		$fontsize = $this->defaultOptions["fontsize"]; //字体大小
		$charset = $this->defaultOptions["charset"];
		$font = $this->defaultOptions["font"];



		$im=imagecreatetruecolor($imgwidth,$imgheight);
		$while=imageColorAllocate($im,255,255,255);
		imagefill($im,0,0,$while); //填充图像
		//取得字符串
		$authstr='';
		$_len = strlen($charset)-1;
		for ($i=0;$i<$codelen;$i++) {
		 $authstr .= $charset[mt_rand(0,$_len)];
		}
		// session_start();
		$session = new Session();
		// $session->start();
		$session->set($this->defaultOptions["authkey"], strtolower($authstr));

		// $_SESSION['scode']=strtolower($authstr);//全部转为小写，主要是为了不区分大小写
		//随机画点,已经改为划星星了
		for ($i=0;$i<$imgwidth;$i++){
		    $randcolor=imageColorallocate($im,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
		 imagestring($im,mt_rand(1,5), mt_rand(0,$imgwidth),mt_rand(0,$imgheight), '*',$randcolor);
		    //imagesetpixel($im,mt_rand(0,$imgwidth),mt_rand(0,$imgheight),$randcolor);
		}
		//随机画线,线条数量=字符数量（随便）
		for($i=0;$i<$codelen;$i++) 
		{  
		 $randcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
		 imageline($im,0,mt_rand(0,$imgheight),$imgwidth,mt_rand(0,$imgheight),$randcolor); 
		}
		$_x=intval($imgwidth/$codelen); //计算字符距离
		$_y=intval($imgheight*0.7); //字符显示在图片70%的位置
		for($i=0;$i<strlen($authstr);$i++){
		 $randcolor=imagecolorallocate($im,mt_rand(0,150),mt_rand(0,150),mt_rand(0,150));
		 //imagestring($im,5,$j,5,$imgstr[$i],$color3);
		 // imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
		 imagettftext($im,$fontsize,mt_rand(-30,30),$i*$_x+3,$_y,$randcolor,$font,$authstr[$i]);
		}

		//生成图像
		$response = new StreamedResponse();
		$response->headers->set("Content-Type", "image/png");
		$response->setCallback(function($im){
			imagepng($im);
			imagedestroy($im);
		},array($im));
		// $response->send();
		return $response;
    }
}
