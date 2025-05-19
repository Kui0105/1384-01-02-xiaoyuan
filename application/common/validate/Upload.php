<?php

namespace app\common\validate;

use think\Validate;

class Upload extends Validate
{

    protected $rule = [
        'file' => 'fileExt:pem,jpg,jpeg,gif,png,bmp,tga,tif,pdf,psd,avi,mp4,mp3,wmv,mpg,mpeg,mov,rm,ram,swf,flv,ico,webp',
    ];

    protected $message = [
        'file.fileExt' => '该文件类型不允许上传',
    ];

}