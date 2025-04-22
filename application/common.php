<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Db;
use app\common\server\{UrlServer,ConfigServer};

/**
 * User:  lr
 * Desc:debug专用
 */
function dd()
{
    $parameters = func_get_args();
    if (count($parameters) == 1) {
        $parameters = $parameters[0];
    }
    var_dump($parameters);
    exit;
}

/**
 * User:  lr
 * Desc：生成密码密文
 * @param $plaintext string 明文
 * @param $salt string 密码盐
 * @return string
 */
function create_password($plaintext, $salt)
{
    $salt = md5('y' . $salt . 'x');
    $salt .= '2020';
    return md5($plaintext . $salt);
}

/**
 * User:  lr
 * Desc: 数组成功拼装
 * @param string $msg
 * @param array $data
 * @param int $code
 * @param int $show
 * @return array
 */
function data_success($msg = '', $data = [], $code = 1, $show = 1)
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
        'show' => $show,
    ];
    return $result;
}

/**
 * User:  lr
 * Desc: 组装失败数据
 * @param string $msg
 * @param array $data
 * @param int $code
 * @param int $show
 * @return array
 */
function data_error($msg = '', $data = [], $code = 0, $show = 1)
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
        'show' => $show,
    ];
    return $result;
}

/**
 * User:  lr
 * Desc: 下载文件
 * @param $url 文件url
 * @param $save_dir 保存目录
 * @param $file_name 文件名
 * @return string
 */
function download_file($url, $save_dir, $file_name)
{
    if (!file_exists($save_dir)) {
        mkdir($save_dir, 0775, true);
    }
    $file_src = $save_dir . $file_name;
    file_exists($file_src) && unlink($file_src);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $file = curl_exec($ch);
    curl_close($ch);
    $resource = fopen($file_src, 'a');
    fwrite($resource, $file);
    fclose($resource);
    if (filesize($file_src) == 0) {
        unlink($file_src);
        return '';
    }
    return $file_src;
}


/**
 * 腾讯地图转百度地图
 * @param $lat
 * @param $lng
 * @return float[]
 */
function convert_gcj02_to_bd09($lat, $lng)
{
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng;
    $y = $lat;
    $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
    $lng = $z * cos($theta) + 0.0065;
    $lat = $z * sin($theta) + 0.006;
    return array('lng' => $lng, 'lat' => $lat);
}

/**
 *百度地图坐标距离
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @param int $len_type
 * @param int $decimal
 * @return false|float
 */
function distance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
{
    if (empty($lat1) || empty($lng1) || empty($lat2) || empty($lat2)) {
        return null;
    }
    $EARTH_RADIUS = 6378.137;
    $PI = 3.1415926;
    $radLat1 = $lat1 * $PI / 180.0;
    $radLat2 = $lat2 * $PI / 180.0;
    $a = $radLat1 - $radLat2;
    $b = ($lng1 * $PI / 180.0) - ($lng2 * $PI / 180.0);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * $EARTH_RADIUS;
    $s = round($s * 1000);
    if ($len_type > 1) {
        $s /= 1000;
    }
    return round($s, $decimal);
}


/**
 * User:  lr
 * Desc: 表单多维数据转换
 * 例：
 * 转换前：{"x":0,"a":[1,2,3],"b":[11,22,33],"c":[111,222,3333,444],"d":[1111,2222,3333]}
 * 转换为：[{"a":1,"b":11,"c":111,"d":1111},{"a":2,"b":22,"c":222,"d":2222},{"a":3,"b":33,"c":3333,"d":3333}]
 * @param $arr array 表单二维数组
 * @param $fill boolean fill为false，返回数据长度取最短，反之取最长，空值自动补充
 * @return array
 */
function form_to_linear($arr, $fill = false)
{
    $keys = [];
    $count = $fill ? 0 : PHP_INT_MAX;
    foreach ($arr as $k => $v) {
        if (is_array($v)) {
            $keys[] = $k;
            $count = $fill ? max($count, count($v)) : min($count, count($v));
        }
    }
    if (empty($keys)) {
        return [];
    }
    $data = [];
    for ($i = 0; $i < $count; $i++) {
        foreach ($keys as $v) {
            $data[$i][$v] = isset($arr[$v][$i]) ? $arr[$v][$i] : null;
        }
    }
    return $data;
}

/**
 * 树形结构转线性结构
 * 例：
 * 转换前：
 *[{"n":"a","nn":"aa","sub":[{"n":"b","nn":"bb","sub":[{"n":"c","nn":"cc"}]},{"n":"d","nn":"dd","sub":[{"n":"e","nn":"ee"},{"n":"f","nn":"ff"}]}]}]
 * 转换后：
 * [{"n":"a","nn":"aa","id":1,"pid":0},{"n":"b","nn":"bb","id":2,"pid":1},{"n":"c","nn":"cc","id":3,"pid":1},
 * {"n":"d","nn":"dd","id":4,"pid":1},{"n":"e","nn":"ee","id":5,"pid":3},{"n":"f","nn":"ff","id":6,"pid":3}]
 * @param array $data 数组
 * @param string $sub_key_name 树形结构子数组名称
 * @param string $id_name 自动生成id名称
 * @param string $parent_id_name 自动生成父类id
 * @param int $parent_id 此值请勿给参数
 * @return array
 */
function tree_to_linear($data, $sub_key_name = 'sub', $id_name = 'id', $parent_id_name = 'pid', $parent_id = 0)
{
    $result = [];
    $i = 1;
    foreach ($data as $key => $row) {
        $row[$id_name] = $i++;
        $row[$parent_id_name] = $parent_id;
        $sub = $row[$sub_key_name] ?? null;
        unset($row[$sub_key_name]);
        $result[] = $row;
        if (!empty($sub)) {
            $child_data = tree_to_linear($sub, $sub_key_name, $id_name, $parent_id_name, $row[$id_name]);
            foreach ($child_data as $child_row) {
                $v[$id_name] = $i++;
                $result[] = $child_row;
            }
        }
    }
    return $result;
}

/**
 * 线性结构转换成树形结构
 * 转换前：
 * [{"n":"a","nn":"aa","id":1,"pid":0},{"n":"b","nn":"bb","id":2,"pid":1},{"n":"c","nn":"cc","id":3,"pid":1},
 * {"n":"d","nn":"dd","id":4,"pid":1},{"n":"e","nn":"ee","id":5,"pid":3},{"n":"f","nn":"ff","id":6,"pid":3}]
 * 转换后：
 *[{"n":"a","nn":"aa","sub":[{"n":"b","nn":"bb","sub":[{"n":"c","nn":"cc"}]},{"n":"d","nn":"dd","sub":[{"n":"e","nn":"ee"},{"n":"f","nn":"ff"}]}]}]
 * @param array $data 线行结构数组
 * @param string $sub_key_name 自动生成子数组名
 * @param string $id_name 数组id名
 * @param string $parent_id_name 数组祖先id名
 * @param int $parent_id 此值请勿给参数
 * @return array
 */
function linear_to_tree($data, $sub_key_name = 'sub', $id_name = 'id', $parent_id_name = 'pid', $parent_id = 0)
{
    $tree = [];
    foreach ($data as $row) {
        if ($row[$parent_id_name] == $parent_id) {
            $temp = $row;
            $temp[$sub_key_name] = linear_to_tree($data, $sub_key_name, $id_name, $parent_id_name, $row[$id_name]);
            $tree[] = $temp;
        }
    }
    return $tree;
}

/**
 * 多级线性结构排序
 * 转换前：
 * [{"id":1,"pid":0,"name":"a"},{"id":2,"pid":0,"name":"b"},{"id":3,"pid":1,"name":"c"},
 * {"id":4,"pid":2,"name":"d"},{"id":5,"pid":4,"name":"e"},{"id":6,"pid":5,"name":"f"},
 * {"id":7,"pid":3,"name":"g"}]
 * 转换后：
 * [{"id":1,"pid":0,"name":"a","level":1},{"id":3,"pid":1,"name":"c","level":2},{"id":7,"pid":3,"name":"g","level":3},
 * {"id":2,"pid":0,"name":"b","level":1},{"id":4,"pid":2,"name":"d","level":2},{"id":5,"pid":4,"name":"e","level":3},
 * {"id":6,"pid":5,"name":"f","level":4}]
 * @param array $data 线性结构数组
 * @param string $symbol 名称前面加符号
 * @param string $name 名称
 * @param string $id_name 数组id名
 * @param string $parent_id_name 数组祖先id名
 * @param int $level 此值请勿给参数
 * @param int $parent_id 此值请勿给参数
 * @return array
 */
function multilevel_linear_sort($data, $symbol = '', $name = 'name', $id_name = 'id', $parent_id_name = 'pid', $level = 1, $parent_id = 0)
{
    $result = [];
    $this_symbol = '';
    for ($i = 0; $i < $level; $i++) {
        $this_symbol .= $symbol;
    }
    foreach ($data as $key => $row) {
        if ($row['pid'] == $parent_id) {
            $row['level'] = $level;
            $row[$name] = $this_symbol . $row['name'];
            $result[] = $row;
            $pid = $row['id'];
            unset($data[$key]);
            $child_data = multilevel_linear_sort($data, $symbol, $name, $id_name, $parent_id_name, ($level + 1), $pid);
            foreach ($child_data as $child_row) {
                $result[] = $child_row;
            }
        }
    }
    return $result;
}


/**
 * User:  cjh
 * Desc: 返回是否有下一页
 * @param $count 总记录数
 * @param $page  当前页码
 * @param $size  每页记录数
 * @return int
 */
function is_more($count, $page, $size)
{

    $more = 0;

    $last_page = ceil($count / $size);      //总页数、也是最后一页

    if ($last_page && $last_page > $page) {
        $more = 1;
    }
    return $more;
}

/**
 * User:  mjf
 * Desc: 用时间生成订单编号
 * @param $table
 * @param $field
 * @param string $prefix
 * @param int $rand_suffix_length
 * @param array $pool
 * @return string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function createSn($table, $field, $prefix = '', $rand_suffix_length = 4, $pool = [])
{
    $suffix = '';
    for ($i = 0; $i < $rand_suffix_length; $i++) {
        if (empty($pool)) {
            $suffix .= rand(0, 9);
        } else {
            $suffix .= $pool[array_rand($pool)];
        }
    }
    $sn = $prefix . date('YmdHis') . $suffix;
    if (Db::name($table)->where($field, $sn)->find()) {
        return createSn($table, $field, $prefix, $rand_suffix_length, $pool);
    }
    return $sn;
}

/*
 * 生成优惠券码 todo 排除1、0、I、O相似的数字和字母
 */
function create_coupon_code()
{

    $letter_all = range('A', 'Z');
    shuffle($letter_all);
    //排除I、O字母
    $letter_array = array_diff($letter_all, ['I', 'O']);
    //随机获取四位字母
    $letter = array_rand(array_flip($letter_array), 4);
    //排除1、0
    $num_array = range('2', '9');
    shuffle($num_array);
    //获取随机六位数字
    $num = array_rand(array_flip($num_array), 6);
    $code = implode('', array_merge($letter, $num));
    do {
        $exist_code = Db::name('coupon_list')->where(['del' => 0, 'coupon_code' => $code])->find();

    } while ($exist_code);
    return $code;

}

/**
 * 生成会员码
 * @return 会员码
 */
function create_user_sn($prefix = '', $length = 8)
{
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= mt_rand(0, 9);
    }
    $sn = $prefix . $rand_str;
    if (Db::name('user')->where(['sn' => $sn])->find()) {
        return create_user_sn($prefix, $length);
    }
    return $sn;
}

/*
 * 生成海报自动适应标题
 */
function auto_adapt($size, $angle = 0, $fontfile, $string, $width, $height, $bg_height)
{
    $content = "";
    // 将字符串拆分成一个个单字 保存到数组 letter 中
    for ($i = 0; $i < mb_strlen($string); $i++) {
        $letters[] = mb_substr($string, $i, 1);
    }

    foreach ($letters as $letter) {
        $str = $content . " " . $letter;
        $box = imagettfbbox($size, $angle, $fontfile, $str);

        $total_height = $box[1] + $height;
        if ($bg_height[1] - $total_height < $size) {
            break;
        }
        //右下角X位置,判断拼接后的字符串是否超过预设的宽度
        if (($box[2] > $width) && ($content !== "")) {
            if ($bg_height[1] - $total_height < $size * 2) {
                break;
            }
            $content .= "\n";
        }
        $content .= $letter;
    }
    return $content;
}

/**
 * 将图片切成圆角
 */
function rounded_corner($src_img)
{
    $w = imagesx($src_img);//微信头像宽度 正方形的
    $h = imagesy($src_img);//微信头像宽度 正方形的
    $w = min($w, $h);
    $h = $w;
    $img = imagecreatetruecolor($w, $h);
    //这一句一定要有
    imagesavealpha($img, true);
    //拾取一个完全透明的颜色,最后一个参数127为全透明
    $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
    imagefill($img, 0, 0, $bg);
    $r = $w / 2; //圆半径
//    $y_x = $r; //圆心X坐标
//    $y_y = $r; //圆心Y坐标
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $rgbColor = imagecolorat($src_img, $x, $y);
            if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                imagesetpixel($img, $x, $y, $rgbColor);
            }
        }
    }
    unset($src_img);
    return $img;
}

/**
 * note 生成验证码
 * author  cjh 2020/11/17 17:24
 * @param int $length 验证码长度
 * @return string
 */
function create_sms_code($length = 4)
{
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= rand(0, 9);
    }
    return $code;
}


function real_path()
{
    if (substr(strtolower(PHP_OS), 0, 3) == 'win') {
        $ini = ini_get_all();
        $path = $ini['extension_dir']['local_value'];
        $php_path = str_replace('\\', '/', $path);
        $php_path = str_replace(array('/ext/', '/ext'), array('/', '/'), $php_path);
        $real_path = $php_path . 'php.exe';
    } else {
        $real_path = PHP_BINDIR . '/php';
    }
    if (strpos($real_path, 'ephp.exe') !== FALSE) {
        $real_path = str_replace('ephp.exe', 'php.exe', $real_path);
    }
    return $real_path;
}

/**
 * 图片加上域名
 */
function addImgDomain($x)
{
    if(is_array($x)) {
        $newX = [];
        foreach($x as $v) {
            $newX[] = trim(UrlServer::getFileUrl($v));
        }
        return $newX;
    }
    return  trim(UrlServer::getFileUrl($x));
}

/**
 * 图片去除域名
 */
function clearDomain($x)
{
    if(is_array($x)) {
        $newX = [];
        foreach($x as $v) {
            $newX[] = trim(UrlServer::setFileUrl($v));
        }
        return $newX;
    }
    return  trim(UrlServer::setFileUrl($x));
}

//生成用户邀请码
function generate_invite_code()
{

    $letter_all = range('A', 'Z');
    shuffle($letter_all);
    //排除I、O字母
    $letter_array = array_diff($letter_all, ['I', 'O', 'D']);
    //排除1、0
    $num_array = range('2', '9');
    shuffle($num_array);

    $pattern = array_merge($num_array, $letter_array, $num_array);
    shuffle($pattern);
    $pattern = array_values($pattern);

    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $pattern[mt_rand(0, count($pattern) - 1)];
    }

    $code = strtoupper($code);
    $check = Db::name('user')->where('distribution_code', $code)->find();
    if ($check) {
        return generate_invite_code();
    }
    return $code;
}


/**
 * 是否在cli模式
 */
if (!function_exists('is_cli')) {
    function is_cli()
    {
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }
}
/**
 * Notes:判断文件是否存在（远程和本地文件）
 * @param $file string 完整的文件链接
 * @return bool
 */
function check_file_exists($file)
{
    //远程文件
    if ('http' == strtolower(substr($file, 0, 4))) {

        $header = get_headers($file, true);

        return isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'));

    } else {

        return file_exists($file);
    }

}

/**
 * Notes:生成一个范围内的随机浮点数
 * @param int $min 最小值
 * @param int $max 最大值
 * @return float|int 返回随机数
 */
function random_float($min = 0, $max = 1)
{
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

function is_mobile()
{
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'textml') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'textml')))) {
            return true;
        }
    }
    return false;
}

/**
 * Notes:去掉名称中的表情
 * @param $str
 * @return string|string[]|null
 * @author: cjhao 2021/3/29 15:56
 */
function filterEmoji($str)
{
    $str = preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);
    return $str;
}


/**
 * Notes: 是否为https
 * @author 段誉(2021/5/8 14:45)
 * @return bool
 */
function is_https()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }
    return false;
}


/**
 * Notes: 获取文件扩展名
 * @param $file
 * @author 段誉(2021/7/7 18:03)
 * @return mixed
 */
if (!function_exists('get_extension')) {
    function get_extension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
}


/**
 * Notes: 遍历指定目录下的文件(目标目录,排除文件)
 * @param $dir 目标文件
 * @param string $exclude_file 要排除的文件
 * @param string $target_suffix 指定后缀
 * @author 段誉(2021/7/7 18:04)
 * @return array|bool
 */

if (!function_exists('get_scandir')) {
    function get_scandir($dir, $exclude_file = '', $target_suffix = '')
    {
        if(!file_exists($dir)) {
            return [];
        }

        if (empty(trim($dir))) {
            return false;
        }

        $files = scandir($dir);
        $res = [];
        foreach ($files as $item) {
            if ($item == "." || $item == ".." || $item == $exclude_file) {
                continue;
            }
            if (!empty($target_suffix)) {
                if (get_extension($item) == $target_suffix) {
                    $res[] = $item;
                }
            } else {
                $res[] = $item;
            }
        }

        if (empty($item)) {
            return false;
        }
        return $res;
    }
}



/**
 * Notes: 解压压缩包
 * @param $file 压缩包路径
 * @param $save_dir 保存路径
 * @author 段誉(2021/7/7 18:11)
 * @return bool
 */
if (!function_exists('unzip')) {
    function unzip($file, $save_dir)
    {
        if (!file_exists($file)) {
            return false;
        }
        $zip = new \ZipArchive();
        if ($zip->open($file) !== TRUE) {//中文文件名要使用ANSI编码的文件格式
            return false;
        }
        $zip->extractTo($save_dir);
        $zip->close();
        return true;
    }
}



/**
 * Notes: 删除目标目录
 * @param $path
 * @param $delDir
 * @author 段誉(2021/7/7 18:19)
 * @return bool
 */
if (!function_exists('del_target_dir')) {
    function del_target_dir($path, $delDir)
    {
        //没找到，不处理
        if (!file_exists($path)) {
            return false;
        }

        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$path/$item")) {
                        del_target_dir("$path/$item", $delDir);
                    } else {
                        unlink("$path/$item");
                    }
                }
            }
            closedir($handle);
            if ($delDir) {
                return rmdir($path);
            }
        } else {
            if (file_exists($path)) {
                return unlink($path);
            }
            return false;
        }
    }
}

/**
 * Notes: 获取本地版本数据
 * @return mixed
 * @author 段誉(2021/7/7 18:18)
 */

if (!function_exists('local_version')) {
    function local_version()
    {
        if(!file_exists('./upgrade/')) {
            // 若文件夹不存在，先创建文件夹
            mkdir('./upgrade/');
        }
        if(!file_exists('./upgrade/version.json')) {
            // 获取本地版本号
            $version = config('project.version');
            $data = ['version' => $version];
            $src = './upgrade/version.json';
            // 新建文件
            file_put_contents($src, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        $json_string = file_get_contents('./upgrade/version.json');
        // 用参数true把JSON字符串强制转成PHP数组
        $data = json_decode($json_string, true);
        return $data;
    }
}


/**
 * Notes: 获取ip
 * @author 段誉(2021/7/9 10:19)
 * @return array|false|mixed|string
 */
if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }
}


/**
 * @notes 字符串超出指定数量后用..省略后面的字符
 * @param $str
 * @param $len
 * @return mixed|string
 * @author 段誉
 * @date 2021/8/10 18:03
 */
if (!function_exists('omit_str')) {
    function omit_str($str, $len) {
        if (mb_strlen($str) > $len ) {
            $str = mb_substr($str, 0, $len) . '..';
        }
        return $str;
    }
}

/**
 * @notes 生成核销员码
 * @param $table
 * @param string $field
 * @param int $length
 * @param string $prefix
 * @return string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @author ljj
 * @date 2021/8/16 4:29 下午
 */
function create_verifier_sn($table, $field = 'sn', $length = 8, $prefix = '')
{
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= mt_rand(0, 9);
    }
    $sn = $prefix . $rand_str;
    if (Db::name($table)->where([$field => $sn])->find()) {
        return create_verifier_sn($table, $field = 'sn', $length = 8, $prefix = '');
    }
    return $sn;
}


/**
 * 百度地图BD09坐标---->中国正常GCJ02坐标
 * 腾讯地图用的也是GCJ02坐标
 * @param double $lat 纬度
 * @param double $lng 经度
 * @return array();
 */
function Convert_BD09_To_GCJ02($lat, $lng)
{
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng - 0.0065;
    $y = $lat - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $lng = $z * cos($theta);
    $lat = $z * sin($theta);
    return array('lng' => $lng, 'lat' => $lat);
}


/**
 * @notes 发起一个post请求
 * @param $url
 * @param array $data
 * @return bool|string
 * @author 张无忌
 * @date 2021/9/13 14:39
 */
function curl_post($url, $data=[]) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    return curl_exec($ch);
}

/**
 * @notes 获取服务端ip
 * @return array|false|mixed|string
 * @author 段誉
 * @date 2021/10/9 15:29
 */
function get_server_ip()
{
    if (!isset($_SERVER)) {
        return getenv('SERVER_ADDR');
    }

    if($_SERVER['SERVER_ADDR']) {
        return $_SERVER['SERVER_ADDR'];
    }

    return $_SERVER['LOCAL_ADDR'];
}


/**
 * @notes 发起一个get请求
 * @param $url
 * @return bool|string|void
 * @author heshihu
 * @date 2021/9/17 14:18
 */
function curl_get($url){

    $header = array(
        'Accept: application/json',
    );
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    // 超时设置,以秒为单位
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);

    // 超时设置，以毫秒为单位
    curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500000);

    // 设置请求头
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //执行命令
    $data = curl_exec($curl);

    // 显示错误信息
    if (curl_error($curl)) {
        print "Error: " . curl_error($curl);
    } else {
        return $data;
        curl_close($curl);
    }
}


/**
 * @notes 获取两个时间戳区间
 * @param $start_date
 * @param $end_date
 * @return array
 * @author 段誉
 * @date 2022/4/27 10:53
 */
function get_date_range($start_date, $end_date)
{
    $start_date = $start_date ?: time();
    $end_date = $end_date ?: time();

    if ($start_date > $end_date) {
        list($start_date, $end_date) = [$end_date, $start_date];
    }

    // 计算时间差
    $diff = get_date_diff(date('Y-m-d', $start_date), date('Y-m-d', $end_date));

    $date = [];
    $day_extend = [];
    $type = 'day';
    if ($diff['year'] > 0) {
        $type = 'year';
        // 两个时间相差年份,取开始至结束间的年份
        $date[] = date('Y', $start_date);
        while (date('Y', $start_date = strtotime('+1 year', $start_date)) <= date('Y', $end_date)) {
            $date[] = date('Y', $start_date);
        }
    } elseif ($diff['month'] > 0) {
        $type = 'month';
        // 两个时间相差月份，取开始至结束的月份
        $date[] = date('Y-m', $start_date);
        while (($start_date = strtotime('+1 month', $start_date)) <= $end_date) {
            $date[] = date('Y-m', $start_date);
        }
        $date[] = date('Y-m', $end_date);
    } else {
        // 两个时间相差天数,取得开始至结束天数,以年月日格式
        $days = ($end_date - $start_date) / 86400 + 1;
        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y-m-d', $start_date + (86400 * $i));
            $day_extend[] = date('m-d', $start_date + (86400 * $i));
        }
    }

    // 获取月份时可能会有重复
    $date = array_unique($date);

    return ['type' => $type, 'data' => $date, 'day_extend' => $day_extend];
}



/**
 * @notes 获取两个日期的时间差
 * @param $start
 * @param $end
 * @return array
 * @author 段誉
 * @date 2022/4/27 11:04
 */
function get_date_diff($start, $end)
{
    $start = $start ?: date('Y-m-d', time());
    $end = $end ?: date('Y-m-d', time());

    if (strtotime($start) > strtotime($end)) {
        list($start, $end) = [$end, $start];
    }

    // 分别取得年月日
    list($Y1, $m1, $d1) = explode('-', $start);
    list($Y2, $m2, $d2) = explode('-', $end);
    $Y = $Y2 - $Y1; // 年
    $m = $m2 - $m1; // 月
    $d = $d2 - $d1; // 日

    if ($d < 0) {
        $d += (int)date('t', strtotime("-1 month $end"));
        $m--;
    }

    if ($m < 0) {
        $m += 12;
        $Y--;
    }

    return array('year' => $Y, 'month' => $m, 'day' => $d);
}

function check_is_image($image) : bool
{
    
    try {
        if (function_exists('exif_imagetype')) {
            $ImageType =  exif_imagetype($image);
        } else {
            $info = getimagesize($image);
            $ImageType = $info ? $info[2] : false;
        }
    } catch (\Exception $e) {
        return false;
    }
    
    return in_array($ImageType, [1, 2, 3, 6]);
}

function check_is_video($video) : bool
{
    $type = mime_content_type($video);
    
    return strpos($type, 'video') !== false;
}

function getCTime($str)
{
    return date('Y-m-d H:i:s',$str);
}

function abIntval($num)
{
    return abs(intval($num));
}

//获取当前年龄
function now_age($birthdayTimestamp,$currentTimestamp,$str = true){
    // 创建生日的DateTime对象
    $birthdayDate = new DateTime();
    $birthdayDate->setTimestamp($birthdayTimestamp);
    // 创建当前日期的DateTime对象
    $currentDate = new DateTime();
    $currentDate->setTimestamp($currentTimestamp);
    // 计算年龄
    $ageInterval = $currentDate->diff($birthdayDate);
    $age = $ageInterval->y;
    $months = $ageInterval->m;
    $days = $ageInterval->d;
    // 输出年龄
    if($str){
        return $age . "岁" . $months . "月" . $days . "天";
    }
    return ['year'=>$age,'month'=>$months,'day'=>$days];
}

//获取体重中文
function bmi_str($bmi){
    if($bmi<=18.4){
        return '消瘦';
    }else if($bmi>=28){
        return '肥胖';
    }else if($bmi>18.4 && $bmi <=23.9){
        return '正常';
    }else{
        return '超重';
    }
}

//获取身高中文
function height_str($height,$arr){
    $key = false;
    foreach ($arr as $k => $v) {
        if($height <= $v['height']){
            $key = $k;
            break;
        }
    }
    if($key === false){
        return '超高';
    }
    $new_arr = [
        0 => '矮小',
        1 => '偏矮',
        2 => '正常',
        3 => '正常',
        4 => '正常',
        5 => '偏高',
        6 => '超高',
    ];
    return $new_arr[$key];
}

function level_to_num($level)
{
    $level_arr = [
        1   => 3,
        2   => 10,
        3   => 25,
        4   => 50,
        5   => 75,
        6   => 90,
        7   => 97,
    ];
    return $level_arr[$level];
}

function spleen_ques()
{
    return [
        [
           'id'     => 1,
           'ques'   => '不爱吃饭',
           'answer' => ''
        ], [
            'id'    => 2,
            'ques'  => '挑食',
            'answer'=> ''
        ], [
            'id'    => 3,
            'ques'  => '积食',
            'answer'=> ''
        ],[
            'id'    => 4,
            'ques'  => '要追着喂饭',
            'answer'=> ''
        ],[
            'id'    => 5,
            'ques'  => '不喜运动',
            'answer'=> ''
        ],[
            'id'    => 6,
            'ques'  => '小肚子鼓鼓易胀气',
            'answer'=> ''
        ],[
            'id'    => 7,
            'ques'  => '便秘',
            'answer'=> ''
        ],[
            'id'    => 8,
            'ques'  => '腹泻',
            'answer'=> ''
        ], [
            'id'    => 9,
            'ques'  => '手指长倒刺',
            'answer'=> ''
        ], [
            'id'    => 10,
            'ques'  => '指甲有白点',
            'answer'=> ''
        ],[
            'id'    => 11,
            'ques'  => '易出现打嗝',
            'answer'=> ''
        ],[
            'id'    => 12,
            'ques'  => '流口水',
            'answer'=> ''
        ],[
            'id'    => 13,
            'ques'  => '舌有齿痕',
            'answer'=> ''
        ],[
            'id'    => 14,
            'ques'  => '舌苔发黄',
            'answer'=> ''
        ], [
            'id'    => 15,
            'ques'  => '舌苔发白',
            'answer'=> ''
        ], [
            'id'    => 16,
            'ques'  => '嘴巴有口气',
            'answer'=> ''
        ],[
            'id'    => 17,
            'ques'  => '睡觉露眼睛',
            'answer'=> ''
        ],[
            'id'    => 18,
            'ques'  => '面色发黄',
            'answer'=> ''
        ],[
            'id'    => 19,
            'ques'  => '四肢无力',
            'answer'=> ''
        ],[
            'id'    => 20,
            'ques'  => '易长湿疹',
            'answer'=> ''
        ],[
            'id'    => 21,
            'ques'  => '易感冒',
            'answer'=> ''
        ], [
            'id'    => 22,
            'ques'  => '易鼻塞流鼻水',
            'answer'=> ''
        ], [
            'id'    => 23,
            'ques'  => '咳嗽缠绵不愈',
            'answer'=> ''
        ],[
            'id'    => 24,
            'ques'  => '易气喘或嗓子有痰',
            'answer'=> ''
        ],[
            'id'    => 25,
            'ques'  => '盗汗',
            'answer'=> ''
        ],[
            'id'    => 26,
            'ques'  => '长高慢',
            'answer'=> ''
        ],[
            'id'    => 27,
            'ques'  => '"发育慢(五迟)---立、行、齿、发、语"',
            'answer'=> ''
        ], [
            'id'    => 28,
            'ques'  => '注意力不集中',
            'answer'=> ''
        ],[
            'id'    => 29,
            'ques'  => '喜欢趴睡',
            'answer'=> ''
        ],[
            'id'    => 30,
            'ques'  => '精神不振',
            'answer'=> ''
        ],[
            'id'    => 31,
            'ques'  => '手脚易冷',
            'answer'=> ''
        ],[
            'id'    => 32,
            'ques'  => '体型偏瘦小',
            'answer'=> ''
        ],[
            'id'    => 33,
            'ques'  => '易发高烧',
            'answer'=> ''
        ], [
            'id'    => 34,
            'ques'  => '经常不停哭闹',
            'answer'=> ''
        ], [
            'id'    => 35,
            'ques'  => '有抽筋或惊厥史',
            'answer'=> ''
        ],[
            'id'    => 36,
            'ques'  => '易喜易怒',
            'answer'=> ''
        ],[
            'id'    => 37,
            'ques'  => '易口舌生疮',
            'answer'=> ''
        ],[
            'id'    => 38,
            'ques'  => '兴奋难入眠',
            'answer'=> ''
        ],[
            'id'    => 39,
            'ques'  => '容易上火',
            'answer'=> ''
        ],
    ];
}

function weight_arr($type){
    if($type == 1){
        return [
            ['age' => '6.0', 'min' => '16.4-17.6', 'max' => '≥17.7'],
            ['age' => '6.5', 'min' => '16.7-18.0', 'max' => '≥18.1'],
            ['age' => '7.0', 'min' => '17-18.6', 'max' => '≥18.7'],
            ['age' => '7.5', 'min' => '17.4-19.1', 'max' => '≥19.2'],
            ['age' => '8.0', 'min' => '17.8-19.6', 'max' => '≥19.7'],
            ['age' => '8.5', 'min' => '18.1-20.2', 'max' => '≥20.3'],
            ['age' => '9.0', 'min' => '18.5-20.7', 'max' => '≥20.8'],
            ['age' => '9.5', 'min' => '18.9-21.3', 'max' => '≥21.4'],
            ['age' => '10.0', 'min' => '19.2-21.8', 'max' => '≥21.9'],
            ['age' => '10.5', 'min' => '19.6-22.4', 'max' => '≥22.5'],
            ['age' => '11.0', 'min' => '19.9-22.9', 'max' => '≥23.0'],
            ['age' => '11.5', 'min' => '20.3-23.5', 'max' => '≥23.6'],
            ['age' => '12.0', 'min' => '20.7-24.0', 'max' => '≥24.1'],
            ['age' => '12.5', 'min' => '21.0-24.6', 'max' => '≥24.7'],
            ['age' => '13.0', 'min' => '21.4-25.1', 'max' => '≥25.2'],
            ['age' => '13.5', 'min' => '21.9-25.6', 'max' => '≥25.7'],
            ['age' => '14.0', 'min' => '22.3-26.0', 'max' => '≥26.1'],
            ['age' => '14.5', 'min' => '22.6-26.3', 'max' => '≥26.4'],
            ['age' => '15.0', 'min' => '22.9-26.5', 'max' => '≥26.6'],
            ['age' => '15.5', 'min' => '23.1-26.8', 'max' => '≥26.9'],
            ['age' => '16.0', 'min' => '23.3-27.0', 'max' => '≥27.1'],
            ['age' => '16.5', 'min' => '23.5-27.3', 'max' => '≥27.4'],
            ['age' => '17.0', 'min' => '23.7-27.5', 'max' => '≥27.6'],
            ['age' => '17.5', 'min' => '23.8-27.7', 'max' => '≥27.8'],
            ['age' => '18.0', 'min' => '24.0-27.9', 'max' => '≥28.0'],
        ];
    }else{
        return [
            ['age' => '6.0', 'min' => '16.2-17.4', 'max' => '≥17.5'],
            ['age' => '6.5', 'min' => '16.5-17.9', 'max' => '≥18.0'],
            ['age' => '7.0', 'min' => '16.8-18.4', 'max' => '≥18.5'],
            ['age' => '7.5', 'min' => '17.2-18.9', 'max' => '≥19.0'],
            ['age' => '8.0', 'min' => '17.6-19.3', 'max' => '≥19.4'],
            ['age' => '8.5', 'min' => '18.1-19.8', 'max' => '≥19.9'],
            ['age' => '9.0', 'min' => '18.5-20.3', 'max' => '≥20.4'],
            ['age' => '9.5', 'min' => '19.0-20.9', 'max' => '≥21.0'],
            ['age' => '10.0', 'min' => '19.5-21.4', 'max' => '≥21.5'],
            ['age' => '10.5', 'min' => '20.0-22.0', 'max' => '≥22.1'],
            ['age' => '11.0', 'min' => '20.5-22.6', 'max' => '≥22.7'],
            ['age' => '11.5', 'min' => '21.1-23.2', 'max' => '≥23.3'],
            ['age' => '12.0', 'min' => '21.5-23.8', 'max' => '≥23.9'],
            ['age' => '12.5', 'min' => '21.9-24.4', 'max' => '≥24.5'],
            ['age' => '13.0', 'min' => '22.2-24.9', 'max' => '≥25.0'],
            ['age' => '13.5', 'min' => '22.6-25.5', 'max' => '≥25.6'],
            ['age' => '14.0', 'min' => '22.8-25.8', 'max' => '≥25.9'],
            ['age' => '14.5', 'min' => '23.0-26.2', 'max' => '≥26.3'],
            ['age' => '15.0', 'min' => '23.2-26.5', 'max' => '≥26.6'],
            ['age' => '15.5', 'min' => '23.4-26.8', 'max' => '≥26.9'],
            ['age' => '16.0', 'min' => '23.6-27.0', 'max' => '≥27.1'],
            ['age' => '16.5', 'min' => '23.7-27.3', 'max' => '≥27.4'],
            ['age' => '17.0', 'min' => '23.8-27.5', 'max' => '≥27.6'],
            ['age' => '17.5', 'min' => '23.9-27.7', 'max' => '≥27.8'],
            ['age' => '18.0', 'min' => '24.0-27.9', 'max' => '≥28.0'],
        ];
    }
}

function spleen_key($num)
{
    if($num<4){
        $key =  0;
    }else if($num >=5 && $num < 9){
        $key =  1;
    }else if($num >=10 && $num < 19){
        $key =  2;
    }else if($num >=20 && $num < 29){
        $key =  3;
    }else if($num >=30){
        $key =  4;
    }
    return $key;
}

function sleep_ques()
{
    return [
        [
            'id'    => 1,
            'ques'  => '大多数情况，每晚孩子睡眠时间有多少小时',
            'answer'=> [
                [
                    'name'  => '9-11个小时',
                    'num'   => 1
                ],[
                    'name'  => '8-9个小时',
                    'num'   => 2
                ],[
                    'name'  => '7-8个小时',
                    'num'   => 3
                ],[
                    'name'  => '5-7个小时',
                    'num'   => 4
                ],[
                    'name'  => '小于5个小时',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 2,
            'ques'  => '通常您的孩子上床睡觉后多长时间才能睡着',
            'answer'=> [
                [
                    'name'  => '小于15分钟',
                    'num'   => 1
                ],[
                    'name'  => '15-30分钟',
                    'num'   => 2
                ],[
                    'name'  => '30-45分钟',
                    'num'   => 3
                ],[
                    'name'  => '45-50分钟',
                    'num'   => 4
                ],[
                    'name'  => '大于60分钟',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 3,
            'ques'  => '孩子不愿上床睡觉',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 4,
            'ques'  => '晚上孩子入睡困难',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 5,
            'ques'  => '孩子入睡会感到焦虑或恐惧',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 6,
            'ques'  => '孩子入睡时会惊吓或抽动',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 7,
            'ques'  => '孩子入睡时会出现重复动作，例如频繁摇头或撞击',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 8,
            'ques'  => '孩子入睡时会经历鲜明的梦境场景',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 9,
            'ques'  => '孩子睡觉时会过度出汗',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 10,
            'ques'  => '每晚上孩子会醒来两次以上',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 11,
            'ques'  => '晚上醒后，孩子很难再入睡',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 12,
            'ques'  => '睡着后，孩子频繁出现腿部抽动或痉挛，或者在深夜的时候经常改变睡姿或者踢被子',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 13,
            'ques'  => '深夜孩子会出现会呼吸困难',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 14,
            'ques'  => '孩子睡着的时候会上气不接下气或呼吸停止',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 15,
            'ques'  => '孩子打鼾',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 16,
            'ques'  => '孩子夜间过度出汗',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 17,
            'ques'  => '您发现孩子会梦游',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 18,
            'ques'  => '您发现孩子说梦话',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 19,
            'ques'  => '孩子睡觉时会磨牙',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 20,
            'ques'  => '孩子会大叫而惊醒或者迷糊中您不能叫醒他/她，但是事后不能记起',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 21,
            'ques'  => '孩子夜里会尖叫醒来或迷迷糊糊，似乎您帮不到他/她，但第二天早上孩子不记得',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 22,
            'ques'  => '孩子早上经常很难醒来',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 23,
            'ques'  => '孩子早上醒来后感到疲乏',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 24,
            'ques'  => '第二天醒来后孩子感到身体不能动',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 25,
            'ques'  => '孩子会出现白天嗜睡',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ],[
            'id'    => 26,
            'ques'  => '孩子会不分场合能突然睡着',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 1
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 2
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 4
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 5
                ]
            ]
        ]
    ];
}

/**
 * * 注：入睡困难和睡眠维持障碍（将条目1，2，3，4，5，10，11得分求和）
 * * 睡眠呼吸障碍（将条目13，14，15得分求和）
 * * 觉醒障碍（将条目17，20，21得分求和）
 * * 睡眠觉醒转换障碍（将条目6，7，8，12，18，19得分求和）
 * * 过度嗜睡（将22，23，24，25，26得分求和）
 * * 夜间多汗（将9，16得分求和）
 * * 总分（将所有6类别的总分求和）
 * */
function sleep_check_com($arr){
    $res = [
        [
            'name'      => '入睡困难和睡眠维持障碍',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '睡眠呼吸障碍',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '觉醒障碍',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '睡眠觉醒转换障碍',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '过度嗜睡',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '夜间多汗',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '总分',
            'num'       => 0,
            'result'    => '',
        ]
    ];
    foreach ($arr as $k => $v){
        if(in_array($k,[1,2,3,4,5,10,11])){
            $res[0]['num'] += $v;
        }else if(in_array($k,[13,14,15])){
            $res[1]['num'] += $v;
        }else if(in_array($k,[17,20,21])){
            $res[2]['num'] += $v;
        }else if(in_array($k,[6,7,8,12,18,19])){
            $res[3]['num'] += $v;
        }else if(in_array($k,[22,23,24,25,26])){
            $res[4]['num'] += $v;
        }else if(in_array($k,[9,16])){
            $res[5]['num'] += $v;
        }
        $res[6]['num'] += $v;
    }
    foreach ($res as $ke => $re){
        if($ke == 6) {
            if($re['num'] > 39){
                $key_name = 'result_total_up';
            }else{
                $key_name = 'result_total_down';
            }
        }else{
            if($re['num'] > 39){
                $key_name = 'result_'.$ke.'_up';
            }else{
                $key_name = 'result_'.$ke.'_down';
            }
        }
        $res[$ke]['result'] = ConfigServer::get('baby_sleep', $key_name);
    }
    return $res;
}
