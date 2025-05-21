<?php



namespace app\api\controller;


use app\common\server\FileServer;

class File extends ApiBase
{
    public $like_not_need_login = ['formImage','test'];
    /**
     * showdoc
     * @catalog 接口/上传文件
     * @title form表单方式上传图片
     * @description 图片上传
     * @method post
     * @url /file/formimage
     * @return param name string 图片名称
     * @return param url string 文件地址
     * @remark
     * @number 1
     * @return {"code":1,"msg":"上传文件成功","data":{"url":"http:\/\/www.ehaiba.com\/uploads\/images\/user\/20200810\/3cb866f6bb30b7239d91582f7d9822d6.png","name":"2.png"},"show":0,"time":"0.283254","debug":{"request":{"get":[],"post":[],"header":{"content-length":"103132","content-type":"multipart\/form-data; boundary=--------------------------206668736604428806173438","connection":"keep-alive","accept-encoding":"gzip, deflate, br","host":"www.ehaiba.com:20002","postman-token":"1f8aa4dd-f53c-4d12-98b4-8d901ac847db","cache-control":"no-cache","accept":"*\/*","user-agent":"PostmanRuntime\/7.26.2"}}}}
     */
    public function formImage()
    {
        $data = FileServer::userFormImage($this->user_id);
        $this->_success($data['msg'], $data['data'], $data['code']);
    }

    public function formPdf()
    {
        $data = FileServer::userFormPdf($this->user_id);
        $this->_success($data['msg'], $data['data'], $data['code']);
    }

    /**
     * 上传 base64 图片
     *
     * @method post
     * @url /file/base64image
     * @param string $base64Image base64 图片数据
     */
    public function base64Image()
    {
        $base64Image = input('post.base64Image'); // 获取 base64 数据
        $data = FileServer::userBase64Image($this->user_id, $base64Image); // 调用文件服务处理 base64 图片上传
        $this->_success($data['msg'], $data['data'], $data['code']);
    }
}