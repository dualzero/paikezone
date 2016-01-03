<?php
function is_login(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return true;
    }
}

function check_verify($code, $id = 1){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

function time_format($time = NULL,$format='Y-m-d H:i'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}


/**
 * 获取缩略图
 * @param unknown_type $filename 原图路劲、url
 * @param unknown_type $width 宽度
 * @param unknown_type $height 高
 * @param unknown_type $cut 是否切割 默认不切割
 * @return string
 */
function getThumbImage($filename, $width = 150, $height = 'auto',$cut=true, $replace = false)
{
    define('UPLOAD_URL', '');
    define('UPLOAD_PATH', '');
    $filename = str_ireplace(UPLOAD_URL, '', $filename); //将URL转化为本地地址
    $info = pathinfo($filename);
    $oldFile = $filename;
    $thumbFile = $filename;
    $thumbFile = str_replace('Picture', 'Thumb', $thumbFile);//设置目录
    mkdir(pathinfo($thumbFile)['dirname']);

    $oldFile = str_replace('\\', '/', $oldFile);
    $thumbFile = str_replace('\\', '/', $thumbFile);


    $filename = ltrim($filename, '/');
    $oldFile = ltrim($oldFile, '/');
    $thumbFile = ltrim($thumbFile, '/');
    //原图不存在直接返回
    if (!file_exists(UPLOAD_PATH . $oldFile)) {
        @unlink(UPLOAD_PATH . $thumbFile);
        $info['src'] = $oldFile;
        $info['width'] = intval($width);
        $info['height'] = intval($height);
        return $info;
        //缩图已存在并且 replace替换为false
    } elseif (file_exists(UPLOAD_PATH . $thumbFile) && !$replace) {
        $imageinfo = getimagesize(UPLOAD_PATH . $thumbFile);
        //dump($imageinfo);exit;
        $info['src'] = $thumbFile;
        $info['width'] = intval($imageinfo[0]);
        $info['height'] = intval($imageinfo[1]);
        return $info;
        //执行缩图操作
    } else {
        $oldimageinfo = getimagesize(UPLOAD_PATH . $oldFile);
        $old_image_width = intval($oldimageinfo[0]);
        $old_image_height = intval($oldimageinfo[1]);
        if ($old_image_width <= $width && $old_image_height <= $height) {
            @unlink(UPLOAD_PATH . $thumbFile);
            @copy(UPLOAD_PATH . $oldFile, UPLOAD_PATH . $thumbFile);
            $info['src'] = $thumbFile;
            $info['width'] = $old_image_width;
            $info['height'] = $old_image_height;
            return $info;
        } else {
            //生成缩略图
            // tsload( ADDON_PATH.'/library/Image.class.php' );
            // if($cut){
            //     Image::cut(UPLOAD_PATH.$filename, UPLOAD_PATH.$thumbFile, $width, $height);
            // }else{
            //     Image::thumb(UPLOAD_PATH.$filename, UPLOAD_PATH.$thumbFile, '', $width, $height);
            // }
            //生成缩略图 - 更好的方法
            if ($height == "auto") $height = 0;
            //import('phpthumb.PhpThumbFactory');
            require_once('ThinkPHP\Library\Vendor\phpthumb\PhpThumbFactory.class.php');

            $thumb = PhpThumbFactory::create(UPLOAD_PATH . $filename);
            //dump($thumb);exit;
            if ($cut) {
                $thumb->adaptiveResize($width, $height);
            } else {
                $thumb->resize($width, $height);
            }
            $res = $thumb->save(UPLOAD_PATH . $thumbFile);
            //缩图失败
            if (!$res) {
                $thumbFile = $oldFile;
            }
            $info['width'] = $width;
            $info['height'] = $height;
            $info['src'] = $thumbFile;
            return $info;
        }
    }
}


function get_sc($pic_path, $width = 150, $height = 150, $cut = true, $replace = false){
    $attach = getThumbImage($pic_path, $width, $height, $cut, $replace);
    return $attach['src'];
}

function get_avatar_name(){
    return session('user_auth')['username'] . '_cache';
}

//通过相册的照片数量排序
function sortByCount($a,$b){
    return $a['count'] < $b['count'];
}

//通过相册的照片数量排序
function sortByAlbumCount($a,$b){
    $Album = M('Album');
    $map1 = array('uid'=>$a['uid']);
    $a['count'] = $Album->where($map1)->count();
    $map2 = array('uid'=>$b['uid']);
    $b['count'] = $Album->where($map2)->count();
    return $a['count'] < $b['count'];
}

//旋转、缩放图片
//先进行旋转，然后缩放
// function imgEdit($url, $rotate, $zoom){
//     //读取图片
//     $size = @getimagesize($url);
//     $w = $size[0];
//     $h = $size[1];
//     switch ($size[2]) {
//         case 1:
//             $img = imagecreatefromgif($url);
//             $type = 'imagegif';
//             $ext = 'gif';
//             break;
//         case 2:
//             $img = imagecreatefromjpeg($url);
//             $type = 'imagejpeg';
//             $ext = 'jpg';
//             break;
//         case 3:
//             $img = imagecreatefrompng($url);
//             $type = 'imagepng';
//             $ext = 'png';
//             break;
//     }
//     // 如果旋转了角度
//     if($rotate){
//         $new_img = imagecreatetruecolor($w, $h);
//         $white = imagecolorallocate($new_img, 255, 255, 255);
//         imageCopyResized($new_img, $img, 0, 0, 0, 0, $w, $h, $w, $h);
//         $new_img = imagerotate($new_img, $rotate, $white);
//         header("Content-type:".$size['mime']);
//         $type($new_img, $url);
//         @imagedestroy($new_img);
//     }
//     if($zoom != 1){
        
//     }
    
//     return true;
// }

//图片旋转
function  img_rotate($url, $rotate){
    //读取图片
    $size = @getimagesize($url);
    $w = $size[0];
    $h = $size[1];
    if($size == false) return false;
    switch ($size[2]) {
        case 1:
            $img = imagecreatefromgif($url);
            $type = 'imagegif';
            $ext = 'gif';
            break;
        case 2:
            $img = imagecreatefromjpeg($url);
            $type = 'imagejpeg';
            $ext = 'jpg';
            break;
        case 3:
            $img = imagecreatefrompng($url);
            $type = 'imagepng';
            $ext = 'png';
            break;
    }
    $new_img = imagecreatetruecolor($w, $h);
    $white = imagecolorallocate($new_img, 255, 255, 255);
    imageCopyResized($new_img, $img, 0, 0, 0, 0, $w, $h, $w, $h);
    $new_img = imagerotate($new_img, $rotate, $white);
    header("Content-type:".$size['mime']);
    $type($new_img, $url);
    @imagedestroy($new_img);
    return true;
}

// 图片缩放
function img_zoom($url, $zoom){
    $size = @getimagesize($url);
    $w = $size[0];
    $h = $size[1];
    if($size == false) return false;
    switch ($size[2]) {
        case 1:
            $img = imagecreatefromgif($url);
            $type = 'imagegif';
            $ext = 'gif';
            break;
        case 2:
            $img = imagecreatefromjpeg($url);
            $type = 'imagejpeg';
            $ext = 'jpg';
            break;
        case 3:
            $img = imagecreatefrompng($url);
            $type = 'imagepng';
            $ext = 'png';
            break;
    }
    $new_w = $w * $zoom;
    $new_h = $h * $zoom;
    $new_img = imagecreatetruecolor($new_w, $new_h);
    $white = imagecolorallocate($new_img, 255, 255, 255);
    imagefilledrectangle($new_img, 0, 0, $new_w, $new_h);
    imagecopyresized($new_img, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
    header("Content-type:".$size['mime']);
    $type($new_img, $url);
    @imagedestroy($new_img);
    return true;
}