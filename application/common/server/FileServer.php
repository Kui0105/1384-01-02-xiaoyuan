<?php



namespace app\common\server;


use app\common\server\storage\Driver as StorageDriver;
use think\Db;
use think\Exception;
use app\common\model\File;

class FileServer extends ServerBase
{

    /**
     * Notes: 上传图片
     * @param $cate_id
     * @param string $save_dir (保存目录, 不建议修改, 不要超二级目录)
     * @return array
     * @author 张无忌(2021/2/20 9:53)
     */
    public static function image($cate_id, $save_dir='uploads/images')
    {
        try {
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine'  => ConfigServer::get('storage_engine')
            ];

            if (empty($config['engine']['local'])) {
                $config['engine']['local'] = [];
            }

            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            
            // 验证是否是图片文件
            if (! check_is_image($StorageDriver->getFileInfo()['tmp_name'] ?? '')) {
                throw new Exception('不是有效的图像文件');
            }

            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception('图片上传失败' . $StorageDriver->getError());
            }

            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();

            //名字长度太长
            if (strlen($fileInfo['name']) > 128) {
                $file_name = substr($fileInfo['name'], 0, 123);
                $file_end = substr($fileInfo['name'], strlen($fileInfo['name'])-5, strlen($fileInfo['name']));
                $fileInfo['name'] = $file_name.$file_end;
            }

            // 记录图片信息
            $data = [
                'cate_id'     => $cate_id,
                'name'        => $fileInfo['name'],
                'type'        => File::type_image,
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'create_time' => time(),
            ];
            Db::name('file')->insert($data);
            return self::dataSuccess('上传文件成功', $data);

        } catch (\Exception $e) {
            $message = lang($e->getMessage()) ?? $e->getMessage();
            return self::dataError('上传文件失败:' . $message);
        }
    }

    public static function userBase64Image($user_id,$base64_img,$save_dir='uploads/base64_images'){
        if (!file_exists($save_dir)) {
            mkdir($save_dir, 0775, true);
        }
        $name = uniqid() . '.png';
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $base64_img));
        if (!$data) {
            throw new Exception('无效的 base64 图片数据');
        }
        $pdf_file = fopen($save_dir."/".$name, "wb");
        fwrite($pdf_file, $data);
        fclose($pdf_file);
        try {
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine'  => ConfigServer::get('storage_engine')
            ];
            if (empty($config['engine']['local'])) {
                $config['engine']['local'] = [];
            }
            if($config['default'] == 'local'){
                return self::dataSuccess('成功', ['uuuri'=>addImgDomain($save_dir.'/'.$name)]);
            }
            $filePath = ROOT_PATH.'/'.$save_dir."/".$name;
            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFileByReal($filePath);
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception('上传失败' . $StorageDriver->getError());
            }
            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();
            // 信息
            $data = [
                'name'        => $fileInfo['name'],
                'type'        => File::type_other,
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'create_time' => time(),
                'user_id'     => $user_id??0
            ];
            Db::name('user_file')->insert($data);
            $data['uuuri'] = addImgDomain($data['uri']);
            return self::dataSuccess('上传文件成功', $data);
        } catch (\Exception $e) {
            $message = lang($e->getMessage()) ?? $e->getMessage();
            return self::dataError('上传文件失败:' . $message);
        }
    }

    /**
     * Notes: 用户上传图片
     * @param $user_id (用户ID)
     * @param string $save_dir (保存目录, 不建议修改, 不要超二级目录)
     * @return array
     * @author 张无忌(2021/2/20 9:53)
     */
    public static function userFormImage($user_id = 0, $save_dir='uploads/user')
    {
        try {
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine'  => ConfigServer::get('storage_engine')
            ];

            if (empty($config['engine']['local'])) {
                $config['engine']['local'] = [];
            }


            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            $fileName = $StorageDriver->getFileName();
            $fileInfo = $StorageDriver->getFileInfo();
    
            // 验证是否是图片文件
            if (! check_is_image($StorageDriver->getFileInfo()['tmp_name'] ?? '')) {
                throw new Exception('不是有效的图像文件');
            }

            // 校验上传文件后缀
            $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), config('project.file_image'))) {
                throw new Exception("上传图片不允许上传{$ext}文件");
            }

            // 上传文件
            $save_dir = $save_dir . '/' .  date('Ymd');
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception($StorageDriver->getError());
            }

            // 记录图片信息
            $data = [
                'user_id'     => $user_id ? $user_id : 0,
                'name'        => $fileInfo['name'],
                'type'        => File::type_image,
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'create_time' => time(),
            ];
            Db::name('user_file')->insert($data);

            $result['url'] = UrlServer::getFileUrl($data['uri']);
            $result['base_url'] = $data['uri'];
            $result['name'] = $data['name'];

            return self::dataSuccess('上传文件成功', $result);

        } catch (\Exception $e) {
            $message = lang($e->getMessage()) ?? $e->getMessage();
            return self::dataError('上传文件失败:' . $message);
        }
    }

    /**
     * Notes: 用户上传pdf
     * @param $user_id (用户ID)
     * @param string $save_dir (保存目录, 不建议修改, 不要超二级目录)
     * @return array
     * @author 张无忌(2021/2/20 9:53)
     */
    public static function userFormPdf($user_id = 0, $save_dir='uploads/user/pdf')
    {
        try {
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine'  => ConfigServer::get('storage_engine')
            ];

            if (empty($config['engine']['local'])) {
                $config['engine']['local'] = [];
            }


            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            $fileName = $StorageDriver->getFileName();
            $fileInfo = $StorageDriver->getFileInfo();

            // 校验上传文件后缀
            $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), ['pdf'])) {
                throw new Exception("上传图片不允许上传{$ext}文件");
            }

            // 上传文件
            $save_dir = $save_dir . '/' .  date('Ymd');
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception($StorageDriver->getError());
            }

            // 记录图片信息
            $data = [
                'user_id'     => $user_id ? $user_id : 0,
                'name'        => $fileInfo['name'],
                'type'        => File::type_image,
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'create_time' => time(),
            ];
            Db::name('user_file')->insert($data);

            $result['url'] = UrlServer::getFileUrl($data['uri']);
            $result['base_url'] = $data['uri'];
            $result['name'] = $data['name'];

            return self::dataSuccess('上传文件成功', $result);

        } catch (\Exception $e) {
            $message = lang($e->getMessage()) ?? $e->getMessage();
            return self::dataError('上传文件失败:' . $message);
        }
    }

    /**
     * Notes: 上传视频
     * @param string $save_dir (保存目录, 不建议修改, 不要超二级目录)
     * @return array
     * @author 张无忌(2021/2/20 9:53)
     */
    public static function video($save_dir='uploads/video')
    {
        try {
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine'  => ConfigServer::get('storage_engine')
            ];

            if (empty($config['engine']['local'])) {
                $config['engine']['local'] = [];
            }

            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            
            $fileName = $StorageDriver->getFileName();
            $fileInfo = $StorageDriver->getFileInfo();
    
            // 验证是否是视频
            if (! check_is_video($StorageDriver->getFileInfo()['tmp_name'] ?? '')) {
                throw new Exception('不是有效的视频文件');
            }

            // 校验上传文件后缀
            $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), config('project.file_video'))) {
                throw new Exception("上传视频不允许上传{$ext}文件");
            }

            // 上传文件
            $save_dir = $save_dir . '/' .  date('Ymd');
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception($StorageDriver->getError());
            }

            // 记录图片信息
            $data = [
                'name'        => $fileInfo['name'],
                'type'        => File::type_video,
                'domain'      => UrlServer::getFileUrl('/'),
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'create_time' => time(),
            ];
            return self::dataSuccess('上传文件成功', $data);

        } catch (\Exception $e) {
            $message = lang($e->getMessage()) ?? $e->getMessage();
            return self::dataError('上传文件失败:' . $message);
        }
    }

    /**
     * Notes: 用户上传图片
     * @param string $save_dir (保存目录, 不建议修改, 不要超二级目录)
     * @param bool $isLocal (是否存不使用oss 只存本地, 上传退款证书会用到)
     * @return array
     * @author 张无忌(2021/2/20 9:53)
     */
    public static function other($save_dir='uploads/other', $isLocal=false )
    {
        try {
            if ($isLocal == false) {
                $config = [
                    'default' => ConfigServer::get('storage', 'default', 'local'),
                    'engine'  => ConfigServer::get('storage_engine')
                ];
            } else {
                $config = [
                    'default' => 'local',
                    'engine'  => ConfigServer::get('storage_engine')
                ];
            }
            if (empty($config['engine']['local'])) {
                $config['engine']['local'] = [];
            }

            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');

            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception('上传失败' . $StorageDriver->getError());
            }

            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();

            // 信息
            $data = [
                'name'        => $fileInfo['name'],
                'type'        => File::type_other,
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'create_time' => time(),
            ];
            Db::name('file')->insert($data);
            return self::dataSuccess('上传文件成功', $data);

        } catch (\Exception $e) {
            $message = lang($e->getMessage()) ?? $e->getMessage();
            return self::dataError('上传文件失败:' . $message);
        }
    }

    /**
     * Notes: 文件列表
     * @param $pag_no
     * @param $page_size
     * @param int $cate_id
     * @param string $type
     * @author 张无忌(2021/2/20 10:23)
     * @return array
     */
    public static function lists($pag_no, $page_size,  $cate_id = 0, $type = '')
    {
        try {
            $where[] = ['del', '=', 0];
            if ($type) {
                $where[] = ['type', '=', $type];
            }
            if (!empty($cate_id) and $cate_id > 0) {
                $lists = Db::name('file_cate')->where(['del' => 0])->select();
                $lists = !empty($lists) ? $lists : [];
                $childs = self::getChildCid($lists, $cate_id, true);
                array_push($childs, $cate_id);
                $where[] = ['cate_id', 'in', $childs];
            }

            $lists = Db::name('file')
                ->field(['name', 'uri', 'id'])
                ->withAttr('uri', function ($value) {
                    return UrlServer::getFileUrl($value);
                })
                ->where($where)
                ->order(['id' => 'desc'])
                ->paginate([
                    'page'      => $pag_no,
                    'list_rows' => $page_size,
                    'var_page' => 'page'
                ])->toArray();

            return $lists;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Notes: 删除选中的图片
     * @param $ids
     * @author 张无忌(2021/2/20 10:24)
     * @return bool|int|string
     */
    public static function del($ids)
    {
        try {
            return Db::name('file')
                ->where('id', 'in', $ids)
                ->update(['del' => 1]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function videokkk($save_dir = '',$ext = 'mp3,mp4,wav,AVI,mov,rmvb,rm,FLV,3GP'){
        try {
            if (!file_exists($save_dir)) {
                mkdir($save_dir, 0775, true);
            }

            $file = request()->file('file');
            $info = $file->validate(['ext' => $ext])->move($save_dir);

            if(empty($info)){
                throw new Exception($file->getError());
            }

            $result['url'] = $save_dir . '/' . $info->getSaveName();
            $result['name'] = $info->getSaveName();
            $result['save_name'] = $info->getFilename();
            $file_data = $info->getInfo();
            if (isset($file_data['name'])){
                $result['name'] = $file_data['name'];
            }
            return self::dataSuccess('上传文件成功', $result);

        } catch (Exception $e) {
            $messge = $e->getMessage();
            $messge = lang($messge) ?? $messge;
            return self::dataError('上传文件失败:' . $messge);
        }
    }

    public static function videoNew($cate_id, $save_dir='uploads/video')
    {
        try {
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine'  => ConfigServer::get('storage_engine')
            ];

            if (empty($config['engine']['local'])) {
                $config['engine']['local'] = [];
            }

            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
    
    
            // 视频上传路径
            $fileName = $StorageDriver->getFileName();
            // 视频信息
            $fileInfo = $StorageDriver->getFileInfo();
    
            // 验证是否是视频
            if (! check_is_video($StorageDriver->getFileInfo()['tmp_name'] ?? '')) {
                throw new Exception('不是有效的视频文件');
            }

            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception('视频上传失败' . $StorageDriver->getError());
            }
            

            //名字长度太长
            if (strlen($fileInfo['name']) > 128) {
                $file_name = substr($fileInfo['name'], 0, 123);
                $file_end = substr($fileInfo['name'], strlen($fileInfo['name'])-5, strlen($fileInfo['name']));
                $fileInfo['name'] = $file_name.$file_end;
            }

            // 记录视频信息
            $data = [
                'cate_id'     => $cate_id,
                'name'        => $fileInfo['name'],
                'type'        => File::type_video,
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'create_time' => time(),
            ];
            Db::name('file')->insert($data);
            return self::dataSuccess('上传视频成功', $data);

        } catch (\Exception $e) {
            $message = lang($e->getMessage()) ?? $e->getMessage();
            return self::dataError('上传视频失败:' . $message);
        }
    }




    /**
     * @notes 获取后代分类
     * @param $lists
     * @param $cid
     * @param $clear
     * @return array
     * @author 段誉
     * @date 2022/3/22 14:29
     */
    public static function getChildCid($lists, $cid, $clear)
    {
        static $temp = [];
        if($clear) {
            $temp = [];
        }
        foreach($lists as $item) {
            if($item['pid'] == $cid) {
                $temp[] = $item['id'];
                self::getChildCid($lists, $item['id'], false);
            }
        }
        return $temp;
    }
}