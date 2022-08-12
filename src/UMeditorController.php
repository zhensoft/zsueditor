<?php
// +----------------------------------------------------------------------
// | 王磊 [ Programming makes me happy ]
// +----------------------------------------------------------------------
// | Copyright (c) 2022 08 12  http://www.wlphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: wl < 613154514@qq.com >
// +----------------------------------------------------------------------
namespace zs_ueditor;



use think\facade\Config;

class UMeditorController
{
    protected  $config = [
        "savePath" => "upload/image/" ,             //存储文件夹
        "maxSize" => 1000 ,                   //允许的文件最大尺寸，单位KB
        "allowFiles" => [
            ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp"
        ]  //允许的文件格式
    ];

    public function __construct(){
        header("Content-Type:text/html;charset=utf-8");
        error_reporting( E_ERROR | E_WARNING );
        $umeditor = Config::get('umeditor'); //如果存在PHP配置则使用PHP配置 否则使用默认配置
        if($umeditor&&isset($ueditor)){
            $this->config = $ueditor;
        }
    }


    /**
     * 精简版图片上传
     */
    public function index(){
        $up = new MUploader("upfile" , $this->config);

        $callback=$_GET['callback'];

        $info = $up->getFileInfo();

        //返回数据
        if($callback) {
            echo '<script>'.$callback.'('.json_encode($info).')</script>';
        } else {
            echo json_encode($info);
        }
    }

}