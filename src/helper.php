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
use think\Db;

//  遇到/ueditor/xxx 路由转换访问 UeditorController控制器
\think\Route::any('ueditor/[:id]', "\\zs_ueditor\\UeditorController@index");





/**
 * 遍历获取目录下的指定类型的文件
 * @param $path
 * @param array $files
 * @return array
 */
function getfiles($path, $allowFiles, &$files = array())
{
    if (!is_dir($path)) return null;
    if (substr($path, strlen($path) - 1) != '/') $path .= '/';
    $handle = opendir($path);
    while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..') {
            $path2 = $path . $file;
            if (is_dir($path2)) {
                getfiles($path2, $allowFiles, $files);
            } else {
                if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {
                    $files[] = array(
                        'url' => substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                        'mtime' => filemtime($path2)
                    );
                }
            }
        }
    }
    return $files;
}






/**
 * ueditor文件上传方法
 */

 function  ueditor_upload_file_copy($file)
 {
    $rt_arr=get_oss_upimg_clientnum();
    if($rt_arr["sta"]!='1'){
        $rt['sta'] = '0';
        $rt['msg'] = $rt_arr["msg"];
        return $rt;
    }
    $clientnum= $rt_arr["clientnum"];

     $pinfo = pathinfo($file['name']);
     $ftype = $pinfo['extension']?$pinfo['extension']:'jpg';
     $imgname = create_guid() . "." . $ftype;
     $remote_file = 'static/upload/' . $clientnum . '/ueditor/images/' . date('Ym') . '/' . $imgname; //上传文件路径
     //上传到阿里云oss
     $arr = uploadFileToAliOss($file["tmp_name"], $remote_file);
     if ($arr['sta'] != '1') {
         $rt['sta'] = '0';
         $rt['msg'] = $arr['msg'];
         return $rt;
     }
     $rt['sta'] = '1';
     $rt['msg'] = "成功";
     $rt['url'] =  $arr['url'];
     return $rt;
 }





/**
 * ueditor文件上传方法,本地文件上传后在删除
 */

 function  ueditor_upload_file_from_local_path_copy($local_path)
 {
    $rt_arr=get_oss_upimg_clientnum();
    if($rt_arr["sta"]!='1'){
        $rt['sta'] = '0';
        $rt['msg'] = $rt_arr["msg"];
        return $rt;
    }
    $clientnum= $rt_arr["clientnum"];


     $imgname = create_guid() . ".jpg";
     $remote_file = 'static/upload/' . $clientnum . '/ueditor/images/' . date('Ym') . '/' . $imgname; //上传文件路径
     //上传到阿里云oss
     $arr = uploadFileToAliOss($local_path, $remote_file);
     if ($arr['sta'] != '1') {
         $rt['sta'] = '0';
         $rt['msg'] = $arr['msg'];
         return $rt;
     }
     $rt['sta'] = '1';
     $rt['msg'] = "成功";
     $rt['url'] =  $arr['url'];
     return $rt;
 }


 //获取上传到oss的图片的路径的客户的clientnum，如果是平台则默认是plat

 function  get_oss_upimg_clientnum_copy (){
    $basekeynum = session('cn_accountinfo.basekeynum');
    if($basekeynum=='平台'){
        $rt['sta'] = '1';
        $rt['msg'] = "获取客户编号成功！";
        $rt['clientnum'] = "plat";
        return $rt;
    }

    $info = Db::table('plat_client')->where('keynum', $basekeynum)->find();
    if(empty($info)){
       $basekeynum = session('cn_accountinfo.parent_basekeynum');
       $info = Db::table('plat_client')->where('keynum', $basekeynum)->find();
       if(empty($info)){
           $rt['sta'] = '0';
           $rt['msg'] = "获取客户编号失败！";
           $rt['clientnum'] = "";
           return $rt;
       }
    }

    $rt['sta'] = '1';
    $rt['msg'] = "获取客户编号成功！";
    $rt['clientnum'] = $info["clientnum"];
    return $rt;
 }


 
//文件上传到阿里云oss方法，请把这个方法去掉copy,然后拷贝到helper.php。
function uploadFileToAliOss_copy($local_file, $remote_file)
{
    //从数据取出来阿里云oss配置参数
    $info = $rs = Db::table('plat_system_set')->where("id='1'")->find();
    //上传到阿里云oss
    // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
    $accessKeyId     = $info['ali_accesskeyid']; //"LTAIVZPMH2ErRTa7";
    $accessKeySecret = $info['ali_accesskeysecret']; //"此处注意修改";
    // Endpoint以杭州为例，其它Region请按实际情况填写。
    $endpoint = $info['ali_endpoint']; //"http://oss-cn-beijing.aliyuncs.com";
    // 存储空间名称
    $bucket = $info['ali_bucket']; //"kunyuan";
    // 文件名称
    $object = $remote_file; //文件的路径
    // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt
    $filePath = $local_file;
    $flag=true;
    try {
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $ossClient->uploadFile($bucket, $object, $filePath);
    } catch (OssException $e) {
        $msg=$e->getMessage();
        $flag=false;
    }
    //根据flag判断是否成功
    if (!$flag) {
        $rt['sta']="0";
        $rt['msg']="上传到oss失败！详细信息：".$msg;
        $rt['url']="";
        return $rt;
        die;
    }
    //成功返回
    $rt['sta']="1";
    $rt['msg']="上传到oss成功！";
    $rt['url']=$info['ali_bucket_url'] . "/" . $object;
    return $rt;
    die;
}
