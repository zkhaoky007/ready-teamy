<?php
/**
 * 图像处理类
 * @author Tianfeng.Han
 * @package SwooleSystem
 * @subpackage Image
 * @link http://code.google.com/p/swoole/
 */
class Image
{
	/**
	 * 裁切图片
	 * @param $pic 源图像
	 * @param $dst_pic 目标图像
	 * @param $width 宽度
	 * @param $height 高度
	 * @param $qulitity 质量
	 * @return unknown_type
	 */
	static function cut($pic,$dst_pic,$width,$height=null,$qulitity=100)
	{
		$im = imagecreatefromjpeg($pic);

		if(imagesx($im)>$width)
		{
			$old_w = imagesx($im);
			$old_h = imagesy($im);

			if($height==null)
			{
				$w_h = $old_w/$old_h;
				$height = $width*$w_h;
			}

			$newim = imagecreatetruecolor($width, $height);
			imagecopyresampled($newim, $im, 0, 0, 0, 0, $width,$height,$old_w,$old_h);
			imagejpeg($newim, $dst_pic,$qulitity);
			imagedestroy($im);
			return true;
		}
		elseif($pic!=$dst_pic) copy($pic,$dst_pic);
	}
	/**
	 * 压缩图像尺寸
	 * @param $pic 源图像
	 * @param $dst_pic 目标图像
	 * @param $width 宽度
	 * @param $height 高度
	 * @param $qulitity 质量
	 * @return unknown_type
	 */
	static function thumbnail($pic,$dst_pic,$max_width,$max_height=null,$qulitity=100)
	{
		$im = self::readfile($pic);
		$old_w = imagesx($im);
		$old_h = imagesy($im);

		if($max_height==null) $max_height=$max_width;

		if($old_w>$max_width or $old_h>$max_height)
		{

			$w_h = $old_w / $old_h;
			$h_w = $old_h / $old_w;
			if($w_h>$h_w)
			{
				$width = $max_width;
				$height = $width*$h_w;
			}
			else
			{
				$height = $max_height;
				$width = $height*$w_h;
			}
			$newim = imagecreatetruecolor($width, $height);
			imagecopyresampled($newim, $im, 0, 0, 0, 0, $width,$height,$old_w,$old_h);
			imagejpeg($newim, $dst_pic,$qulitity);
			imagedestroy($im);
		}
		elseif($pic!=$dst_pic) copy($pic,$dst_pic);
	}
	/**
	 * 读取图像
	 * @param $pic
	 * @return unknown_type
	 */
	static function readfile($pic)
	{
		$image_info = getimagesize($pic);
		if($image_info["mime"]=="image/jpeg" || $image_info["mime"]=="image/gif" || $image_info["mime"]=="image/png")
		{
			switch($image_info["mime"])
			{
				case "image/jpeg":
					$im = imagecreatefromjpeg($pic);
					break;
				case "image/gif":
					$im = imagecreatefromgif($pic);
					break;
				case "image/png":
					$im = imagecreatefrompng($pic);
					break;
			}
			return $im;
		}
		return false;
	}
	/**
	 * 加给图片加水印
	 *
	 * @param strimg $groundImage 要加水印地址
	 * @param int $waterPos 水印位置
	 * @param string $waterImage 水印图片地址
	 * @param string $waterText 文本文字
	 * @param int $textFont 文字大小
	 * @param string $textColor 文字颜色
	 * @param int $minWidth 小于此值不加水印
	 * @param int $minHeight 小于此值不加水印
	 * @param float $alpha 透明度
	 * @return FALSE
	 */
	public static function waterMark($groundImage , $waterPos = 0 , $waterImage = "" , $waterText = "" , $textFont = 15 , $textColor = "#FF0000",$minWidth='100',$minHeight='100',$alpha=0.9)
	{
		$isWaterImg = FALSE;
		$bg_h = $bg_w = $water_h = $water_w = 0;
		//获取背景图的高，宽
		if(is_file($groundImage) && !empty($groundImage)){
			$bg = new Imagick();
			$bg ->readImage($groundImage);
			$bg_h = $bg->getImageHeight();
			$bg_w = $bg->getImageWidth();
		}
		//获取水印图的高，宽
		if(is_file($waterImage) && !empty($waterImage)){
			$water = new Imagick($waterImage);
			$water_h = $water->getImageHeight();
			$water_w = $water->getImageWidth();
		}
		//如果背景图的高宽小于水印图的高宽或指定的高和宽则不加水印
		if($bg_h < $minHeight || $bg_w < $minWidth || $bg_h < $water_h || $bg_w < $water_w ) {
			return ;
		}else {
			$isWaterImg = TRUE;
		}
		//加水印
		if($isWaterImg){
			$dw = new ImagickDraw();
			//加图片水印
			if(is_file($waterImage)){
				$water->setImageOpacity($alpha);
				$dw -> setGravity($waterPos);
				$dw -> composite($water->getImageCompose(),0,0,50,0,$water);
				$bg -> drawImage($dw);
				if(!$bg -> writeImage($groundImage)){
					return FALSE;
				}
			}else{
				//加文字水印
				$dw -> setFontSize($textFont);
				$dw -> setFillColor($textColor);
				$dw -> setGravity($waterPos);
				$dw -> setFillAlpha($alpha);
				$dw -> annotation(0,0,$waterText);
				$bg -> drawImage($dw);
				if(!$bg -> writeImage($groundImage)){
					return FALSE;
				}
			}
		}
	}
	/**
	 * PHP图片水印 (水印支持图片或文字)
	 * 注意：Support GD 2.0，Support FreeType、GIF Read、GIF Create、JPG 、PNG
	 *      $waterImage 和 $waterText 最好不要同时使用，选其中之一即可，优先使用 $waterImage。
	 *      当$waterImage有效时，参数$waterString、$stringFont、$stringColor均不生效。
	 *      加水印后的图片的文件名和 $groundImage 一样。
	 * @param $groundImage  背景图片，即需要加水印的图片，暂只支持GIF,JPG,PNG格式；
	 * @param $waterPos  水印位置，有10种状态，0为随机位置；1为顶端居左，2为顶端居中，3为顶端居右；4为中部居左，5为中部居中，6为中部居右；7为底端居左，8为底端居中，9为底端居右；
	 * @param $waterImage  图片水印，即作为水印的图片，暂只支持GIF,JPG,PNG格式；
	 * @param $waterText   文字水印，即把文字作为为水印，支持ASCII码，不支持中文；
	 * @param $textFont    文字大小
	 * @param $textColor   文字颜色，值为十六进制颜色值，默认为#FF0000(红色)；
	 * @param $minwidth
	 * @param $minheight
	 * @return unknown_type
	 */
	public static function waterMark2($groundImage , $waterPos = 0 , $waterImage = "" , $waterText = "" , $textFont = 5 , $textColor = "#FF0000",$minwidth,$minheight)
	{
		$isWaterImage = FALSE ;
		//$waterImage = SYSROOTPATH . $waterImage;

		$formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。" ;
		//读取水印文件
		if(!empty( $waterImage ) && file_exists ( $waterImage )) {
			$isWaterImage = TRUE ;
			$water_info = getimagesize ( $waterImage );
			$water_w     = $water_info [ 0 ]; //取得水印图片的宽
			$water_h     = $water_info [ 1 ]; //取得水印图片的高

			switch( $water_info [ 2 ]) //取得水印图片的格式
			{
				case 1 : $water_im = imagecreatefromgif ( $waterImage );break;
				case 2 : $water_im = imagecreatefromjpeg ( $waterImage );break;
				case 3 : $water_im = imagecreatefrompng ( $waterImage );break;
				default:die( $formatMsg );
			}
		}
		//读取背景图片
		if(!empty( $groundImage ) && file_exists ( $groundImage )) {
			$ground_info = getimagesize ( $groundImage );
			$ground_w     = $ground_info [ 0 ]; //取得背景图片的宽
			$ground_h     = $ground_info [ 1 ]; //取得背景图片的高

			switch( $ground_info [ 2 ]) //取得背景图片的格式
			{
				case 1 : $ground_im = imagecreatefromgif ( $groundImage );break;
				case 2 : $ground_im = imagecreatefromjpeg ( $groundImage );break;
				case 3 : $ground_im = imagecreatefrompng ( $groundImage );break;
				default:die( $formatMsg );
			}
		} else {
			die( "需要加水印的图片不存在！" );
		}
		//水印位置
		if( $isWaterImage ) //图片水印
		{
			$w = $water_w ;
			$h = $water_h ;
			$label = "图片的" ;
		}
		else //文字水印
		{
			$temp = imagettfbbox ( ceil ( $textFont * 2.5 ), 0 , SYSROOTPATH."images/watermark/ant1.ttf" , $waterText ); //取得使用 TrueType 字体的文本的范围
			$w = $temp [ 2 ] - $temp [ 6 ];
			$h = $temp [ 3 ] - $temp [ 7 ];
			unset( $temp );
			$label = "文字区域" ;
		}
		// add
		if( ( $ground_w < $w ) || ( $ground_h < $h ) || ( $ground_w < $minwidth ) || ( $ground_h < $minheight ) ){
			echo "需要加水印的图片的长度或宽度比水印" . $label . "还小，无法生成水印！" ;
			return;
		}
		switch( $waterPos ) {
			case 0 : //随机
				$posX = rand ( 0 ,( $ground_w - $w ));
				$posY = rand ( 0 ,( $ground_h - $h ));
				break;
			case 1 : //1为顶端居左
				$posX = 0 ;
				$posY = 0 ;
				break;
			case 2 : //2为顶端居中
				$posX = ( $ground_w - $w ) / 2 ;
				$posY = 0 ;
				break;
			case 3 : //3为顶端居右
				$posX = $ground_w - $w ;
				$posY = 0 ;
				break;
			case 4 : //4为中部居左
				$posX = 0 ;
				$posY = ( $ground_h - $h ) / 2 ;
				break;
			case 5 : //5为中部居中
				$posX = ( $ground_w - $w ) / 2 ;
				$posY = ( $ground_h - $h ) / 2 ;
				break;
			case 6 : //6为中部居右
				$posX = $ground_w - $w ;
				$posY = ( $ground_h - $h ) / 2 ;
				break;
			case 7 : //7为底端居左
				$posX = 0 ;
				$posY = $ground_h - $h ;
				break;
			case 8 : //8为底端居中
				$posX = ( $ground_w - $w ) / 2 ;
				$posY = $ground_h - $h ;
				break;
			case 9 : //9为底端居右
				$posX = $ground_w - $w ;
				$posY = $ground_h - $h ;
				break;
			default: //随机
				$posX = rand ( 0 ,( $ground_w - $w ));
				$posY = rand ( 0 ,( $ground_h - $h ));
				break;
		}
		//设定图像的混色模式
		imagealphablending ( $ground_im , true );
		if( $isWaterImage ) //图片水印
		{
			imagecopy ( $ground_im , $water_im , $posX , $posY , 0 , 0 , $water_w , $water_h ); //拷贝水印到目标文件
		}
		else //文字水印
		{
			if( !empty( $textColor ) && ( strlen ( $textColor )== 7 ) )
			{
				$R = hexdec ( substr ( $textColor , 1 , 2 ));
				$G = hexdec ( substr ( $textColor , 3 , 2 ));
				$B = hexdec ( substr ( $textColor , 5 ));
			}
			else
			{
				die( "水印文字颜色格式不正确！" );
			}
			imagestring ( $ground_im , $textFont , $posX , $posY , $waterText , imagecolorallocate ( $ground_im , $R , $G , $B ));
		}
		//生成水印后的图片
		@unlink ( $groundImage );
		switch( $ground_info [ 2 ]) //取得背景图片的格式
		{
			case 1 : imagegif ( $ground_im , $groundImage );break;
			case 2 : imagejpeg ( $ground_im , $groundImage );break;
			case 3 : imagepng ( $ground_im , $groundImage );break;
			default:die( $errorMsg );
		}
		//释放内存
		if(isset( $water_info )) unset( $water_info );
		if(isset( $water_im )) imagedestroy ( $water_im );
		unset( $ground_info );
		imagedestroy ( $ground_im );
	}
	/**
	 * 生成验证码使用GD
	 * @param $img_width
	 * @param $img_height
	 * @return unknown_type
	 */
	static function authcode_gd($img_width=80,$img_height=30)
	{
		session_start();
		$authnum = '';
		srand(microtime() * 100000);
		for($Tmpa=0;$Tmpa<4;$Tmpa++)
		{
			$authnum.=dechex(rand(0,15));
		}
		$authnum = strtoupper($authnum);;
		$_SESSION['authcode'] = $authnum;

		$aimg = imageCreate($img_width,$img_height);       //生成图片
		ImageColorAllocate($aimg, 255,255,255);            //图片底色，ImageColorAllocate第1次定义颜色PHP就认为是底色了

		//下面该生成雪花背景了，其实就是在图片上生成一些符号
		for($i=1; $i<=128; $i++)
		{
			imageString($aimg,1,mt_rand(1,$img_width),mt_rand(1,$img_height),"*",imageColorAllocate($aimg,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255)));
			//其实也不是雪花，就是生成＊号而已。为了使它们看起来"杂乱无章、5颜6色"，就得在1个1个生成它们的时候，让它们的位置、颜色，甚至大小都用随机数，rand()或mt_rand都可以完成。
		}
		for($i=0;$i<strlen($_SESSION['authcode']);$i++)
		{
			imageString($aimg, mt_rand(8,12),$i*$img_width/4+mt_rand(1,8),mt_rand(1,$img_height/4), $_SESSION['authcode'][$i],imageColorAllocate($aimg,mt_rand(0,100),mt_rand(0,150),mt_rand(0,200)));
		}
		header("Content-type: image/png");    //告诉浏览器，下面的数据是图片
		ImagePng($aimg);                      //生成png格式
		ImageDestroy($aimg);
	}
	static function authcode_im()
	{
		if(empty($_SESSION)) session_start();
		$authnum = '';
		srand((double)microtime()*1000000);
		$_SESSION['authcode']="";

		/* imagick对象 *
		 $Imagick = new Imagick();

		 /* 背景对象 */
		$bg = new ImagickPixel();

		/* Set the pixel color to white */
		$bg->setColor('rgb(235,235,235)');

		/* 画刷 *
		 $ImagickDraw = new ImagickDraw();

		 /* Set font and font size. You can also specify /path/to/font.ttf */
		$ImagickDraw->setFont(LIBPATH.'/../static/fonts/CONSOLA.TTF');
		$ImagickDraw->setFontSize( 24 );
		$ImagickDraw->setFillColor('black');

		//生成数字和字母混合的验证码方法
		$ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
		$list=explode(",",$ychar);
		for($i=0;$i<4;$i++)
		{
			$randnum=rand(0,35);
			$authnum.=$list[$randnum];
		}
		$authnum = strtoupper($authnum);;
		$_SESSION['authcode'] = $authnum;

		/* Create new empty image */
		$Imagick->newImage( 60, 24, $bg );

		/* Write the text on the image */
		$Imagick->annotateImage( $ImagickDraw, 4, 20, 0, $authnum );

		/* 变形 *
		 //$Imagick->swirlImage( 10 );

		 /* 随即线条 */
		/*$ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
		 $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
		 $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
		 $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
		 $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );*/

		/* Draw the ImagickDraw object contents to the image. */
		$Imagick->drawImage( $ImagickDraw );

		/* Give the image a format */
		$Imagick->setImageFormat( 'png' );

		/* Send headers and output the image */
		//header( "Content-Type: image/{$Imagick->getImageFormat()}" );
		echo $Imagick->getImageBlob( );
	}
	/**
	 * 生成验证码，使用TTF字体
	 * @param $font
	 * @param $img_width
	 * @param $img_height
	 * @return unknown_type
	 */
	static function authcode_ttf($font,$img_width=80,$img_height=30)
	{
		if(empty($_SESSION)) session_start();
		session_register("login_check_number");
		$nmsg='';

		srand(microtime() * 100000);
		for($Tmpa=0;$Tmpa<4;$Tmpa++)
		{
			$nmsg.=dechex(rand(0,15));
		}
		$_SESSION['login_check_number'] = strtoupper($nmsg);
		$aimg = imageCreate($img_width,$img_height);       //生成图片
		ImageColorAllocate($aimg, 255,255,255);            //图片底色，ImageColorAllocate第1次定义颜色PHP就认为是底色了

		//下面该生成雪花背景了，其实就是在图片上生成一些符号
		for ($i=1; $i<=128; $i++)
		{
			$randcolor = ImageColorallocate($aimg,rand(50,100),rand(50,100),rand(50,100));
			imagesetpixel($aimg, rand()%70 , rand()%30 , $randcolor);
		}

		//为了区别于背景，这里的颜色不超过200，上面的不小于200
		for ($i=0;$i<strlen($_SESSION['login_check_number']);$i++)
		{
			$x = $i*$img_width/4+mt_rand(1,8);
			$y = $img_height-5;
			$color = imageColorAllocate($aimg,mt_rand(0,100),mt_rand(0,150),mt_rand(0,200));

			$angle = mt_rand(0,30);
			if(rand(0,1)==1) $angle = -$angle;

			$code = $_SESSION['login_check_number'][$i];
			$li_x = mt_rand(1,$img_width);
			$li_y = mt_rand(1,$img_height);
			imageline($aimg, $li_x,$li_y,$li_x+rand(10,$img_width/2),$li_y+rand(10,$img_height/2),$color);
			imagettftext($aimg,18,$angle,$x,$y,$color,$font,$code);

		}
		Header("Content-type: image/png");    //告诉浏览器，下面的数据是图片
		ImagePng($aimg);                      //生成png格式
		ImageDestroy($aimg);
	}
	static function thumb_name($file_name,$insert='thumb')
	{
	    $dirname = dirname($file_name);
		$file_name = basename($file_name);
		$extend =explode("." , $file_name);
		return $dirname.'/'.$extend[0].'_'.$insert.'.'.$extend[count($extend)-1];
	}
    /**
     * 裁切图片，制作头像
     * @param $image  图片相对网站根目录的地址
     * @param $params 参数，高度height=100，宽度width=116，精度qulitity=80，新图片的地址newfile，原图的真实宽度abs_width
     * @param $original_size 原始的尺寸
     * @param $crop_size 裁切的参数，高度,宽度,四点坐标
     * @return true/false
     */
    static function cropImage($image,$params,$original_size,$crop_size)
    {
        $qulitity = isset($params['qulitity'])?$params['qulitity']:100;
        $dst_width = isset($params['width'])?$params['width']:90;
        $dst_height = isset($params['height'])?$params['height']:105;

        $image = WEBPATH.$image;
        if(!file_exists($image))
            return '错误，图片不存在！';

        $image_info = getimagesize($image);

        if($image_info["mime"]=="image/jpeg" || $image_info["mime"]=="image/gif" || $image_info["mime"]=="image/png")
        {
            /**
             * 计算实际裁剪区域，图片是否被缩放，如果不是真实大小，需要计算
             */
            if(isset($params['abs_width']))
            {
                $tmp_rate = $params['abs_width'] / $original['width'];
                $crop_size['left'] = $crop_size['left'] * $tmp_rate;
                $crop_size['top'] = $crop_size['top'] * $tmp_rate;
                $crop_size['width'] = $crop_size['width'] * $tmp_rate;
                $crop_size['height'] = $crop_size['height'] * $tmp_rate;
            }

            //裁剪
            $image_new = imagecreatetruecolor($dst_width, $dst_height);
            switch($image_info["mime"]){
                case "image/jpeg":
                    $bin_ori = imagecreatefromjpeg($image);
                    break;
                case "image/gif":
                    $bin_ori = imagecreatefromgif($image);
                    break;
                case "image/png":
                    $bin_ori = imagecreatefrompng($image);
                    break;
            }

            imagecopyresampled($image_new, $bin_ori, 0, 0, $crop_size['left'], $crop_size['top'], $dst_width, $dst_height, $crop_size['width'], $crop_size['height']);
            $file_new = WEBPATH.$params['newfile'];
            if(!file_exists(dirname($file_new))) mkdir(dirname($file_new),0777,true);
            return imagejpeg($image_new, $file_new , $qulitity);
        }
    }
}