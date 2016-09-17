<?php
namespace Pinturus\Service;

use Silex\Application;

class Captcha
{
	private $app;
	
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	public function wordRandom($n)
	{
		return substr(md5(uniqid()),0,$n);
	}

	public function numberRandom($n)
	{
		return str_pad(mt_rand(0,pow(10,$n)-1),$n,'0',STR_PAD_LEFT);
	}

	public function generate($word)
	{
		$request = $this->app['request'];
		
		$size = 80;
		$margin = 60;

		$font = $this->app['web_directory'].'/font/Edmundsbury_Serif.ttf';

		$matrix_blur = array(
			array(1,1,1),
			array(1,1,1),
			array(1,1,1));
			
		$box = imagettfbbox($size, 0, $font, $word);
		$width = $box[2] - $box[0];
		$height = $box[1] - $box[7];
		$width_letter = round($width/strlen($word));
		

		
		$img = imagecreate($width+$margin, $height+$margin);
		$white = imagecolorallocate($img, 255, 255, 255); 
		$black = imagecolorallocate($img, 0, 0, 0);
		
		$color = array(
			imagecolorallocate($img, 0x99, 0x00, 0x66),
			imagecolorallocate($img, 0xCC, 0x00, 0x00),
			imagecolorallocate($img, 0x00, 0x00, 0xCC),
			imagecolorallocate($img, 0x00, 0x00, 0xCC),
			imagecolorallocate($img, 0xBB, 0x88, 0x77));

		for($i = 0; $i < strlen($word);++$i)
		{
			$l = $word[$i];
			$angle = mt_rand(-35,35);
			imagettftext($img,mt_rand($size-7,$size),$angle,($i*$width_letter)+$margin, $height+mt_rand(0,$margin/2),$color[array_rand($color)], $font, $l);	
		}
		
		imageline($img, 2,mt_rand(2,$height), $width+$margin, mt_rand(2,$height), $black);
		imageline($img, 2,mt_rand(2,$height), $width+$margin, mt_rand(2,$height), $black);
		

		imageconvolution($img, $matrix_blur,9,0);
		imageconvolution($img, $matrix_blur,9,0);
		
		ob_start();
		
		imagepng($img);
		
		$contents =  ob_get_contents();
		ob_end_clean();

		imagedestroy($img);
		
		$this->app['session']->set('captcha_word', $word);

		return base64_encode($contents);
	}
}