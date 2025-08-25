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
    
    return in_array($ImageType, [1, 2, 3, 6, 18]);
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

function abFloatval($num)
{
    return abs(floatval($num));
}

//获取当前年龄
function now_age($birthdayTimestamp,$currentTimestamp,$str = true,$decimal = false){
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
    if($decimal){
        $totalDays = $currentDate->diff($birthdayDate)->days;
        $decimalAge = round($totalDays / 365.25, 2);
        return $decimalAge;
    }
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

//身高键取值
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

//脾胃问题
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

//体重区间
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

//睡眠区间
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

//睡眠问题
function sleep_ques()
{
    return [
        [
            'id'    => 1,
            'ques'  => '大多数情况，每晚孩子睡眠时间有多少小时',
            'answer'=> [
                [
                    'name'  => '9-11个小时',
                    'num'   => 5
                ],[
                    'name'  => '8-9个小时',
                    'num'   => 4
                ],[
                    'name'  => '7-8个小时',
                    'num'   => 3
                ],[
                    'name'  => '5-7个小时',
                    'num'   => 2
                ],[
                    'name'  => '小于5个小时',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 2,
            'ques'  => '通常您的孩子上床睡觉后多长时间才能睡着',
            'answer'=> [
                [
                    'name'  => '小于15分钟',
                    'num'   => 5
                ],[
                    'name'  => '15-30分钟',
                    'num'   => 4
                ],[
                    'name'  => '30-45分钟',
                    'num'   => 3
                ],[
                    'name'  => '45-50分钟',
                    'num'   => 2
                ],[
                    'name'  => '大于60分钟',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 3,
            'ques'  => '孩子不愿上床睡觉',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 4,
            'ques'  => '晚上孩子入睡困难',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 5,
            'ques'  => '孩子入睡会感到焦虑或恐惧',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 6,
            'ques'  => '孩子入睡时会惊吓或抽动',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 7,
            'ques'  => '孩子入睡时会出现重复动作，例如频繁摇头或撞击',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 8,
            'ques'  => '孩子入睡时会经历鲜明的梦境场景',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 9,
            'ques'  => '孩子睡觉时会过度出汗',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 10,
            'ques'  => '每晚上孩子会醒来两次以上',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 11,
            'ques'  => '晚上醒后，孩子很难再入睡',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 12,
            'ques'  => '睡着后，孩子频繁出现腿部抽动或痉挛，或者在深夜的时候经常改变睡姿或者踢被子',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 13,
            'ques'  => '深夜孩子会出现会呼吸困难',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 14,
            'ques'  => '孩子睡着的时候会上气不接下气或呼吸停止',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 15,
            'ques'  => '孩子打鼾',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 16,
            'ques'  => '孩子夜间过度出汗',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 17,
            'ques'  => '您发现孩子会梦游',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 18,
            'ques'  => '您发现孩子说梦话',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 19,
            'ques'  => '孩子睡觉时会磨牙',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 20,
            'ques'  => '孩子会大叫而惊醒或者迷糊中您不能叫醒他/她，但是事后不能记起',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 21,
            'ques'  => '孩子夜里会尖叫醒来或迷迷糊糊，似乎您帮不到他/她，但第二天早上孩子不记得',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 22,
            'ques'  => '孩子早上经常很难醒来',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 23,
            'ques'  => '孩子早上醒来后感到疲乏',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 24,
            'ques'  => '第二天醒来后孩子感到身体不能动',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 25,
            'ques'  => '孩子会出现白天嗜睡',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ],[
            'id'    => 26,
            'ques'  => '孩子会不分场合能突然睡着',
            'answer'=> [
                [
                    'name'  => '无',
                    'num'   => 5
                ],[
                    'name'  => '偶尔（每月少于1或2次）',
                    'num'   => 4
                ],[
                    'name'  => '有时（每月1-2次）',
                    'num'   => 3
                ],[
                    'name'  => '经常（每月3或5次）',
                    'num'   => 2
                ],[
                    'name'  => '总是（日常）',
                    'num'   => 1
                ]
            ]
        ]
    ];
}

/**
 * * 注：
 * * 睡眠总体情况分析：较差（77≤）、不佳（78-90）、正常（≥91）
 * 26个题目总和
 *
 * 睡眠时长：较差（1）、不佳（2-3）、正常（4-5）
 * 1题
 *
 * 入睡行为分析（入睡困难和睡眠维持障碍）：较差（20≤）、不佳（21-24）、正常（≥25）
 * 1、2、3、4、5、10、11题
 *
 * 睡眠质量：较差（56≤）、不佳（57-66）、正常（≥67）
 * 6、7、8、9、12、13、14、15、16、17、18、19、20、21、22、23、24、25、26题
 *
 * 呼吸状态分析（睡眠呼吸障碍）：较差（8≤）、正常（≥9）
 * 13、14、15题
 *
 * 觉醒状态分析（觉醒障碍）：较差（8≤）、正常（≥9）
 * 17、20、21题
 *
 * 睡醒转换状态分析（睡眠觉醒转换障碍）：较差（17≤）、正常（≥18）
 * 6、7、8、12、18、19题
 *
 * 嗜睡状态分析（过度嗜睡）：较差（≤14）、正常（≥15）
 * 22、23、24、25、26题
 *
 * 出汗状态分析（夜间多汗）：较差（≤5）、正常（≥6）
 * 9、16题
 * */
function sleep_check_com($arr){
    $res = [
        [
            'name'      => '睡眠总体情况分析',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '睡眠时长',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '入睡行为分析（入睡困难和睡眠维持障碍）',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '睡眠质量',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '呼吸状态分析（睡眠呼吸障碍）',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '觉醒状态分析（觉醒障碍）',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '睡醒转换状态分析（睡眠觉醒转换障碍）',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '嗜睡状态分析（过度嗜睡）',
            'num'       => 0,
            'result'    => '',
        ],
        [
            'name'      => '出汗状态分析（夜间多汗）',
            'num'       => 0,
            'result'    => '',
        ],

    ];
    foreach ($arr as $k => $v){
        if(in_array($k,[1])){
            $res[1]['num'] += $v;
        }
        if(in_array($k,[1,2,3,4,5,10,11])){
            $res[2]['num'] += $v;
        }
        if(in_array($k,[6,7,8,9,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26])){
            $res[3]['num'] += $v;
        }
        if(in_array($k,[13,14,15])){
            $res[4]['num'] += $v;
        }
        if(in_array($k,[17,20,21])){
            $res[5]['num'] += $v;
        }
        if(in_array($k,[6,7,8,12,18,19])){
            $res[6]['num'] += $v;
        }
        if(in_array($k,[22,23,24,25,26])){
            $res[7]['num'] += $v;
        }
        if(in_array($k,[9,16])){
            $res[8]['num'] += $v;
        }
        $res[0]['num'] += $v;
    }
    foreach ($res as $ke => $re){
        switch ($ke){
            case 0:
                if($re['num'] >= 91){
                    $key_name = 'result_total_3';
                    $res[$ke]['result_name'] = '正常';
                }else if($re['num'] <= 77){
                    $key_name = 'result_total_1';
                    $res[$ke]['result_name'] = '较差';
                }else{
                    $key_name = 'result_total_2';
                    $res[$ke]['result_name'] = '不佳';
                }
                break;
            case 1:
                if($re['num'] >= 4){
                    $key_name = 'result_'.$ke.'_3';
                    $res[$ke]['result_name'] = '正常';
                }else if($re['num'] <= 1){
                    $key_name = 'result_'.$ke.'_1';
                    $res[$ke]['result_name'] = '较差';
                }else{
                    $key_name = 'result_'.$ke.'_2';
                    $res[$ke]['result_name'] = '不佳';
                }
                break;
            case 2:
                if($re['num'] >= 25){
                    $key_name = 'result_'.$ke.'_3';
                    $res[$ke]['result_name'] = '正常';
                }else if($re['num'] <= 20){
                    $key_name = 'result_'.$ke.'_1';
                    $res[$ke]['result_name'] = '较差';
                }else{
                    $key_name = 'result_'.$ke.'_2';
                    $res[$ke]['result_name'] = '不佳';
                }
                break;
            case 3:
                if($re['num'] >= 67){
                    $key_name = 'result_'.$ke.'_3';
                    $res[$ke]['result_name'] = '正常';
                }else if($re['num'] <= 56){
                    $key_name = 'result_'.$ke.'_1';
                    $res[$ke]['result_name'] = '较差';
                }else{
                    $key_name = 'result_'.$ke.'_2';
                    $res[$ke]['result_name'] = '不佳';
                }
                break;
            case 4:
                if($re['num'] >= 9){
                    $key_name = 'result_'.$ke.'_2';
                    $res[$ke]['result_name'] = '正常';
                }else{
                    $key_name = 'result_'.$ke.'_1';
                    $res[$ke]['result_name'] = '较差';
                }
                break;
            case 5:
                if($re['num'] >= 9){
                    $key_name = 'result_'.$ke.'_2';
                    $res[$ke]['result_name'] = '正常';
                }else{
                    $key_name = 'result_'.$ke.'_1';
                    $res[$ke]['result_name'] = '较差';
                }
                break;
            case 6:
                if($re['num'] >= 18){
                    $key_name = 'result_'.$ke.'_2';
                    $res[$ke]['result_name'] = '正常';
                }else{
                    $key_name = 'result_'.$ke.'_1';
                    $res[$ke]['result_name'] = '较差';
                }
                break;
            case 7:
                if($re['num'] >= 15){
                    $key_name = 'result_'.$ke.'_2';
                    $res[$ke]['result_name'] = '正常';
                }else{
                    $key_name = 'result_'.$ke.'_1';
                    $res[$ke]['result_name'] = '较差';
                }
                break;
            case 8:
                if($re['num'] >= 6){
                    $key_name = 'result_'.$ke.'_2';
                    $res[$ke]['result_name'] = '正常';
                }else{
                    $key_name = 'result_'.$ke.'_1';
                    $res[$ke]['result_name'] = '较差';
                }
                break;
        }
        $res[$ke]['result'] = ConfigServer::get('baby_sleep', $key_name);

    }
    return $res;
}


/**
 * 年级范围
 */
function sport_grade_arr()
{
    return [
        [
            'id'    => 1,
            'name'  => '一年级',
        ],
        [
            'id'    => 2,
            'name'  => '二年级',
        ],
        [
            'id'    => 3,
            'name'  => '三年级',
        ],
        [
            'id'    => 4,
            'name'  => '四年级',
        ],
        [
            'id'    => 5,
            'name'  => '五年级',
        ],
        [
            'id'    => 6,
            'name'  => '六年级',
        ],
        [
            'id'    => 7,
            'name'  => '初一',
        ],
        [
            'id'    => 8,
            'name'  => '初二',
        ],
        [
            'id'    => 9,
            'name'  => '初三',
        ],
        [
            'id'    => 10,
            'name'  => '高一',
        ],
        [
            'id'    => 11,
            'name'  => '高二',
        ],
        [
            'id'    => 12,
            'name'  => '高三',
        ],
        [
            'id'    => 13,
            'name'  => '大一',
        ],
        [
            'id'    => 14,
            'name'  => '大二',
        ],
        [
            'id'    => 15,
            'name'  => '大三',
        ],
        [
            'id'    => 16,
            'name'  => '大四',
        ],
    ];
}

/**
 * 根据年级返回对应的测评项目
 * @param $id
 * @param $sex
 * @return array[]
 */
function sport_grade_to_select($id,$sex = 1,$no_bmi = false)
{
    if($no_bmi){
        $arr = [
            [
                'key'   => 'vital_capacity',
                'name'  => '肺活量',
                'value' => 15,
                'dw'    => 'ml',
                'pla'   => '请输入'
            ]
        ];
    }else{
        $arr = [
            [
                'key'   => 'bmi',
                'name'  => '体重指数(BMI)',
                'value' => 15,
                'dw'    => 'kg/m²',
                'pla'   => '请输入'
            ],
            [
                'key'   => 'vital_capacity',
                'name'  => '肺活量',
                'value' => 15,
                'dw'    => 'ml',
                'pla'   => '请输入'
            ]
        ];
    }

    switch ($id){
        case 1:
        case 2:
            $new_arr = [
                [
                    'key'   => 'run_50',
                    'name'  => '50米跑',
                    'value' => 20,
                    'dw'    => 's',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'sit_and_reach',
                    'name'  => '坐位体前屈',
                    'value' => 30,
                    'dw'    => 'cm',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'skip',
                    'name'  => '1分钟跳绳',
                    'value' => 20,
                    'dw'    => '次',
                    'pla'   => '请输入'
                ]
            ];
            break;
        case 3:
        case 4:
            $new_arr = [
                [
                    'key'   => 'run_50',
                    'name'  => '50米跑',
                    'value' => 20,
                    'dw'    => 's',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'sit_and_reach',
                    'name'  => '坐位体前屈',
                    'value' => 20,
                    'dw'    => 'cm',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'skip',
                    'name'  => '1分钟跳绳',
                    'value' => 20,
                    'dw'    => '次',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'sit_up',
                    'name'  => '1分钟仰卧起坐',
                    'value' => 10,
                    'dw'    => '次',
                    'pla'   => '请输入'
                ]
            ];
            break;
        case 5:
        case 6:
            $new_arr = [
                [
                    'key'   => 'run_50',
                    'name'  => '50米跑',
                    'value' => 20,
                    'dw'    => 's',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'sit_and_reach',
                    'name'  => '坐位体前屈',
                    'value' => 10,
                    'dw'    => 'cm',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'skip',
                    'name'  => '1分钟跳绳',
                    'value' => 10,
                    'dw'    => '次',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'sit_up',
                    'name'  => '1分钟仰卧起坐',
                    'value' => 20,
                    'dw'    => '次',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'run_400',
                    'name'  => '50米×8往返跑',
                    'value' => 10,
                    'dw'    => 's',
                    'pla'   => '请输入'
                ]
            ];
            break;
        default:
            $new_arr = [
                [
                    'key'   => 'run_50',
                    'name'  => '50米跑',
                    'value' => 20,
                    'dw'    => 's',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'sit_and_reach',
                    'name'  => '坐位体前屈',
                    'value' => 10,
                    'dw'    => 'cm',
                    'pla'   => '请输入'
                ],
                [
                    'key'   => 'jump',
                    'name'  => '立定跳远',
                    'value' => 10,
                    'dw'    => 'cm',
                    'pla'   => '请输入'
                ]
            ];
            $arr = $arr + $new_arr;
            if($sex == 1){
                $new_arr = [
                    [
                        'key'   => 'chinning',
                        'name'  => '引体向上',
                        'value' => 10,
                        'dw'    => '次',
                        'pla'   => '请输入'
                    ],
                    [
                        'key'   => 'run_1000',
                        'name'  => '1000米跑',
                        'value' => 20,
                        'dw'    => 's',
                        'pla'   => '请输入'
                    ]
                ];
            }else{
                $new_arr = [
                    [
                        'key'   => 'sit_up',
                        'name'  => '1分钟仰卧起坐',
                        'value' => 10,
                        'dw'    => '次',
                        'pla'   => '请输入'
                    ],
                    [
                        'key'   => 'run_800',
                        'name'  => '800米跑',
                        'value' => 20,
                        'dw'    => 's',
                        'pla'   => '请输入'
                    ]
                ];
            }
            break;
    }
    return array_merge($arr,$new_arr);
}

/**
 * BMI 结果
 * @param $id
 * @param $num
 * @param $sex
 * @return array
 */
function sport_bmi_result($id,$num,$sex = 1)
{
    $return = [
        'name'  => '未知',
        'num'   => 0,
    ];
    switch ($id){
        case 1:
            if($sex == 1){
                $max = 20.4;
                $min = 13.4;
                $over = 18.2;
            }else{
                $max = 19.3;
                $min = 13.2;
                $over = 17.4;
            }
        break;
        case 2:
            if($sex == 1){
                $max = 20.5;
                $min = 13.6;
                $over = 18.5;
            }else{
                $max = 20.3;
                $min = 13.4;
                $over = 17.9;
            }
            break;
        case 3:
            if($sex == 1){
                $max = 22.2;
                $min = 13.8;
                $over = 19.5;
            }else{
                $max = 21.2;
                $min = 13.5;
                $over = 18.7;
            }
            break;
        case 4:
            if($sex == 1){
                $min    = 14.1;
                $over   = 20.2;
                $max    = 22.7;
            }else{
                $min    = 13.6;
                $over   = 19.5;
                $max    = 22.1;
            }
            break;
        case 5:
            if($sex == 1){
                $min    = 14.3;
                $over   = 21.5;
                $max    = 24.2;
            }else{
                $min    = 13.7;
                $over   = 20.6;
                $max    = 23.0;
            }
            break;
        case 6:
            if($sex == 1){
                $min    = 14.6;
                $over   = 21.9;
                $max    = 24.6;
            }else{
                $min    = 14.1;
                $over   = 20.9;
                $max    = 23.7;
            }
            break;
        case 7:
            if($sex == 1){
                $min    = 15.4;
                $over   = 22.2;
                $max    = 25.0;
            }else{
                $min    = 14.7;
                $over   = 21.8;
                $max    = 24.5;
            }
            break;
        case 8:
            if($sex == 1){
                $min    = 15.6;
                $over   = 22.6;
                $max    = 25.3;
            }else{
                $min    = 15.2;
                $over   = 22.3;
                $max    = 24.9;
            }
            break;
        case 9:
            if($sex == 1){
                $min    = 15.7;
                $over   = 22.9;
                $max    = 26.1;
            }else{
                $min    = 15.9;
                $over   = 22.7;
                $max    = 25.2;
            }
            break;
        case 10:
            if($sex == 1){
                $min    = 16.4;
                $over   = 23.3;
                $max    = 26.4;
            }else{
                $min    = 16.4;
                $over   = 22.8;
                $max    = 25.3;
            }
            break;
        case 11:
            if($sex == 1){
                $min    = 16.7;
                $over   = 23.8;
                $max    = 26.6;
            }else{
                $min    = 16.8;
                $over   = 23.3;
                $max    = 25.5;
            }
            break;
        case 12:
            if($sex == 1){
                $min    = 17.2;
                $over   = 23.9;
                $max    = 27.4;
            }else{
                $min    = 17.0;
                $over   = 23.4;
                $max    = 25.8;
            }
            break;
        case 13:
        case 14:
        case 15:
        case 16:
            if($sex == 1){
                $min    = 17.8;
                $over   = 24.0;
                $max    = 28.0;
            }else{
                $min    = 17.1;
                $over   = 24.0;
                $max    = 28.0;
            }
            break;
    }
    if($num<=$min){
        $return['name'] = '低体重';
        $return['num']  = 80;
    }else if($num >= $max){
        $return['name'] = '肥胖';
        $return['num']  = 60;
    }else if($num < $max && $num >=$over){
        $return['name'] = '超重';
        $return['num']  = 80;
    }else{
        $return['name'] = '正常';
        $return['num']  = 100;
    }
    return $return;
}

//男女肺活量
function sport_vital_capacity_result($id,$num,$sex = 1)
{
    $return = [
        'name'  => '未知',
        'num'   => 0,
    ];
    switch ($id){
        case 1:
            if($sex == 1){
                $y_100 = 1700;
                $y_95 = 1600;
                $y_90 = 1500;
                $l_85 = 1400;
                $l_80 = 1300;
                $j_78 = 1240;
                $j_76 = 1180;
                $j_74 = 1120;
                $j_72 = 1060;
                $j_70 = 1000;
                $j_68 = 940;
                $j_66 = 880;
                $j_64 = 820;
                $j_62 = 760;
                $j_60 = 700;
                $b_50 = 660;
                $b_40 = 620;
                $b_30 = 580;
                $b_20 = 540;
                $b_10 = 500;
            }else{
                $y_100 = 1400;
                $y_95 =  1300;
                $y_90 =  1200;
                $l_85 =  1100;
                $l_80 =  1000;
                $j_78 =  960;
                $j_76 =  920;
                $j_74 =  880;
                $j_72 =  840;
                $j_70 =  800;
                $j_68 =  760;
                $j_66 =  720;
                $j_64 =  680;
                $j_62 =  640;
                $j_60 =  600;
                $b_50 =  580;
                $b_40 =  560;
                $b_30 =  540;
                $b_20 =  520;
                $b_10 =  500;
            }
            break;
        case 2:
            if($sex == 1){
                $y_100 = 2000;
                $y_95 =  1900;
                $y_90 =  1800;
                $l_85 =  1650;
                $l_80 =  1500;
                $j_78 =  1430;
                $j_76 =  1360;
                $j_74 =  1290;
                $j_72 =  1220;
                $j_70 =  1150;
                $j_68 =  1080;
                $j_66 =  1010;
                $j_64 =  940;
                $j_62 =  870;
                $j_60 =  800;
                $b_50 =  750;
                $b_40 =  700;
                $b_30 =  650;
                $b_20 =  600;
                $b_10 =  550;
            }else{
                $y_100 = 1600;
                $y_95 =  1500;
                $y_90 =  1400;
                $l_85 =  1300;
                $l_80 =  1200;
                $j_78 =  1150;
                $j_76 =  1100;
                $j_74 =  1050;
                $j_72 =  1000;
                $j_70 =  950;
                $j_68 =  900;
                $j_66 =  850;
                $j_64 =  800;
                $j_62 =  750;
                $j_60 =  700;
                $b_50 =  680;
                $b_40 =  660;
                $b_30 =  640;
                $b_20 =  620;
                $b_10 =  600;
            }
            break;
        case 3:
            if($sex == 1){
                $y_100 = 2300;
                $y_95 =  2200;
                $y_90 =  2100;
                $l_85 =  1900;
                $l_80 =  1700;
                $j_78 =  1620;
                $j_76 =  1540;
                $j_74 =  1460;
                $j_72 =  1380;
                $j_70 =  1300;
                $j_68 =  1220;
                $j_66 =  1140;
                $j_64 =  1060;
                $j_62 =  980;
                $j_60 =  900;
                $b_50 =  840;
                $b_40 =  780;
                $b_30 =  720;
                $b_20 =  660;
                $b_10 =  600;
            }else{
                $y_100 = 1800;
                $y_95 =  1700;
                $y_90 =  1600;
                $l_85 =  1500;
                $l_80 =  1400;
                $j_78 =  1340;
                $j_76 =  1280;
                $j_74 =  1220;
                $j_72 =  1160;
                $j_70 =  1100;
                $j_68 =  1040;
                $j_66 =  980;
                $j_64 =  920;
                $j_62 =  860;
                $j_60 =  800;
                $b_50 =  780;
                $b_40 =  760;
                $b_30 =  740;
                $b_20 =  720;
                $b_10 =  700;
            }
            break;
        case 4:
            if($sex == 1){
                $y_100 = 2600;
                $y_95 =  2500;
                $y_90 =  2400;
                $l_85 =  2150;
                $l_80 =  1900;
                $j_78 =  1820;
                $j_76 =  1740;
                $j_74 =  1660;
                $j_72 =  1580;
                $j_70 =  1500;
                $j_68 =  1420;
                $j_66 =  1340;
                $j_64 =  1260;
                $j_62 =  1180;
                $j_60 =  1100;
                $b_50 =  1030;
                $b_40 =  960;
                $b_30 =  890;
                $b_20 =  820;
                $b_10 =  750;
            }else{
                $y_100 = 2000;
                $y_95 =  1900;
                $y_90 =  1800;
                $l_85 =  1700;
                $l_80 =  1600;
                $j_78 =  1530;
                $j_76 =  1460;
                $j_74 =  1390;
                $j_72 =  1320;
                $j_70 =  1250;
                $j_68 =  1180;
                $j_66 =  1110;
                $j_64 =  1040;
                $j_62 =  970;
                $j_60 =  900;
                $b_50 =  880;
                $b_40 =  860;
                $b_30 =  840;
                $b_20 =  820;
                $b_10 =  800;
            }
            break;
        case 5:
            if($sex == 1){
                $y_100 = 2900;
                $y_95 =  2800;
                $y_90 =  2700;
                $l_85 =  2450;
                $l_80 =  2200;
                $j_78 =  2110;
                $j_76 =  2020;
                $j_74 =  1930;
                $j_72 =  1840;
                $j_70 =  1750;
                $j_68 =  1660;
                $j_66 =  1570;
                $j_64 =  1480;
                $j_62 =  1390;
                $j_60 =  1300;
                $b_50 =  1220;
                $b_40 =  1140;
                $b_30 =  1060;
                $b_20 =  980;
                $b_10 =  900;
            }else{
                $y_100 = 2250;
                $y_95 =  2150;
                $y_90 =  2050;
                $l_85 =  1950;
                $l_80 =  1850;
                $j_78 =  1770;
                $j_76 =  1690;
                $j_74 =  1610;
                $j_72 =  1530;
                $j_70 =  1450;
                $j_68 =  1370;
                $j_66 =  1290;
                $j_64 =  1210;
                $j_62 =  1130;
                $j_60 =  1050;
                $b_50 =  1020;
                $b_40 =  990;
                $b_30 =  960;
                $b_20 =  930;
                $b_10 =  900;
            }
            break;
        case 6:
            if($sex == 1){
                $y_100 = 3200;
                $y_95 =  3100;
                $y_90 =  3000;
                $l_85 =  2750;
                $l_80 =  2500;
                $j_78 =  2400;
                $j_76 =  2300;
                $j_74 =  2200;
                $j_72 =  2100;
                $j_70 =  2000;
                $j_68 =  1900;
                $j_66 =  1800;
                $j_64 =  1700;
                $j_62 =  1600;
                $j_60 =  1500;
                $b_50 =  1410;
                $b_40 =  1320;
                $b_30 =  1230;
                $b_20 =  1140;
                $b_10 =  1050;
            }else{
                $y_100 = 2500;
                $y_95 =  2400;
                $y_90 =  2300;
                $l_85 =  2200;
                $l_80 =  2100;
                $j_78 =  2010;
                $j_76 =  1920;
                $j_74 =  1830;
                $j_72 =  1740;
                $j_70 =  1650;
                $j_68 =  1560;
                $j_66 =  1470;
                $j_64 =  1380;
                $j_62 =  1290;
                $j_60 =  1200;
                $b_50 =  1170;
                $b_40 =  1140;
                $b_30 =  1110;
                $b_20 =  1080;
                $b_10 =  1050;
            }
            break;
        case 7:
            if($sex == 1){
                $y_100 = 3640;
                $y_95 =  3520;
                $y_90 =  3400;
                $l_85 =  3150;
                $l_80 =  2900;
                $j_78 =  2780;
                $j_76 =  2660;
                $j_74 =  2540;
                $j_72 =  2420;
                $j_70 =  2300;
                $j_68 =  2180;
                $j_66 =  2060;
                $j_64 =  1940;
                $j_62 =  1820;
                $j_60 =  1700;
                $b_50 =  1600;
                $b_40 =  1500;
                $b_30 =  1400;
                $b_20 =  1300;
                $b_10 =  1200;
            }else{
                $y_100 = 2750;
                $y_95 =  2650;
                $y_90 =  2550;
                $l_85 =  2450;
                $l_80 =  2350;
                $j_78 =  2250;
                $j_76 =  2150;
                $j_74 =  2050;
                $j_72 =  1950;
                $j_70 =  1850;
                $j_68 =  1750;
                $j_66 =  1650;
                $j_64 =  1550;
                $j_62 =  1450;
                $j_60 =  1350;
                $b_50 =  1310;
                $b_40 =  1270;
                $b_30 =  1230;
                $b_20 =  1190;
                $b_10 =  1150;
            }
            break;
        case 8:
            if($sex == 1){
                $y_100 = 3940;
                $y_95 =  3820;
                $y_90 =  3700;
                $l_85 =  3450;
                $l_80 =  3200;
                $j_78 =  3080;
                $j_76 =  2960;
                $j_74 =  2840;
                $j_72 =  2720;
                $j_70 =  2600;
                $j_68 =  2480;
                $j_66 =  2360;
                $j_64 =  2240;
                $j_62 =  2120;
                $j_60 =  2000;
                $b_50 =  1890;
                $b_40 =  1780;
                $b_30 =  1670;
                $b_20 =  1560;
                $b_10 =  1450;
            }else{
                $y_100 = 2900;
                $y_95 =  2850;
                $y_90 =  2800;
                $l_85 =  2650;
                $l_80 =  2500;
                $j_78 =  2400;
                $j_76 =  2300;
                $j_74 =  2200;
                $j_72 =  2100;
                $j_70 =  2000;
                $j_68 =  1900;
                $j_66 =  1800;
                $j_64 =  1700;
                $j_62 =  1600;
                $j_60 =  1500;
                $b_50 =  1460;
                $b_40 =  1420;
                $b_30 =  1380;
                $b_20 =  1340;
                $b_10 =  1300;
            }
            break;
        case 9:
            if($sex == 1){
                $y_100 = 4240;
                $y_95 =  4120;
                $y_90 =  4000;
                $l_85 =  3750;
                $l_80 =  3500;
                $j_78 =  3380;
                $j_76 =  3260;
                $j_74 =  3140;
                $j_72 =  3020;
                $j_70 =  2900;
                $j_68 =  2780;
                $j_66 =  2660;
                $j_64 =  2540;
                $j_62 =  2420;
                $j_60 =  2300;
                $b_50 =  2180;
                $b_40 =  2060;
                $b_30 =  1940;
                $b_20 =  1820;
                $b_10 =  1700;
            }else{
                $y_100 = 3050;
                $y_95 =  3000;
                $y_90 =  2950;
                $l_85 =  2800;
                $l_80 =  2650;
                $j_78 =  2550;
                $j_76 =  2450;
                $j_74 =  2350;
                $j_72 =  2250;
                $j_70 =  2150;
                $j_68 =  2050;
                $j_66 =  1950;
                $j_64 =  1850;
                $j_62 =  1750;
                $j_60 =  1650;
                $b_50 =  1610;
                $b_40 =  1570;
                $b_30 =  1530;
                $b_20 =  1490;
                $b_10 =  1450;
            }
            break;
        case 10:
            if($sex == 1){
                $y_100 = 4540;
                $y_95 =  4420;
                $y_90 =  4300;
                $l_85 =  4050;
                $l_80 =  3800;
                $j_78 =  3680;
                $j_76 =  3560;
                $j_74 =  3440;
                $j_72 =  3320;
                $j_70 =  3200;
                $j_68 =  3080;
                $j_66 =  2960;
                $j_64 =  2840;
                $j_62 =  2720;
                $j_60 =  2600;
                $b_50 =  2470;
                $b_40 =  2340;
                $b_30 =  2210;
                $b_20 =  2080;
                $b_10 =  1950;
            }else{
                $y_100 = 3150;
                $y_95 =  3100;
                $y_90 =  3050;
                $l_85 =  2900;
                $l_80 =  2750;
                $j_78 =  2650;
                $j_76 =  2550;
                $j_74 =  2450;
                $j_72 =  2350;
                $j_70 =  2250;
                $j_68 =  2150;
                $j_66 =  2050;
                $j_64 =  1950;
                $j_62 =  1850;
                $j_60 =  1750;
                $b_50 =  1710;
                $b_40 =  1670;
                $b_30 =  1630;
                $b_20 =  1590;
                $b_10 =  1550;
            }
            break;
        case 11:
            if($sex == 1){
                $y_100 = 4740;
                $y_95 =  4620;
                $y_90 =  4500;
                $l_85 =  4250;
                $l_80 =  4000;
                $j_78 =  3880;
                $j_76 =  3760;
                $j_74 =  3640;
                $j_72 =  3520;
                $j_70 =  3400;
                $j_68 =  3280;
                $j_66 =  3160;
                $j_64 =  3040;
                $j_62 =  2920;
                $j_60 =  2800;
                $b_50 =  2660;
                $b_40 =  2520;
                $b_30 =  2380;
                $b_20 =  2240;
                $b_10 =  2100;
            }else{
                $y_100 = 3250;
                $y_95 =  3200;
                $y_90 =  3150;
                $l_85 =  3000;
                $l_80 =  2850;
                $j_78 =  2750;
                $j_76 =  2650;
                $j_74 =  2550;
                $j_72 =  2450;
                $j_70 =  2350;
                $j_68 =  2250;
                $j_66 =  2150;
                $j_64 =  2050;
                $j_62 =  1950;
                $j_60 =  1850;
                $b_50 =  1810;
                $b_40 =  1770;
                $b_30 =  1730;
                $b_20 =  1690;
                $b_10 =  1650;
            }
            break;
        case 12:
            if($sex == 1){
                $y_100 = 4940;
                $y_95 =  4820;
                $y_90 =  4700;
                $l_85 =  4450;
                $l_80 =  4200;
                $j_78 =  4080;
                $j_76 =  3960;
                $j_74 =  3840;
                $j_72 =  3720;
                $j_70 =  3600;
                $j_68 =  3480;
                $j_66 =  3360;
                $j_64 =  3240;
                $j_62 =  3120;
                $j_60 =  3000;
                $b_50 =  2850;
                $b_40 =  2700;
                $b_30 =  2550;
                $b_20 =  2400;
                $b_10 =  2250;
            }else{
                $y_100 = 3350;
                $y_95 =  3300;
                $y_90 =  3250;
                $l_85 =  3100;
                $l_80 =  2950;
                $j_78 =  2850;
                $j_76 =  2750;
                $j_74 =  2650;
                $j_72 =  2550;
                $j_70 =  2450;
                $j_68 =  2350;
                $j_66 =  2250;
                $j_64 =  2150;
                $j_62 =  2050;
                $j_60 =  1950;
                $b_50 =  1910;
                $b_40 =  1870;
                $b_30 =  1830;
                $b_20 =  1790;
                $b_10 =  1750;
            }
            break;
        case 13:
        case 14:
            if($sex == 1){
                $y_100 = 5040;
                $y_95 =  4920;
                $y_90 =  4800;
                $l_85 =  4550;
                $l_80 =  4300;
                $j_78 =  4180;
                $j_76 =  4060;
                $j_74 =  3940;
                $j_72 =  3820;
                $j_70 =  3700;
                $j_68 =  3580;
                $j_66 =  3460;
                $j_64 =  3340;
                $j_62 =  3220;
                $j_60 =  3100;
                $b_50 =  2940;
                $b_40 =  2780;
                $b_30 =  2620;
                $b_20 =  2460;
                $b_10 =  2300;
            }else{
                $y_100 = 3400;
                $y_95 =  3350;
                $y_90 =  3300;
                $l_85 =  3150;
                $l_80 =  3000;
                $j_78 =  2900;
                $j_76 =  2800;
                $j_74 =  2700;
                $j_72 =  2600;
                $j_70 =  2500;
                $j_68 =  2400;
                $j_66 =  2300;
                $j_64 =  2200;
                $j_62 =  2100;
                $j_60 =  2000;
                $b_50 =  1960;
                $b_40 =  1920;
                $b_30 =  1880;
                $b_20 =  1840;
                $b_10 =  1800;
            }
            break;
        case 15:
        case 16:
            if($sex == 1){
                $y_100 = 5140;
                $y_95 =  5020;
                $y_90 =  4900;
                $l_85 =  4650;
                $l_80 =  4400;
                $j_78 =  4280;
                $j_76 =  4160;
                $j_74 =  4040;
                $j_72 =  3920;
                $j_70 =  3800;
                $j_68 =  3680;
                $j_66 =  3560;
                $j_64 =  3440;
                $j_62 =  3320;
                $j_60 =  3200;
                $b_50 =  3030;
                $b_40 =  2860;
                $b_30 =  2690;
                $b_20 =  2520;
                $b_10 =  2350;
            }else{
                $y_100 = 3450;
                $y_95 =  3400;
                $y_90 =  3350;
                $l_85 =  3200;
                $l_80 =  3050;
                $j_78 =  2950;
                $j_76 =  2850;
                $j_74 =  2750;
                $j_72 =  2650;
                $j_70 =  2550;
                $j_68 =  2450;
                $j_66 =  2350;
                $j_64 =  2250;
                $j_62 =  2150;
                $j_60 =  2050;
                $b_50 =  2010;
                $b_40 =  1970;
                $b_30 =  1930;
                $b_20 =  1890;
                $b_10 =  1850;
            }
            break;
        default:
            return ['num'=>0,'name'=>'未知'];
    }
    if($num<$b_10){
        $return['name'] = '不及格';
        $return['num']  = 0;
    }else if($num >= $b_10 && $num < $b_20){
        $return['name'] = '不及格';
        $return['num']  = 10;
    }else if($num >= $b_20 && $num < $b_30){
        $return['name'] = '不及格';
        $return['num']  = 20;
    }else if($num >= $b_30 && $num < $b_40){
        $return['name'] = '不及格';
        $return['num']  = 30;
    }else if($num >= $b_40 && $num < $b_50){
        $return['name'] = '不及格';
        $return['num']  = 40;
    }else if($num >= $b_50 && $num < $j_60){
        $return['name'] = '不及格';
        $return['num']  = 50;
    }else if($num >= $j_60 && $num < $j_62){
        $return['name'] = '及格';
        $return['num']  = 60;
    }else if($num >= $j_62 && $num < $j_64){
        $return['name'] = '及格';
        $return['num']  = 62;
    }else if($num >= $j_64 && $num < $j_66){
        $return['name'] = '及格';
        $return['num']  = 64;
    }else if($num >= $j_66 && $num < $j_68){
        $return['name'] = '及格';
        $return['num']  = 66;
    }else if($num >= $j_68 && $num < $j_70){
        $return['name'] = '及格';
        $return['num']  = 68;
    }else if($num >= $j_70 && $num < $j_72){
        $return['name'] = '及格';
        $return['num']  = 70;
    }else if($num >= $j_72 && $num < $j_74){
        $return['name'] = '及格';
        $return['num']  = 72;
    }else if($num >= $j_74 && $num < $j_76){
        $return['name'] = '及格';
        $return['num']  = 74;
    }else if($num >= $j_76 && $num < $j_78){
        $return['name'] = '及格';
        $return['num']  = 76;
    }else if($num >= $j_78 && $num < $l_80){
        $return['name'] = '及格';
        $return['num']  = 78;
    }else if($num >= $l_80 && $num < $l_85){
        $return['name'] = '良好';
        $return['num']  = 80;
    }else if($num >= $l_85 && $num < $y_90){
        $return['name'] = '良好';
        $return['num']  = 85;
    }else if($num >= $y_90 && $num < $y_95){
        $return['name'] = '优秀';
        $return['num']  = 90;
    }else if($num >= $y_95 && $num < $y_100){
        $return['name'] = '优秀';
        $return['num']  = 95;
    }else if($num >= $y_100 ){
        $return['name'] = '优秀';
        $return['num']  = 100;
    }
    return $return;
}

//男女50米
function sport_run_50_result($id,$num,$sex = 1)
{
    $return = [
        'name'  => '未知',
        'num'   => 0,
    ];
    switch ($id){
        case 1:
            if($sex == 1){
                $y_100 = 10.2;
                $y_95  = 10.3;
                $y_90  = 10.4;
                $l_85  = 10.5;
                $l_80  = 10.6;
                $j_78  = 10.8;
                $j_76  = 11.0;
                $j_74  = 11.2;
                $j_72  = 11.4;
                $j_70  = 11.6;
                $j_68  = 11.8;
                $j_66  = 12.0;
                $j_64  = 12.2;
                $j_62  = 12.4;
                $j_60  = 12.6;
                $b_50  = 12.8;
                $b_40  = 13.0;
                $b_30  = 13.2;
                $b_20  = 13.4;
                $b_10  = 13.6;
            }else{
                $y_100 = 11.0;
                $y_95  = 11.1;
                $y_90  = 11.2;
                $l_85  = 11.5;
                $l_80  = 11.8;
                $j_78  = 12.0;
                $j_76  = 12.2;
                $j_74  = 12.4;
                $j_72  = 12.6;
                $j_70  = 12.8;
                $j_68  = 13.0;
                $j_66  = 13.2;
                $j_64  = 13.4;
                $j_62  = 13.6;
                $j_60  = 13.8;
                $b_50  = 14.0;
                $b_40  = 14.2;
                $b_30  = 14.4;
                $b_20  = 14.6;
                $b_10  = 14.8;
            }
            break;
        case 2:
            if($sex == 1){
                $y_100 = 9.6;
                $y_95  = 9.7;
                $y_90  = 9.8;
                $l_85  = 9.9;
                $l_80  = 10.0;
                $j_78  = 10.2;
                $j_76  = 10.4;
                $j_74  = 10.6;
                $j_72  = 10.8;
                $j_70  = 11.0;
                $j_68  = 11.2;
                $j_66  = 11.4;
                $j_64  = 11.6;
                $j_62  = 11.8;
                $j_60  = 12.0;
                $b_50  = 12.2;
                $b_40  = 12.4;
                $b_30  = 12.6;
                $b_20  = 12.8;
                $b_10  = 13.0;
            }else{
                $y_100 = 10.0;
                $y_95  = 10.1;
                $y_90  = 10.2;
                $l_85  = 10.5;
                $l_80  = 10.8;
                $j_78  = 11.0;
                $j_76  = 11.2;
                $j_74  = 11.4;
                $j_72  = 11.6;
                $j_70  = 11.8;
                $j_68  = 12.0;
                $j_66  = 12.2;
                $j_64  = 12.4;
                $j_62  = 12.6;
                $j_60  = 12.8;
                $b_50  = 13.0;
                $b_40  = 13.2;
                $b_30  = 13.4;
                $b_20  = 13.6;
                $b_10  = 13.8;
            }
            break;
        case 3:
            if($sex == 1){
                $y_100 = 9.1;
                $y_95  = 9.2;
                $y_90  = 9.3;
                $l_85  = 9.4;
                $l_80  = 9.5;
                $j_78  = 9.7;
                $j_76  = 9.9;
                $j_74  = 10.1;
                $j_72  = 10.3;
                $j_70  = 10.5;
                $j_68  = 10.7;
                $j_66  = 10.9;
                $j_64  = 11.1;
                $j_62  = 11.3;
                $j_60  = 11.5;
                $b_50  = 11.7;
                $b_40  = 11.9;
                $b_30  = 12.1;
                $b_20  = 12.3;
                $b_10  = 12.5;
            }else{
                $y_100 = 9.2;
                $y_95  = 9.3;
                $y_90  = 9.4;
                $l_85  = 9.7;
                $l_80  = 10.0;
                $j_78  = 10.2;
                $j_76  = 10.4;
                $j_74  = 10.6;
                $j_72  = 10.8;
                $j_70  = 11.0;
                $j_68  = 11.2;
                $j_66  = 11.4;
                $j_64  = 11.6;
                $j_62  = 11.8;
                $j_60  = 12.0;
                $b_50  = 12.2;
                $b_40  = 12.4;
                $b_30  = 12.6;
                $b_20  = 12.8;
                $b_10  = 13.0;
            }
            break;
        case 4:
            if($sex == 1){
                $y_100 = 8.7;
                $y_95  = 8.8;
                $y_90  = 8.9;
                $l_85  = 9.0;
                $l_80  = 9.1;
                $j_78  = 9.3;
                $j_76  = 9.5;
                $j_74  = 9.7;
                $j_72  = 9.9;
                $j_70  = 10.1;
                $j_68  = 10.3;
                $j_66  = 10.5;
                $j_64  = 10.7;
                $j_62  = 10.9;
                $j_60  = 11.1;
                $b_50  = 11.3;
                $b_40  = 11.5;
                $b_30  = 11.7;
                $b_20  = 11.9;
                $b_10  = 12.1;
            }else{
                $y_100 = 8.7;
                $y_95  = 8.8;
                $y_90  = 8.9;
                $l_85  = 9.2;
                $l_80  = 9.5;
                $j_78  = 9.7;
                $j_76  = 9.9;
                $j_74  = 10.1;
                $j_72  = 10.3;
                $j_70  = 10.5;
                $j_68  = 10.7;
                $j_66  = 10.9;
                $j_64  = 11.1;
                $j_62  = 11.3;
                $j_60  = 11.5;
                $b_50  = 11.7;
                $b_40  = 11.9;
                $b_30  = 12.1;
                $b_20  = 12.3;
                $b_10  = 12.5;
            }
            break;
        case 5:
            if($sex == 1){
                $y_100 = 8.4;
                $y_95  = 8.5;
                $y_90  = 8.6;
                $l_85  = 8.7;
                $l_80  = 8.8;
                $j_78  = 9.0;
                $j_76  = 9.2;
                $j_74  = 9.4;
                $j_72  = 9.6;
                $j_70  = 9.8;
                $j_68  = 10.0;
                $j_66  = 10.2;
                $j_64  = 10.4;
                $j_62  = 10.6;
                $j_60  = 10.8;
                $b_50  = 11.0;
                $b_40  = 11.2;
                $b_30  = 11.4;
                $b_20  = 11.6;
                $b_10  = 11.8;
            }else{
                $y_100 = 8.3;
                $y_95  = 8.4;
                $y_90  = 8.5;
                $l_85  = 8.8;
                $l_80  = 9.1;
                $j_78  = 9.3;
                $j_76  = 9.5;
                $j_74  = 9.7;
                $j_72  = 9.9;
                $j_70  = 10.1;
                $j_68  = 10.3;
                $j_66  = 10.5;
                $j_64  = 10.7;
                $j_62  = 10.9;
                $j_60  = 11.1;
                $b_50  = 11.3;
                $b_40  = 11.5;
                $b_30  = 11.7;
                $b_20  = 11.9;
                $b_10  = 12.1;
            }
            break;
        case 6:
            if($sex == 1){
                $y_100 = 8.2;
                $y_95  = 8.3;
                $y_90  = 8.4;
                $l_85  = 8.5;
                $l_80  = 8.6;
                $j_78  = 8.8;
                $j_76  = 9.0;
                $j_74  = 9.2;
                $j_72  = 9.4;
                $j_70  = 9.6;
                $j_68  = 9.8;
                $j_66  = 10.0;
                $j_64  = 10.2;
                $j_62  = 10.4;
                $j_60  = 10.6;
                $b_50  = 10.8;
                $b_40  = 11.0;
                $b_30  = 11.2;
                $b_20  = 11.4;
                $b_10  = 11.6;
            }else{
                $y_100 = 8.2;
                $y_95  = 8.3;
                $y_90  = 8.4;
                $l_85  = 8.7;
                $l_80  = 9.0;
                $j_78  = 9.2;
                $j_76  = 9.4;
                $j_74  = 9.6;
                $j_72  = 9.8;
                $j_70  = 10.0;
                $j_68  = 10.2;
                $j_66  = 10.4;
                $j_64  = 10.6;
                $j_62  = 10.8;
                $j_60  = 11.0;
                $b_50  = 11.2;
                $b_40  = 11.4;
                $b_30  = 11.6;
                $b_20  = 11.8;
                $b_10  = 12.0;
            }
            break;
        case 7:
            if($sex == 1){
                $y_100 = 7.8;
                $y_95  = 7.9;
                $y_90  = 8.0;
                $l_85  = 8.1;
                $l_80  = 8.2;
                $j_78  = 8.4;
                $j_76  = 8.6;
                $j_74  = 8.8;
                $j_72  = 9.0;
                $j_70  = 9.2;
                $j_68  = 9.4;
                $j_66  = 9.6;
                $j_64  = 9.8;
                $j_62  = 10.0;
                $j_60  = 10.2;
                $b_50  = 10.4;
                $b_40  = 10.6;
                $b_30  = 10.8;
                $b_20  = 11.0;
                $b_10  = 11.2;
            }else{
                $y_100 = 8.1;
                $y_95  = 8.2;
                $y_90  = 8.3;
                $l_85  = 8.6;
                $l_80  = 8.9;
                $j_78  = 9.1;
                $j_76  = 9.3;
                $j_74  = 9.5;
                $j_72  = 9.7;
                $j_70  = 9.9;
                $j_68  = 10.1;
                $j_66  = 10.3;
                $j_64  = 10.5;
                $j_62  = 10.7;
                $j_60  = 10.9;
                $b_50  = 11.1;
                $b_40  = 11.3;
                $b_30  = 11.5;
                $b_20  = 11.7;
                $b_10  = 11.9;
            }
            break;
        case 8:
            if($sex == 1){
                $y_100 = 7.5;
                $y_95  = 7.6;
                $y_90  = 7.7;
                $l_85  = 7.8;
                $l_80  = 7.9;
                $j_78  = 8.1;
                $j_76  = 8.3;
                $j_74  = 8.5;
                $j_72  = 8.7;
                $j_70  = 8.9;
                $j_68  = 9.1;
                $j_66  = 9.3;
                $j_64  = 9.5;
                $j_62  = 9.7;
                $j_60  = 9.9;
                $b_50  = 10.1;
                $b_40  = 10.3;
                $b_30  = 10.5;
                $b_20  = 10.7;
                $b_10  = 10.9;
            }else{
                $y_100 = 8.0;
                $y_95  = 8.1;
                $y_90  = 8.2;
                $l_85  = 8.5;
                $l_80  = 8.8;
                $j_78  = 9.0;
                $j_76  = 9.2;
                $j_74  = 9.4;
                $j_72  = 9.6;
                $j_70  = 9.8;
                $j_68  = 10.0;
                $j_66  = 10.2;
                $j_64  = 10.4;
                $j_62  = 10.6;
                $j_60  = 10.8;
                $b_50  = 11.0;
                $b_40  = 11.2;
                $b_30  = 11.4;
                $b_20  = 11.6;
                $b_10  = 11.8;
            }
            break;
        case 9:
            if($sex == 1){
                $y_100 = 7.3;
                $y_95  = 7.4;
                $y_90  = 7.5;
                $l_85  = 7.6;
                $l_80  = 7.7;
                $j_78  = 7.9;
                $j_76  = 8.1;
                $j_74  = 8.3;
                $j_72  = 8.5;
                $j_70  = 8.7;
                $j_68  = 8.9;
                $j_66  = 9.1;
                $j_64  = 9.3;
                $j_62  = 9.5;
                $j_60  = 9.7;
                $b_50  = 9.9;
                $b_40  = 10.1;
                $b_30  = 10.3;
                $b_20  = 10.5;
                $b_10  = 10.7;
            }else{
                $y_100 = 7.9;
                $y_95  = 8.0;
                $y_90  = 8.1;
                $l_85  = 8.4;
                $l_80  = 8.7;
                $j_78  = 8.9;
                $j_76  = 9.1;
                $j_74  = 9.3;
                $j_72  = 9.5;
                $j_70  = 9.7;
                $j_68  = 9.9;
                $j_66  = 10.1;
                $j_64  = 10.3;
                $j_62  = 10.5;
                $j_60  = 10.7;
                $b_50  = 10.9;
                $b_40  = 11.1;
                $b_30  = 11.3;
                $b_20  = 11.5;
                $b_10  = 11.7;
            }
            break;
        case 10:
            if($sex == 1){
                $y_100 = 7.1;
                $y_95  = 7.2;
                $y_90  = 7.3;
                $l_85  = 7.4;
                $l_80  = 7.5;
                $j_78  = 7.7;
                $j_76  = 7.9;
                $j_74  = 8.1;
                $j_72  = 8.3;
                $j_70  = 8.5;
                $j_68  = 8.7;
                $j_66  = 8.9;
                $j_64  = 9.1;
                $j_62  = 9.3;
                $j_60  = 9.5;
                $b_50  = 9.7;
                $b_40  = 9.9;
                $b_30  = 10.1;
                $b_20  = 10.3;
                $b_10  = 10.5;
            }else{
                $y_100 = 7.8;
                $y_95  = 7.9;
                $y_90  = 8.0;
                $l_85  = 8.3;
                $l_80  = 8.6;
                $j_78  = 8.8;
                $j_76  = 9.0;
                $j_74  = 9.2;
                $j_72  = 9.4;
                $j_70  = 9.6;
                $j_68  = 9.8;
                $j_66  = 10.0;
                $j_64  = 10.2;
                $j_62  = 10.4;
                $j_60  = 10.6;
                $b_50  = 10.8;
                $b_40  = 11.0;
                $b_30  = 11.2;
                $b_20  = 11.4;
                $b_10  = 11.6;
            }
            break;
        case 11:
            if($sex == 1){
                $y_100 = 7.0;
                $y_95  = 7.1;
                $y_90  = 7.2;
                $l_85  = 7.3;
                $l_80  = 7.4;
                $j_78  = 7.6;
                $j_76  = 7.8;
                $j_74  = 8.0;
                $j_72  = 8.2;
                $j_70  = 8.4;
                $j_68  = 8.6;
                $j_66  = 8.8;
                $j_64  = 9.0;
                $j_62  = 9.2;
                $j_60  = 9.4;
                $b_50  = 9.6;
                $b_40  = 9.8;
                $b_30  = 10.0;
                $b_20  = 10.2;
                $b_10  = 10.4;
            }else{
                $y_100 = 7.7;
                $y_95  = 7.8;
                $y_90  = 7.9;
                $l_85  = 8.2;
                $l_80  = 8.5;
                $j_78  = 8.7;
                $j_76  = 8.9;
                $j_74  = 9.1;
                $j_72  = 9.3;
                $j_70  = 9.5;
                $j_68  = 9.7;
                $j_66  = 9.9;
                $j_64  = 10.1;
                $j_62  = 10.3;
                $j_60  = 10.5;
                $b_50  = 10.7;
                $b_40  = 10.9;
                $b_30  = 11.1;
                $b_20  = 11.3;
                $b_10  = 11.5;
            }
            break;
        case 12:
            if($sex == 1){
                $y_100 = 6.8;
                $y_95  = 6.9;
                $y_90  = 7.0;
                $l_85  = 7.1;
                $l_80  = 7.2;
                $j_78  = 7.4;
                $j_76  = 7.6;
                $j_74  = 7.8;
                $j_72  = 8.0;
                $j_70  = 8.2;
                $j_68  = 8.4;
                $j_66  = 8.6;
                $j_64  = 8.8;
                $j_62  = 9.0;
                $j_60  = 9.2;
                $b_50  = 9.4;
                $b_40  = 9.6;
                $b_30  = 9.8;
                $b_20  = 10.0;
                $b_10  = 10.2;
            }else{
                $y_100 = 7.6;
                $y_95  = 7.7;
                $y_90  = 7.8;
                $l_85  = 8.1;
                $l_80  = 8.4;
                $j_78  = 8.6;
                $j_76  = 8.8;
                $j_74  = 9.0;
                $j_72  = 9.2;
                $j_70  = 9.4;
                $j_68  = 9.6;
                $j_66  = 9.8;
                $j_64  = 10.0;
                $j_62  = 10.2;
                $j_60  = 10.4;
                $b_50  = 10.6;
                $b_40  = 10.8;
                $b_30  = 11.0;
                $b_20  = 11.2;
                $b_10  = 11.4;
            }
            break;
        case 13:
        case 14:
            if($sex == 1){
                $y_100 = 6.7;
                $y_95  = 6.8;
                $y_90  = 6.9;
                $l_85  = 7.0;
                $l_80  = 7.1;
                $j_78  = 7.3;
                $j_76  = 7.5;
                $j_74  = 7.7;
                $j_72  = 7.9;
                $j_70  = 8.1;
                $j_68  = 8.3;
                $j_66  = 8.5;
                $j_64  = 8.7;
                $j_62  = 8.9;
                $j_60  = 9.1;
                $b_50  = 9.3;
                $b_40  = 9.5;
                $b_30  = 9.7;
                $b_20  = 9.9;
                $b_10  = 10.1;
            }else{
                $y_100 = 7.5;
                $y_95  = 7.6;
                $y_90  = 7.7;
                $l_85  = 8.0;
                $l_80  = 8.3;
                $j_78  = 8.5;
                $j_76  = 8.7;
                $j_74  = 8.9;
                $j_72  = 9.1;
                $j_70  = 9.3;
                $j_68  = 9.5;
                $j_66  = 9.7;
                $j_64  = 9.9;
                $j_62  = 10.1;
                $j_60  = 10.3;
                $b_50  = 10.5;
                $b_40  = 10.7;
                $b_30  = 10.9;
                $b_20  = 11.1;
                $b_10  = 11.3;
            }
            break;
        case 15:
        case 16:
            if($sex == 1){
                $y_100 = 6.6;
                $y_95  = 6.7;
                $y_90  = 6.8;
                $l_85  = 6.9;
                $l_80  = 7.0;
                $j_78  = 7.2;
                $j_76  = 7.4;
                $j_74  = 7.6;
                $j_72  = 7.8;
                $j_70  = 8.0;
                $j_68  = 8.2;
                $j_66  = 8.4;
                $j_64  = 8.6;
                $j_62  = 8.8;
                $j_60  = 9.0;
                $b_50  = 9.2;
                $b_40  = 9.4;
                $b_30  = 9.6;
                $b_20  = 9.8;
                $b_10  = 10.0;
            }else{
                $y_100 = 7.4;
                $y_95  = 7.5;
                $y_90  = 7.6;
                $l_85  = 7.9;
                $l_80  = 8.2;
                $j_78  = 8.4;
                $j_76  = 8.6;
                $j_74  = 8.8;
                $j_72  = 9.0;
                $j_70  = 9.2;
                $j_68  = 9.4;
                $j_66  = 9.6;
                $j_64  = 9.8;
                $j_62  = 10.0;
                $j_60  = 10.2;
                $b_50  = 10.4;
                $b_40  = 10.6;
                $b_30  = 10.8;
                $b_20  = 11.0;
                $b_10  = 11.2;
            }
            break;
        default:
            return ['num'=>0,'name'=>'未知'];
    }
    if($num>$b_10){
        $return['name'] = '不及格';
        $return['num']  = 0;
    }else if($num <= $b_10 && $num > $b_20){
        $return['name'] = '不及格';
        $return['num']  = 10;
    }else if($num <= $b_20 && $num > $b_30){
        $return['name'] = '不及格';
        $return['num']  = 20;
    }else if($num <= $b_30 && $num > $b_40){
        $return['name'] = '不及格';
        $return['num']  = 30;
    }else if($num <= $b_40 && $num > $b_50){
        $return['name'] = '不及格';
        $return['num']  = 40;
    }else if($num <= $b_50 && $num > $j_60){
        $return['name'] = '不及格';
        $return['num']  = 50;
    }else if($num <= $j_60 && $num > $j_62){
        $return['name'] = '及格';
        $return['num']  = 60;
    }else if($num <= $j_62 && $num > $j_64){
        $return['name'] = '及格';
        $return['num']  = 62;
    }else if($num <= $j_64 && $num > $j_66){
        $return['name'] = '及格';
        $return['num']  = 64;
    }else if($num <= $j_66 && $num > $j_68){
        $return['name'] = '及格';
        $return['num']  = 66;
    }else if($num <= $j_68 && $num > $j_70){
        $return['name'] = '及格';
        $return['num']  = 68;
    }else if($num <= $j_70 && $num > $j_72){
        $return['name'] = '及格';
        $return['num']  = 70;
    }else if($num <= $j_72 && $num > $j_74){
        $return['name'] = '及格';
        $return['num']  = 72;
    }else if($num <= $j_74 && $num > $j_76){
        $return['name'] = '及格';
        $return['num']  = 74;
    }else if($num <= $j_76 && $num > $j_78){
        $return['name'] = '及格';
        $return['num']  = 76;
    }else if($num <= $j_78 && $num > $l_80){
        $return['name'] = '及格';
        $return['num']  = 78;
    }else if($num <= $l_80 && $num > $l_85){
        $return['name'] = '良好';
        $return['num']  = 80;
    }else if($num <= $l_85 && $num > $y_90){
        $return['name'] = '良好';
        $return['num']  = 85;
    }else if($num <= $y_90 && $num > $y_95){
        $return['name'] = '优秀';
        $return['num']  = 90;
    }else if($num <= $y_95 && $num > $y_100){
        $return['name'] = '优秀';
        $return['num']  = 95;
    }else if($num <= $y_100 ){
        $return['name'] = '优秀';
        $return['num']  = 100;
    }
    return $return;
}

//坐位体前屈
function sport_sit_and_reach_result($id,$num,$sex = 1)
{
    $return = [
        'name'  => '未知',
        'num'   => 0,
    ];
    switch ($id){
        case 1:
            if($sex == 1){
                $y_100 = 16.1;
                $y_95  = 14.6;
                $y_90  = 13.0;
                $l_85  = 12.0;
                $l_80  = 11.0;
                $j_78  = 9.9;
                $j_76  = 8.8;
                $j_74  = 7.7;
                $j_72  = 6.6;
                $j_70  = 5.5;
                $j_68  = 4.4;
                $j_66  = 3.3;
                $j_64  = 2.2;
                $j_62  = 1.1;
                $j_60  = 0.0;
                $b_50  = -0.8;
                $b_40  = -1.6;
                $b_30  = -2.4;
                $b_20  = -3.2;
                $b_10  = -4.0;
            }else{
                $y_100 = 18.6;
                $y_95  = 17.3;
                $y_90  = 16.0;
                $l_85  = 14.7;
                $l_80  = 13.4;
                $j_78  = 12.3;
                $j_76  = 11.2;
                $j_74  = 10.1;
                $j_72  = 9.0;
                $j_70  = 7.9;
                $j_68  = 6.8;
                $j_66  = 5.7;
                $j_64  = 4.6;
                $j_62  = 3.5;
                $j_60  = 2.4;
                $b_50  = 1.6;
                $b_40  = 0.8;
                $b_30  = 0.0;
                $b_20  = -0.8;
                $b_10  = -1.6;
            }
            break;
        case 2:
            if($sex == 1){
                $y_100 = 16.2;
                $y_95  = 14.7;
                $y_90  = 13.2;
                $l_85  = 11.9;
                $l_80  = 10.6;
                $j_78  = 9.5;
                $j_76  = 8.4;
                $j_74  = 7.3;
                $j_72  = 6.2;
                $j_70  = 5.1;
                $j_68  = 4.0;
                $j_66  = 2.9;
                $j_64  = 1.8;
                $j_62  = 0.7;
                $j_60  = -0.4;
                $b_50  = -1.2;
                $b_40  = -2.0;
                $b_30  = -2.8;
                $b_20  = -3.6;
                $b_10  = -4.4;
            }else{
                $y_100 = 18.9;
                $y_95  = 17.6;
                $y_90  = 16.3;
                $l_85  = 14.8;
                $l_80  = 13.3;
                $j_78  = 12.2;
                $j_76  = 11.1;
                $j_74  = 10.0;
                $j_72  = 8.9;
                $j_70  = 7.8;
                $j_68  = 6.7;
                $j_66  = 5.6;
                $j_64  = 4.5;
                $j_62  = 3.4;
                $j_60  = 2.3;
                $b_50  = 1.5;
                $b_40  = 0.7;
                $b_30  = -0.1;
                $b_20  = -0.9;
                $b_10  = -1.7;
            }
            break;
        case 3:
            if($sex == 1){
                $y_100 = 16.3;
                $y_95  = 14.9;
                $y_90  = 13.4;
                $l_85  = 11.8;
                $l_80  = 10.2;
                $j_78  = 9.1;
                $j_76  = 8.0;
                $j_74  = 6.9;
                $j_72  = 5.8;
                $j_70  = 4.7;
                $j_68  = 3.6;
                $j_66  = 2.5;
                $j_64  = 1.4;
                $j_62  = 0.3;
                $j_60  = -0.8;
                $b_50  = -1.6;
                $b_40  = -2.4;
                $b_30  = -3.2;
                $b_20  = -4.0;
                $b_10  = -4.8;
            }else{
                $y_100 = 19.2;
                $y_95  = 17.9;
                $y_90  = 16.6;
                $l_85  = 14.9;
                $l_80  = 13.2;
                $j_78  = 12.1;
                $j_76  = 11.0;
                $j_74  = 9.9;
                $j_72  = 8.8;
                $j_70  = 7.7;
                $j_68  = 6.6;
                $j_66  = 5.5;
                $j_64  = 4.4;
                $j_62  = 3.3;
                $j_60  = 2.2;
                $b_50  = 1.4;
                $b_40  = 0.6;
                $b_30  = -0.2;
                $b_20  = -1.0;
                $b_10  = -1.8;
            }
            break;
        case 4:
            if($sex == 1){
                $y_100 = 16.4;
                $y_95  = 15.0;
                $y_90  = 13.6;
                $l_85  = 11.7;
                $l_80  = 9.8;
                $j_78  = 8.6;
                $j_76  = 7.4;
                $j_74  = 6.2;
                $j_72  = 5.0;
                $j_70  = 3.8;
                $j_68  = 2.6;
                $j_66  = 1.4;
                $j_64  = 0.2;
                $j_62  = -1.0;
                $j_60  = -2.2;
                $b_50  = -3.2;
                $b_40  = -4.2;
                $b_30  = -5.2;
                $b_20  = -6.2;
                $b_10  = -7.2;
            }else{
                $y_100 = 19.5;
                $y_95  = 18.1;
                $y_90  = 16.9;
                $l_85  = 15.0;
                $l_80  = 13.1;
                $j_78  = 12.0;
                $j_76  = 10.9;
                $j_74  = 9.8;
                $j_72  = 8.7;
                $j_70  = 7.6;
                $j_68  = 6.5;
                $j_66  = 5.4;
                $j_64  = 4.3;
                $j_62  = 3.2;
                $j_60  = 2.1;
                $b_50  = 1.3;
                $b_40  = 0.5;
                $b_30  = -0.3;
                $b_20  = -1.1;
                $b_10  = -1.9;
            }
            break;
        case 5:
            if($sex == 1){
                $y_100 = 16.5;
                $y_95  = 15.2;
                $y_90  = 13.8;
                $l_85  = 11.6;
                $l_80  = 9.4;
                $j_78  = 8.2;
                $j_76  = 7.0;
                $j_74  = 5.8;
                $j_72  = 4.6;
                $j_70  = 3.4;
                $j_68  = 2.2;
                $j_66  = 1.0;
                $j_64  = -0.2;
                $j_62  = -1.4;
                $j_60  = -2.6;
                $b_50  = -3.6;
                $b_40  = -4.6;
                $b_30  = -5.6;
                $b_20  = -6.6;
                $b_10  = -7.6;
            }else{
                $y_100 = 19.8;
                $y_95  = 18.5;
                $y_90  = 17.2;
                $l_85  = 15.1;
                $l_80  = 13.0;
                $j_78  = 11.9;
                $j_76  = 10.8;
                $j_74  = 9.7;
                $j_72  = 8.6;
                $j_70  = 7.5;
                $j_68  = 6.4;
                $j_66  = 5.3;
                $j_64  = 4.2;
                $j_62  = 3.1;
                $j_60  = 2.0;
                $b_50  = 1.2;
                $b_40  = 0.4;
                $b_30  = -0.4;
                $b_20  = -1.2;
                $b_10  = -2.0;
            }
            break;
        case 6:
            if($sex == 1){
                $y_100 = 16.6;
                $y_95  = 15.3;
                $y_90  = 14.0;
                $l_85  = 11.5;
                $l_80  = 9.0;
                $j_78  = 7.7;
                $j_76  = 6.4;
                $j_74  = 5.1;
                $j_72  = 3.8;
                $j_70  = 2.5;
                $j_68  = 1.2;
                $j_66  = -0.1;
                $j_64  = -1.4;
                $j_62  = -2.7;
                $j_60  = -4.0;
                $b_50  = -5.0;
                $b_40  = -6.0;
                $b_30  = -7.0;
                $b_20  = -8.0;
                $b_10  = -9.0;
            }else{
                $y_100 = 19.9;
                $y_95  = 18.7;
                $y_90  = 17.5;
                $l_85  = 15.2;
                $l_80  = 12.9;
                $j_78  = 11.8;
                $j_76  = 10.7;
                $j_74  = 9.6;
                $j_72  = 8.5;
                $j_70  = 7.4;
                $j_68  = 6.3;
                $j_66  = 5.2;
                $j_64  = 4.1;
                $j_62  = 3.0;
                $j_60  = 1.9;
                $b_50  = 1.1;
                $b_40  = 0.3;
                $b_30  = -0.5;
                $b_20  = -1.3;
                $b_10  = -2.1;
            }
            break;
        case 7:
            if($sex == 1){
                $y_100 = 17.6;
                $y_95  = 15.9;
                $y_90  = 14.2;
                $l_85  = 12.3;
                $l_80  = 10.4;
                $j_78  = 9.1;
                $j_76  = 7.8;
                $j_74  = 6.5;
                $j_72  = 5.2;
                $j_70  = 3.9;
                $j_68  = 2.6;
                $j_66  = 1.3;
                $j_64  = 0.0;
                $j_62  = -1.3;
                $j_60  = -2.6;
                $b_50  = -3.8;
                $b_40  = -5.0;
                $b_30  = -6.2;
                $b_20  = -7.4;
                $b_10  = -8.6;
            }else{
                $y_100 = 21.8;
                $y_95  = 20.1;
                $y_90  = 18.4;
                $l_85  = 16.7;
                $l_80  = 15.0;
                $j_78  = 13.7;
                $j_76  = 12.4;
                $j_74  = 11.1;
                $j_72  = 9.8;
                $j_70  = 8.5;
                $j_68  = 7.2;
                $j_66  = 5.9;
                $j_64  = 4.6;
                $j_62  = 3.3;
                $j_60  = 2.0;
                $b_50  = 1.2;
                $b_40  = 0.4;
                $b_30  = -0.4;
                $b_20  = -1.2;
                $b_10  = -2.0;
            }
            break;
        case 8:
            if($sex == 1){
                $y_100 = 19.6;
                $y_95  = 17.7;
                $y_90  = 15.8;
                $l_85  = 13.7;
                $l_80  = 11.6;
                $j_78  = 10.3;
                $j_76  = 9.0;
                $j_74  = 7.7;
                $j_72  = 6.4;
                $j_70  = 5.1;
                $j_68  = 3.8;
                $j_66  = 2.5;
                $j_64  = 1.2;
                $j_62  = -0.1;
                $j_60  = -1.4;
                $b_50  = -2.6;
                $b_40  = -3.8;
                $b_30  = -5.0;
                $b_20  = -6.2;
                $b_10  = -7.4;
            }else{
                $y_100 = 22.7;
                $y_95  = 21.0;
                $y_90  = 19.3;
                $l_85  = 17.6;
                $l_80  = 15.9;
                $j_78  = 14.6;
                $j_76  = 13.3;
                $j_74  = 12.0;
                $j_72  = 10.7;
                $j_70  = 9.4;
                $j_68  = 8.1;
                $j_66  = 6.8;
                $j_64  = 5.5;
                $j_62  = 4.2;
                $j_60  = 2.9;
                $b_50  = 2.1;
                $b_40  = 1.3;
                $b_30  = 0.5;
                $b_20  = -0.3;
                $b_10  = -1.1;
            }
            break;
        case 9:
            if($sex == 1){
                $y_100 = 21.6;
                $y_95  = 19.7;
                $y_90  = 17.8;
                $l_85  = 15.8;
                $l_80  = 13.8;
                $j_78  = 12.4;
                $j_76  = 11.0;
                $j_74  = 9.6;
                $j_72  = 8.2;
                $j_70  = 6.8;
                $j_68  = 5.4;
                $j_66  = 4.0;
                $j_64  = 2.6;
                $j_62  = 1.2;
                $j_60  = -0.2;
                $b_50  = -1.4;
                $b_40  = -2.6;
                $b_30  = -3.8;
                $b_20  = -5.0;
                $b_10  = -6.2;
            }else{
                $y_100 = 23.5;
                $y_95  = 21.8;
                $y_90  = 20.1;
                $l_85  = 18.4;
                $l_80  = 16.7;
                $j_78  = 15.4;
                $j_76  = 14.1;
                $j_74  = 12.8;
                $j_72  = 11.5;
                $j_70  = 10.2;
                $j_68  = 8.9;
                $j_66  = 7.6;
                $j_64  = 6.3;
                $j_62  = 5.0;
                $j_60  = 3.7;
                $b_50  = 2.9;
                $b_40  = 2.1;
                $b_30  = 1.3;
                $b_20  = 0.5;
                $b_10  = -0.3;
            }
            break;
        case 10:
            if($sex == 1){
                $y_100 = 23.6;
                $y_95  = 21.5;
                $y_90  = 19.4;
                $l_85  = 17.2;
                $l_80  = 15.0;
                $j_78  = 13.6;
                $j_76  = 12.2;
                $j_74  = 10.8;
                $j_72  = 9.4;
                $j_70  = 8.0;
                $j_68  = 6.6;
                $j_66  = 5.2;
                $j_64  = 3.8;
                $j_62  = 2.4;
                $j_60  = 1.0;
                $b_50  = 0.0;
                $b_40  = -1.0;
                $b_30  = -2.0;
                $b_20  = -3.0;
                $b_10  = -4.0;
            }else{
                $y_100 = 24.2;
                $y_95  = 22.5;
                $y_90  = 20.8;
                $l_85  = 19.1;
                $l_80  = 17.4;
                $j_78  = 16.1;
                $j_76  = 14.8;
                $j_74  = 13.5;
                $j_72  = 12.2;
                $j_70  = 10.9;
                $j_68  = 9.6;
                $j_66  = 8.3;
                $j_64  = 7.0;
                $j_62  = 5.7;
                $j_60  = 4.4;
                $b_50  = 3.6;
                $b_40  = 2.8;
                $b_30  = 2.0;
                $b_20  = 1.2;
                $b_10  = 0.4;
            }
            break;
        case 11:
            if($sex == 1){
                $y_100 = 24.3;
                $y_95  = 22.4;
                $y_90  = 20.5;
                $l_85  = 18.3;
                $l_80  = 16.1;
                $j_78  = 14.7;
                $j_76  = 13.3;
                $j_74  = 11.9;
                $j_72  = 10.5;
                $j_70  = 9.1;
                $j_68  = 7.7;
                $j_66  = 6.3;
                $j_64  = 4.9;
                $j_62  = 3.5;
                $j_60  = 2.1;
                $b_50  = 1.1;
                $b_40  = 0.1;
                $b_30  = -0.9;
                $b_20  = -1.9;
                $b_10  = -2.9;
            }else{
                $y_100 = 24.8;
                $y_95  = 23.1;
                $y_90  = 21.4;
                $l_85  = 19.7;
                $l_80  = 18.0;
                $j_78  = 16.7;
                $j_76  = 15.4;
                $j_74  = 14.1;
                $j_72  = 12.8;
                $j_70  = 11.5;
                $j_68  = 10.2;
                $j_66  = 8.9;
                $j_64  = 7.6;
                $j_62  = 6.3;
                $j_60  = 5.0;
                $b_50  = 4.2;
                $b_40  = 3.4;
                $b_30  = 2.6;
                $b_20  = 1.8;
                $b_10  = 1.0;
            }
            break;
        case 12:
            if($sex == 1){
                $y_100 = 24.6;
                $y_95  = 22.8;
                $y_90  = 21.0;
                $l_85  = 19.1;
                $l_80  = 17.2;
                $j_78  = 15.8;
                $j_76  = 14.4;
                $j_74  = 13.0;
                $j_72  = 11.6;
                $j_70  = 10.2;
                $j_68  = 8.8;
                $j_66  = 7.4;
                $j_64  = 6.0;
                $j_62  = 4.6;
                $j_60  = 3.2;
                $b_50  = 2.2;
                $b_40  = 1.2;
                $b_30  = 0.2;
                $b_20  = -0.8;
                $b_10  = -1.8;
            }else{
                $y_100 = 25.3;
                $y_95  = 23.6;
                $y_90  = 21.9;
                $l_85  = 20.2;
                $l_80  = 18.5;
                $j_78  = 17.2;
                $j_76  = 15.9;
                $j_74  = 14.6;
                $j_72  = 13.3;
                $j_70  = 12.0;
                $j_68  = 10.7;
                $j_66  = 9.4;
                $j_64  = 8.1;
                $j_62  = 6.8;
                $j_60  = 5.5;
                $b_50  = 4.7;
                $b_40  = 3.9;
                $b_30  = 3.1;
                $b_20  = 2.3;
                $b_10  = 1.5;
            }
            break;
        case 13:
        case 14:
            if($sex == 1){
                $y_100 = 24.9;
                $y_95  = 23.1;
                $y_90  = 21.3;
                $l_85  = 19.5;
                $l_80  = 17.7;
                $j_78  = 16.3;
                $j_76  = 14.9;
                $j_74  = 13.5;
                $j_72  = 12.1;
                $j_70  = 10.7;
                $j_68  = 9.3;
                $j_66  = 7.9;
                $j_64  = 6.5;
                $j_62  = 5.1;
                $j_60  = 3.7;
                $b_50  = 2.7;
                $b_40  = 1.7;
                $b_30  = 0.7;
                $b_20  = -0.3;
                $b_10  = -1.3;
            }else{
                $y_100 = 25.8;
                $y_95  = 24.0;
                $y_90  = 22.2;
                $l_85  = 20.6;
                $l_80  = 19.0;
                $j_78  = 17.7;
                $j_76  = 16.4;
                $j_74  = 15.1;
                $j_72  = 13.8;
                $j_70  = 12.5;
                $j_68  = 11.2;
                $j_66  = 9.9;
                $j_64  = 8.6;
                $j_62  = 7.3;
                $j_60  = 6.0;
                $b_50  = 5.2;
                $b_40  = 4.4;
                $b_30  = 3.6;
                $b_20  = 2.8;
                $b_10  = 2.0;
            }
            break;
        case 15:
        case 16:
            if($sex == 1){
                $y_100 = 25.1;
                $y_95  = 23.3;
                $y_90  = 21.5;
                $l_85  = 19.9;
                $l_80  = 18.2;
                $j_78  = 16.8;
                $j_76  = 15.4;
                $j_74  = 14.0;
                $j_72  = 12.6;
                $j_70  = 11.2;
                $j_68  = 9.8;
                $j_66  = 8.4;
                $j_64  = 7.0;
                $j_62  = 5.6;
                $j_60  = 4.2;
                $b_50  = 3.2;
                $b_40  = 2.2;
                $b_30  = 1.2;
                $b_20  = 0.2;
                $b_10  = -0.8;
            }else{
                $y_100 = 26.3;
                $y_95  = 24.4;
                $y_90  = 22.4;
                $l_85  = 21.0;
                $l_80  = 19.5;
                $j_78  = 18.2;
                $j_76  = 16.9;
                $j_74  = 15.6;
                $j_72  = 14.3;
                $j_70  = 13.0;
                $j_68  = 11.7;
                $j_66  = 10.4;
                $j_64  = 9.1;
                $j_62  = 7.8;
                $j_60  = 6.5;
                $b_50  = 5.7;
                $b_40  = 4.9;
                $b_30  = 4.1;
                $b_20  = 3.3;
                $b_10  = 2.5;
            }
            break;
        default:
            return ['num'=>0,'name'=>'未知'];
    }
    if($num<$b_10){
        $return['name'] = '不及格';
        $return['num']  = 0;
    }else if($num >= $b_10 && $num < $b_20){
        $return['name'] = '不及格';
        $return['num']  = 10;
    }else if($num >= $b_20 && $num < $b_30){
        $return['name'] = '不及格';
        $return['num']  = 20;
    }else if($num >= $b_30 && $num < $b_40){
        $return['name'] = '不及格';
        $return['num']  = 30;
    }else if($num >= $b_40 && $num < $b_50){
        $return['name'] = '不及格';
        $return['num']  = 40;
    }else if($num >= $b_50 && $num < $j_60){
        $return['name'] = '不及格';
        $return['num']  = 50;
    }else if($num >= $j_60 && $num < $j_62){
        $return['name'] = '及格';
        $return['num']  = 60;
    }else if($num >= $j_62 && $num < $j_64){
        $return['name'] = '及格';
        $return['num']  = 62;
    }else if($num >= $j_64 && $num < $j_66){
        $return['name'] = '及格';
        $return['num']  = 64;
    }else if($num >= $j_66 && $num < $j_68){
        $return['name'] = '及格';
        $return['num']  = 66;
    }else if($num >= $j_68 && $num < $j_70){
        $return['name'] = '及格';
        $return['num']  = 68;
    }else if($num >= $j_70 && $num < $j_72){
        $return['name'] = '及格';
        $return['num']  = 70;
    }else if($num >= $j_72 && $num < $j_74){
        $return['name'] = '及格';
        $return['num']  = 72;
    }else if($num >= $j_74 && $num < $j_76){
        $return['name'] = '及格';
        $return['num']  = 74;
    }else if($num >= $j_76 && $num < $j_78){
        $return['name'] = '及格';
        $return['num']  = 76;
    }else if($num >= $j_78 && $num < $l_80){
        $return['name'] = '及格';
        $return['num']  = 78;
    }else if($num >= $l_80 && $num < $l_85){
        $return['name'] = '良好';
        $return['num']  = 80;
    }else if($num >= $l_85 && $num < $y_90){
        $return['name'] = '良好';
        $return['num']  = 85;
    }else if($num >= $y_90 && $num < $y_95){
        $return['name'] = '优秀';
        $return['num']  = 90;
    }else if($num >= $y_95 && $num < $y_100){
        $return['name'] = '优秀';
        $return['num']  = 95;
    }else if($num >= $y_100 ){
        $return['name'] = '优秀';
        $return['num']  = 100;
    }
    return $return;
}

//一分钟跳绳
function sport_skip_result($id,$num,$sex = 1)
{
    $return = [
        'name'  => '未知',
        'num'   => 0,
    ];
    switch ($id){
        case 1:
            if($sex == 1){
                $y_100 = 109;
                $y_95  = 104;
                $y_90  = 99;
                $l_85  = 93;
                $l_80  = 87;
                $j_78  = 80;
                $j_76  = 73;
                $j_74  = 66;
                $j_72  = 59;
                $j_70  = 52;
                $j_68  = 45;
                $j_66  = 38;
                $j_64  = 31;
                $j_62  = 24;
                $j_60  = 17;
                $b_50  = 14;
                $b_40  = 11;
                $b_30  = 8;
                $b_20  = 5;
                $b_10  = 2;
            }else{
                $y_100 = 117;
                $y_95  = 110;
                $y_90  = 103;
                $l_85  = 95;
                $l_80  = 87;
                $j_78  = 80;
                $j_76  = 73;
                $j_74  = 66;
                $j_72  = 59;
                $j_70  = 52;
                $j_68  = 45;
                $j_66  = 38;
                $j_64  = 31;
                $j_62  = 24;
                $j_60  = 17;
                $b_50  = 14;
                $b_40  = 11;
                $b_30  = 8;
                $b_20  = 5;
                $b_10  = 2;
            }
            break;
        case 2:
            if($sex == 1){
                $y_100 = 117;
                $y_95  = 112;
                $y_90  = 107;
                $l_85  = 101;
                $l_80  = 95;
                $j_78  = 88;
                $j_76  = 81;
                $j_74  = 74;
                $j_72  = 67;
                $j_70  = 60;
                $j_68  = 53;
                $j_66  = 46;
                $j_64  = 39;
                $j_62  = 32;
                $j_60  = 25;
                $b_50  = 22;
                $b_40  = 19;
                $b_30  = 16;
                $b_20  = 13;
                $b_10  = 10;
            }else{
                $y_100 = 127;
                $y_95  = 120;
                $y_90  = 113;
                $l_85  = 105;
                $l_80  = 97;
                $j_78  = 90;
                $j_76  = 83;
                $j_74  = 76;
                $j_72  = 69;
                $j_70  = 62;
                $j_68  = 55;
                $j_66  = 48;
                $j_64  = 41;
                $j_62  = 34;
                $j_60  = 27;
                $b_50  = 24;
                $b_40  = 21;
                $b_30  = 18;
                $b_20  = 15;
                $b_10  = 12;
            }
            break;
        case 3:
            if($sex == 1){
                $y_100 = 126;
                $y_95  = 121;
                $y_90  = 116;
                $l_85  = 110;
                $l_80  = 104;
                $j_78  = 97;
                $j_76  = 90;
                $j_74  = 83;
                $j_72  = 76;
                $j_70  = 69;
                $j_68  = 62;
                $j_66  = 55;
                $j_64  = 48;
                $j_62  = 41;
                $j_60  = 34;
                $b_50  = 31;
                $b_40  = 28;
                $b_30  = 25;
                $b_20  = 22;
                $b_10  = 19;
            }else{
                $y_100 = 139;
                $y_95  = 132;
                $y_90  = 125;
                $l_85  = 117;
                $l_80  = 109;
                $j_78  = 102;
                $j_76  = 95;
                $j_74  = 88;
                $j_72  = 81;
                $j_70  = 74;
                $j_68  = 67;
                $j_66  = 60;
                $j_64  = 53;
                $j_62  = 46;
                $j_60  = 39;
                $b_50  = 36;
                $b_40  = 33;
                $b_30  = 30;
                $b_20  = 27;
                $b_10  = 24;
            }
            break;
        case 4:
            if($sex == 1){
                $y_100 = 137;
                $y_95  = 132;
                $y_90  = 127;
                $l_85  = 121;
                $l_80  = 115;
                $j_78  = 108;
                $j_76  = 101;
                $j_74  = 94;
                $j_72  = 87;
                $j_70  = 80;
                $j_68  = 73;
                $j_66  = 66;
                $j_64  = 59;
                $j_62  = 52;
                $j_60  = 45;
                $b_50  = 42;
                $b_40  = 39;
                $b_30  = 36;
                $b_20  = 33;
                $b_10  = 30;
            }else{
                $y_100 = 149;
                $y_95  = 142;
                $y_90  = 135;
                $l_85  = 127;
                $l_80  = 119;
                $j_78  = 112;
                $j_76  = 105;
                $j_74  = 98;
                $j_72  = 91;
                $j_70  = 84;
                $j_68  = 77;
                $j_66  = 70;
                $j_64  = 63;
                $j_62  = 56;
                $j_60  = 49;
                $b_50  = 46;
                $b_40  = 43;
                $b_30  = 40;
                $b_20  = 37;
                $b_10  = 34;
            }
            break;
        case 5:
            if($sex == 1){
                $y_100 = 148;
                $y_95  = 143;
                $y_90  = 138;
                $l_85  = 132;
                $l_80  = 126;
                $j_78  = 119;
                $j_76  = 112;
                $j_74  = 105;
                $j_72  = 98;
                $j_70  = 91;
                $j_68  = 84;
                $j_66  = 77;
                $j_64  = 70;
                $j_62  = 63;
                $j_60  = 56;
                $b_50  = 53;
                $b_40  = 50;
                $b_30  = 47;
                $b_20  = 44;
                $b_10  = 41;
            }else{
                $y_100 = 158;
                $y_95  = 151;
                $y_90  = 144;
                $l_85  = 136;
                $l_80  = 128;
                $j_78  = 121;
                $j_76  = 114;
                $j_74  = 107;
                $j_72  = 100;
                $j_70  = 93;
                $j_68  = 86;
                $j_66  = 79;
                $j_64  = 72;
                $j_62  = 65;
                $j_60  = 58;
                $b_50  = 55;
                $b_40  = 52;
                $b_30  = 49;
                $b_20  = 46;
                $b_10  = 43;
            }
            break;
        case 6:
            if($sex == 1){
                $y_100 = 157;
                $y_95  = 152;
                $y_90  = 147;
                $l_85  = 141;
                $l_80  = 135;
                $j_78  = 128;
                $j_76  = 121;
                $j_74  = 114;
                $j_72  = 107;
                $j_70  = 100;
                $j_68  = 93;
                $j_66  = 86;
                $j_64  = 79;
                $j_62  = 72;
                $j_60  = 65;
                $b_50  = 62;
                $b_40  = 59;
                $b_30  = 56;
                $b_20  = 53;
                $b_10  = 50;
            }else{
                $y_100 = 166;
                $y_95  = 159;
                $y_90  = 152;
                $l_85  = 144;
                $l_80  = 136;
                $j_78  = 129;
                $j_76  = 122;
                $j_74  = 115;
                $j_72  = 108;
                $j_70  = 101;
                $j_68  = 94;
                $j_66  = 87;
                $j_64  = 80;
                $j_62  = 73;
                $j_60  = 66;
                $b_50  = 63;
                $b_40  = 60;
                $b_30  = 57;
                $b_20  = 54;
                $b_10  = 51;
            }
            break;
        default:
            return ['num'=>0,'name'=>'未知'];
    }
    if($num<$b_10){
        $return['name'] = '不及格';
        $return['num']  = 0;
    }else if($num >= $b_10 && $num < $b_20){
        $return['name'] = '不及格';
        $return['num']  = 10;
    }else if($num >= $b_20 && $num < $b_30){
        $return['name'] = '不及格';
        $return['num']  = 20;
    }else if($num >= $b_30 && $num < $b_40){
        $return['name'] = '不及格';
        $return['num']  = 30;
    }else if($num >= $b_40 && $num < $b_50){
        $return['name'] = '不及格';
        $return['num']  = 40;
    }else if($num >= $b_50 && $num < $j_60){
        $return['name'] = '不及格';
        $return['num']  = 50;
    }else if($num >= $j_60 && $num < $j_62){
        $return['name'] = '及格';
        $return['num']  = 60;
    }else if($num >= $j_62 && $num < $j_64){
        $return['name'] = '及格';
        $return['num']  = 62;
    }else if($num >= $j_64 && $num < $j_66){
        $return['name'] = '及格';
        $return['num']  = 64;
    }else if($num >= $j_66 && $num < $j_68){
        $return['name'] = '及格';
        $return['num']  = 66;
    }else if($num >= $j_68 && $num < $j_70){
        $return['name'] = '及格';
        $return['num']  = 68;
    }else if($num >= $j_70 && $num < $j_72){
        $return['name'] = '及格';
        $return['num']  = 70;
    }else if($num >= $j_72 && $num < $j_74){
        $return['name'] = '及格';
        $return['num']  = 72;
    }else if($num >= $j_74 && $num < $j_76){
        $return['name'] = '及格';
        $return['num']  = 74;
    }else if($num >= $j_76 && $num < $j_78){
        $return['name'] = '及格';
        $return['num']  = 76;
    }else if($num >= $j_78 && $num < $l_80){
        $return['name'] = '及格';
        $return['num']  = 78;
    }else if($num >= $l_80 && $num < $l_85){
        $return['name'] = '良好';
        $return['num']  = 80;
    }else if($num >= $l_85 && $num < $y_90){
        $return['name'] = '良好';
        $return['num']  = 85;
    }else if($num >= $y_90 && $num < $y_95){
        $return['name'] = '优秀';
        $return['num']  = 90;
    }else if($num >= $y_95 && $num < $y_100){
        $return['name'] = '优秀';
        $return['num']  = 95;
    }else if($num >= $y_100 ){
        $return['name'] = '优秀';
        $return['num']  = 100;
    }
    //加分
    if($return['num'] == 100){
        $cha = intval($y_100 - $num);
        $jf20 = 40;
        $jf19 = 38;
        $jf18 = 36;
        $jf17 = 34;
        $jf16 = 32;
        $jf15 = 30;
        $jf14 = 28;
        $jf13 = 26;
        $jf12 = 24;
        $jf11 = 22;
        $jf10 = 20;
        $jf9  = 18;
        $jf8  = 16;
        $jf7  = 14;
        $jf6  = 12;
        $jf5  = 10;
        $jf4  = 8;
        $jf3  = 6;
        $jf2  = 4;
        $jf1  = 2;
        if($cha >= $jf1 && $cha < $jf2){
            $return['num'] += 1;
        }else if($cha >= $jf2 && $cha < $jf3){
            $return['num'] += 2;
        }else if($cha >= $jf3 && $cha < $jf4){
            $return['num'] += 3;
        }else if($cha >= $jf4 && $cha < $jf5){
            $return['num'] += 4;
        }else if($cha >= $jf5 && $cha < $jf6){
            $return['num'] += 5;
        }else if($cha >= $jf6 && $cha < $jf7){
            $return['num'] += 6;
        }else if($cha >= $jf7 && $cha < $jf8){
            $return['num'] += 7;
        }else if($cha >= $jf8 && $cha < $jf9){
            $return['num'] += 8;
        }else if($cha >= $jf9 && $cha < $jf10){
            $return['num'] += 9;
        }else if($cha >= $jf10 && $cha < $jf11){
            $return['num'] += 10;
        }else if($cha >= $jf11 && $cha < $jf12){
            $return['num'] += 11;
        }else if($cha >= $jf12 && $cha < $jf13){
            $return['num'] += 12;
        }else if($cha >= $jf13 && $cha < $jf14){
            $return['num'] += 13;
        }else if($cha >= $jf14 && $cha < $jf15){
            $return['num'] += 14;
        }else if($cha >= $jf15 && $cha < $jf16){
            $return['num'] += 15;
        }else if($cha >= $jf16 && $cha < $jf17){
            $return['num'] += 16;
        }else if($cha >= $jf17 && $cha < $jf18){
            $return['num'] += 17;
        }else if($cha >= $jf18 && $cha < $jf19){
            $return['num'] += 18;
        }else if($cha >= $jf19 && $cha < $jf20){
            $return['num'] += 19;
        }else if($cha >= $jf20){
            $return['num'] += 20;
        }
    }
    return $return;
}

//立定跳远
function sport_jump_result($id,$num,$sex = 1)
{
    $return = [
        'name'  => '未知',
        'num'   => 0,
    ];
    switch ($id){
        case 7:
            if($sex == 1){
                $y_100 = 225;
                $y_95  = 218;
                $y_90  = 211;
                $l_85  = 203;
                $l_80  = 195;
                $j_78  = 191;
                $j_76  = 187;
                $j_74  = 183;
                $j_72  = 179;
                $j_70  = 175;
                $j_68  = 171;
                $j_66  = 167;
                $j_64  = 163;
                $j_62  = 159;
                $j_60  = 155;
                $b_50  = 150;
                $b_40  = 145;
                $b_30  = 140;
                $b_20  = 135;
                $b_10  = 130;
            }else{
                $y_100 = 196;
                $y_95  = 190;
                $y_90  = 184;
                $l_85  = 177;
                $l_80  = 170;
                $j_78  = 167;
                $j_76  = 164;
                $j_74  = 161;
                $j_72  = 158;
                $j_70  = 155;
                $j_68  = 152;
                $j_66  = 149;
                $j_64  = 146;
                $j_62  = 143;
                $j_60  = 140;
                $b_50  = 135;
                $b_40  = 130;
                $b_30  = 125;
                $b_20  = 120;
                $b_10  = 115;
            }
            break;
        case 8:
            if($sex == 1){
                $y_100 = 240;
                $y_95  = 233;
                $y_90  = 226;
                $l_85  = 218;
                $l_80  = 210;
                $j_78  = 206;
                $j_76  = 202;
                $j_74  = 198;
                $j_72  = 194;
                $j_70  = 190;
                $j_68  = 186;
                $j_66  = 182;
                $j_64  = 178;
                $j_62  = 174;
                $j_60  = 170;
                $b_50  = 165;
                $b_40  = 160;
                $b_30  = 155;
                $b_20  = 150;
                $b_10  = 145;
            }else{
                $y_100 = 200;
                $y_95  = 194;
                $y_90  = 188;
                $l_85  = 181;
                $l_80  = 174;
                $j_78  = 171;
                $j_76  = 168;
                $j_74  = 165;
                $j_72  = 162;
                $j_70  = 159;
                $j_68  = 156;
                $j_66  = 153;
                $j_64  = 150;
                $j_62  = 147;
                $j_60  = 144;
                $b_50  = 139;
                $b_40  = 134;
                $b_30  = 129;
                $b_20  = 124;
                $b_10  = 119;
            }
            break;
        case 9:
            if($sex == 1){
                $y_100 = 250;
                $y_95  = 245;
                $y_90  = 240;
                $l_85  = 233;
                $l_80  = 225;
                $j_78  = 221;
                $j_76  = 217;
                $j_74  = 213;
                $j_72  = 209;
                $j_70  = 205;
                $j_68  = 201;
                $j_66  = 197;
                $j_64  = 193;
                $j_62  = 189;
                $j_60  = 185;
                $b_50  = 180;
                $b_40  = 175;
                $b_30  = 170;
                $b_20  = 165;
                $b_10  = 160;
            }else{
                $y_100 = 202;
                $y_95  = 196;
                $y_90  = 190;
                $l_85  = 183;
                $l_80  = 176;
                $j_78  = 173;
                $j_76  = 170;
                $j_74  = 167;
                $j_72  = 164;
                $j_70  = 161;
                $j_68  = 158;
                $j_66  = 155;
                $j_64  = 152;
                $j_62  = 149;
                $j_60  = 146;
                $b_50  = 141;
                $b_40  = 136;
                $b_30  = 131;
                $b_20  = 126;
                $b_10  = 121;
            }
            break;
        case 10:
            if($sex == 1){
                $y_100 = 260;
                $y_95  = 255;
                $y_90  = 250;
                $l_85  = 243;
                $l_80  = 235;
                $j_78  = 231;
                $j_76  = 227;
                $j_74  = 223;
                $j_72  = 219;
                $j_70  = 215;
                $j_68  = 211;
                $j_66  = 207;
                $j_64  = 203;
                $j_62  = 199;
                $j_60  = 195;
                $b_50  = 190;
                $b_40  = 185;
                $b_30  = 180;
                $b_20  = 175;
                $b_10  = 170;
            }else{
                $y_100 = 204;
                $y_95  = 198;
                $y_90  = 192;
                $l_85  = 185;
                $l_80  = 178;
                $j_78  = 175;
                $j_76  = 172;
                $j_74  = 169;
                $j_72  = 166;
                $j_70  = 163;
                $j_68  = 160;
                $j_66  = 157;
                $j_64  = 154;
                $j_62  = 151;
                $j_60  = 148;
                $b_50  = 143;
                $b_40  = 138;
                $b_30  = 133;
                $b_20  = 128;
                $b_10  = 123;
            }
            break;
        case 11:
            if($sex == 1){
                $y_100 = 265;
                $y_95  = 260;
                $y_90  = 255;
                $l_85  = 248;
                $l_80  = 240;
                $j_78  = 236;
                $j_76  = 232;
                $j_74  = 228;
                $j_72  = 224;
                $j_70  = 220;
                $j_68  = 216;
                $j_66  = 212;
                $j_64  = 208;
                $j_62  = 204;
                $j_60  = 200;
                $b_50  = 195;
                $b_40  = 190;
                $b_30  = 185;
                $b_20  = 180;
                $b_10  = 175;
            }else{
                $y_100 = 205;
                $y_95  = 199;
                $y_90  = 193;
                $l_85  = 186;
                $l_80  = 179;
                $j_78  = 176;
                $j_76  = 173;
                $j_74  = 170;
                $j_72  = 167;
                $j_70  = 164;
                $j_68  = 161;
                $j_66  = 158;
                $j_64  = 155;
                $j_62  = 152;
                $j_60  = 149;
                $b_50  = 144;
                $b_40  = 139;
                $b_30  = 134;
                $b_20  = 129;
                $b_10  = 124;
            }
            break;
        case 12:
            if($sex == 1){
                $y_100 = 270;
                $y_95  = 265;
                $y_90  = 260;
                $l_85  = 253;
                $l_80  = 245;
                $j_78  = 241;
                $j_76  = 237;
                $j_74  = 233;
                $j_72  = 229;
                $j_70  = 225;
                $j_68  = 221;
                $j_66  = 217;
                $j_64  = 213;
                $j_62  = 209;
                $j_60  = 205;
                $b_50  = 200;
                $b_40  = 195;
                $b_30  = 190;
                $b_20  = 185;
                $b_10  = 180;
            }else{
                $y_100 = 206;
                $y_95  = 200;
                $y_90  = 194;
                $l_85  = 187;
                $l_80  = 180;
                $j_78  = 177;
                $j_76  = 174;
                $j_74  = 171;
                $j_72  = 168;
                $j_70  = 165;
                $j_68  = 162;
                $j_66  = 159;
                $j_64  = 156;
                $j_62  = 153;
                $j_60  = 150;
                $b_50  = 145;
                $b_40  = 140;
                $b_30  = 135;
                $b_20  = 130;
                $b_10  = 125;
            }
            break;
        case 13:
        case 14:
            if($sex == 1){
                $y_100 = 273;
                $y_95  = 268;
                $y_90  = 263;
                $l_85  = 256;
                $l_80  = 248;
                $j_78  = 244;
                $j_76  = 240;
                $j_74  = 236;
                $j_72  = 232;
                $j_70  = 228;
                $j_68  = 224;
                $j_66  = 220;
                $j_64  = 216;
                $j_62  = 212;
                $j_60  = 208;
                $b_50  = 203;
                $b_40  = 198;
                $b_30  = 193;
                $b_20  = 188;
                $b_10  = 183;
            }else{
                $y_100 = 207;
                $y_95  = 201;
                $y_90  = 195;
                $l_85  = 188;
                $l_80  = 181;
                $j_78  = 178;
                $j_76  = 175;
                $j_74  = 172;
                $j_72  = 169;
                $j_70  = 166;
                $j_68  = 163;
                $j_66  = 160;
                $j_64  = 157;
                $j_62  = 154;
                $j_60  = 151;
                $b_50  = 146;
                $b_40  = 141;
                $b_30  = 136;
                $b_20  = 131;
                $b_10  = 126;
            }
            break;
        case 15:
        case 16:
            if($sex == 1){
                $y_100 = 275;
                $y_95  = 270;
                $y_90  = 265;
                $l_85  = 258;
                $l_80  = 250;
                $j_78  = 246;
                $j_76  = 242;
                $j_74  = 238;
                $j_72  = 234;
                $j_70  = 230;
                $j_68  = 226;
                $j_66  = 222;
                $j_64  = 218;
                $j_62  = 214;
                $j_60  = 210;
                $b_50  = 205;
                $b_40  = 200;
                $b_30  = 195;
                $b_20  = 190;
                $b_10  = 185;
            }else{
                $y_100 = 208;
                $y_95  = 202;
                $y_90  = 196;
                $l_85  = 189;
                $l_80  = 182;
                $j_78  = 179;
                $j_76  = 176;
                $j_74  = 173;
                $j_72  = 170;
                $j_70  = 167;
                $j_68  = 164;
                $j_66  = 161;
                $j_64  = 158;
                $j_62  = 155;
                $j_60  = 152;
                $b_50  = 147;
                $b_40  = 142;
                $b_30  = 137;
                $b_20  = 132;
                $b_10  = 127;
            }
            break;
        default:
            return ['num'=>0,'name'=>'未知'];
    }
    if($num<$b_10){
        $return['name'] = '不及格';
        $return['num']  = 0;
    }else if($num >= $b_10 && $num < $b_20){
        $return['name'] = '不及格';
        $return['num']  = 10;
    }else if($num >= $b_20 && $num < $b_30){
        $return['name'] = '不及格';
        $return['num']  = 20;
    }else if($num >= $b_30 && $num < $b_40){
        $return['name'] = '不及格';
        $return['num']  = 30;
    }else if($num >= $b_40 && $num < $b_50){
        $return['name'] = '不及格';
        $return['num']  = 40;
    }else if($num >= $b_50 && $num < $j_60){
        $return['name'] = '不及格';
        $return['num']  = 50;
    }else if($num >= $j_60 && $num < $j_62){
        $return['name'] = '及格';
        $return['num']  = 60;
    }else if($num >= $j_62 && $num < $j_64){
        $return['name'] = '及格';
        $return['num']  = 62;
    }else if($num >= $j_64 && $num < $j_66){
        $return['name'] = '及格';
        $return['num']  = 64;
    }else if($num >= $j_66 && $num < $j_68){
        $return['name'] = '及格';
        $return['num']  = 66;
    }else if($num >= $j_68 && $num < $j_70){
        $return['name'] = '及格';
        $return['num']  = 68;
    }else if($num >= $j_70 && $num < $j_72){
        $return['name'] = '及格';
        $return['num']  = 70;
    }else if($num >= $j_72 && $num < $j_74){
        $return['name'] = '及格';
        $return['num']  = 72;
    }else if($num >= $j_74 && $num < $j_76){
        $return['name'] = '及格';
        $return['num']  = 74;
    }else if($num >= $j_76 && $num < $j_78){
        $return['name'] = '及格';
        $return['num']  = 76;
    }else if($num >= $j_78 && $num < $l_80){
        $return['name'] = '及格';
        $return['num']  = 78;
    }else if($num >= $l_80 && $num < $l_85){
        $return['name'] = '良好';
        $return['num']  = 80;
    }else if($num >= $l_85 && $num < $y_90){
        $return['name'] = '良好';
        $return['num']  = 85;
    }else if($num >= $y_90 && $num < $y_95){
        $return['name'] = '优秀';
        $return['num']  = 90;
    }else if($num >= $y_95 && $num < $y_100){
        $return['name'] = '优秀';
        $return['num']  = 95;
    }else if($num >= $y_100 ){
        $return['name'] = '优秀';
        $return['num']  = 100;
    }
    return $return;
}

//男生一分钟仰卧起坐、引体向上
//小学三年级～六年级：一分钟仰卧起坐；初中、高中、大学：引体向上。
//女生一分钟仰卧起坐
function sport_sit_up_result($id,$num,$sex = 1)
{
    $return = [
        'name'  => '未知',
        'num'   => 0,
    ];
    switch ($id){
        case 3:
            if($sex == 1){
                $y_100 = 48;
                $y_95  = 45;
                $y_90  = 42;
                $l_85  = 39;
                $l_80  = 36;
                $j_78  = 34;
                $j_76  = 32;
                $j_74  = 30;
                $j_72  = 28;
                $j_70  = 26;
                $j_68  = 24;
                $j_66  = 22;
                $j_64  = 20;
                $j_62  = 18;
                $j_60  = 16;
                $b_50  = 14;
                $b_40  = 12;
                $b_30  = 10;
                $b_20  = 8;
                $b_10  = 6;
            }else{
                $y_100 = 46;
                $y_95  = 44;
                $y_90  = 42;
                $l_85  = 39;
                $l_80  = 36;
                $j_78  = 34;
                $j_76  = 32;
                $j_74  = 30;
                $j_72  = 28;
                $j_70  = 26;
                $j_68  = 24;
                $j_66  = 22;
                $j_64  = 20;
                $j_62  = 18;
                $j_60  = 16;
                $b_50  = 14;
                $b_40  = 12;
                $b_30  = 10;
                $b_20  = 8;
                $b_10  = 6;
            }
            break;
        case 4:
            if($sex == 1){
                $y_100 = 49;
                $y_95  = 46;
                $y_90  = 43;
                $l_85  = 40;
                $l_80  = 37;
                $j_78  = 35;
                $j_76  = 33;
                $j_74  = 31;
                $j_72  = 29;
                $j_70  = 27;
                $j_68  = 25;
                $j_66  = 23;
                $j_64  = 21;
                $j_62  = 19;
                $j_60  = 17;
                $b_50  = 15;
                $b_40  = 13;
                $b_30  = 11;
                $b_20  = 9;
                $b_10  = 7;
            }else{
                $y_100 = 47;
                $y_95  = 45;
                $y_90  = 43;
                $l_85  = 40;
                $l_80  = 37;
                $j_78  = 35;
                $j_76  = 33;
                $j_74  = 31;
                $j_72  = 29;
                $j_70  = 27;
                $j_68  = 25;
                $j_66  = 23;
                $j_64  = 21;
                $j_62  = 19;
                $j_60  = 17;
                $b_50  = 15;
                $b_40  = 13;
                $b_30  = 11;
                $b_20  = 9;
                $b_10  = 7;
            }
            break;
        case 5:
            if($sex == 1){
                $y_100 = 50;
                $y_95  = 47;
                $y_90  = 44;
                $l_85  = 41;
                $l_80  = 38;
                $j_78  = 36;
                $j_76  = 34;
                $j_74  = 32;
                $j_72  = 30;
                $j_70  = 28;
                $j_68  = 26;
                $j_66  = 24;
                $j_64  = 22;
                $j_62  = 20;
                $j_60  = 18;
                $b_50  = 16;
                $b_40  = 14;
                $b_30  = 12;
                $b_20  = 10;
                $b_10  = 8;
            }else{
                $y_100 = 48;
                $y_95  = 46;
                $y_90  = 44;
                $l_85  = 41;
                $l_80  = 38;
                $j_78  = 36;
                $j_76  = 34;
                $j_74  = 32;
                $j_72  = 30;
                $j_70  = 28;
                $j_68  = 26;
                $j_66  = 24;
                $j_64  = 22;
                $j_62  = 20;
                $j_60  = 18;
                $b_50  = 16;
                $b_40  = 14;
                $b_30  = 12;
                $b_20  = 10;
                $b_10  = 8;
            }
            break;
        case 6:
            if($sex == 1){
                $y_100 = 51;
                $y_95  = 48;
                $y_90  = 45;
                $l_85  = 42;
                $l_80  = 39;
                $j_78  = 37;
                $j_76  = 35;
                $j_74  = 33;
                $j_72  = 31;
                $j_70  = 29;
                $j_68  = 27;
                $j_66  = 25;
                $j_64  = 23;
                $j_62  = 21;
                $j_60  = 19;
                $b_50  = 17;
                $b_40  = 15;
                $b_30  = 13;
                $b_20  = 11;
                $b_10  = 9;
            }else{
                $y_100 = 49;
                $y_95  = 47;
                $y_90  = 45;
                $l_85  = 42;
                $l_80  = 39;
                $j_78  = 37;
                $j_76  = 35;
                $j_74  = 33;
                $j_72  = 31;
                $j_70  = 29;
                $j_68  = 27;
                $j_66  = 25;
                $j_64  = 23;
                $j_62  = 21;
                $j_60  = 19;
                $b_50  = 17;
                $b_40  = 15;
                $b_30  = 13;
                $b_20  = 11;
                $b_10  = 9;
            }
            break;
        case 7:
            if($sex == 1){
                $y_100 = 13;
                $y_95  = 12;
                $y_90  = 11;
                $l_85  = 10;
                $l_80  = 9;
                $j_78  = '';
                $j_76  = 8;
                $j_74  = '';
                $j_72  = 7;
                $j_70  = '';
                $j_68  = 6;
                $j_66  = '';
                $j_64  = 5;
                $j_62  = '';
                $j_60  = 4;
                $b_50  = 3;
                $b_40  = 2;
                $b_30  = 1;
                $b_20  = '';
                $b_10  = '';
            }else{
                $y_100 = 50;
                $y_95  = 48;
                $y_90  = 46;
                $l_85  = 43;
                $l_80  = 40;
                $j_78  = 38;
                $j_76  = 36;
                $j_74  = 34;
                $j_72  = 32;
                $j_70  = 30;
                $j_68  = 28;
                $j_66  = 26;
                $j_64  = 24;
                $j_62  = 22;
                $j_60  = 20;
                $b_50  = 18;
                $b_40  = 16;
                $b_30  = 14;
                $b_20  = 12;
                $b_10  = 10;
            }
            break;
        case 8:
            if($sex == 1){
                $y_100 = 14;
                $y_95  = 13;
                $y_90  = 12;
                $l_85  = 11;
                $l_80  = 10;
                $j_78  = '';
                $j_76  = 9;
                $j_74  = '';
                $j_72  = 8;
                $j_70  = '';
                $j_68  = 7;
                $j_66  = '';
                $j_64  = 6;
                $j_62  = '';
                $j_60  = 5;
                $b_50  = 4;
                $b_40  = 3;
                $b_30  = 2;
                $b_20  = 1;
                $b_10  = '';
            }else{
                $y_100 = 51;
                $y_95  = 49;
                $y_90  = 47;
                $l_85  = 44;
                $l_80  = 41;
                $j_78  = 39;
                $j_76  = 37;
                $j_74  = 35;
                $j_72  = 33;
                $j_70  = 31;
                $j_68  = 29;
                $j_66  = 27;
                $j_64  = 25;
                $j_62  = 23;
                $j_60  = 21;
                $b_50  = 19;
                $b_40  = 17;
                $b_30  = 15;
                $b_20  = 13;
                $b_10  = 11;
            }
            break;
        case 9:
            if($sex == 1){
                $y_100 = 15;
                $y_95  = 14;
                $y_90  = 13;
                $l_85  = 12;
                $l_80  = 11;
                $j_78  = '';
                $j_76  = 10;
                $j_74  = '';
                $j_72  = 9;
                $j_70  = '';
                $j_68  = 8;
                $j_66  = '';
                $j_64  = 7;
                $j_62  = '';
                $j_60  = 6;
                $b_50  = 5;
                $b_40  = 4;
                $b_30  = 3;
                $b_20  = 2;
                $b_10  = 1;
            }else{
                $y_100 = 52;
                $y_95  = 50;
                $y_90  = 48;
                $l_85  = 45;
                $l_80  = 42;
                $j_78  = 40;
                $j_76  = 38;
                $j_74  = 36;
                $j_72  = 34;
                $j_70  = 32;
                $j_68  = 30;
                $j_66  = 28;
                $j_64  = 26;
                $j_62  = 24;
                $j_60  = 22;
                $b_50  = 20;
                $b_40  = 18;
                $b_30  = 16;
                $b_20  = 14;
                $b_10  = 12;
            }
            break;
        case 10:
            if($sex == 1){
                $y_100 = 16;
                $y_95  = 15;
                $y_90  = 14;
                $l_85  = 13;
                $l_80  = 12;
                $j_78  = '';
                $j_76  = 11;
                $j_74  = '';
                $j_72  = 10;
                $j_70  = '';
                $j_68  = 9;
                $j_66  = '';
                $j_64  = 8;
                $j_62  = '';
                $j_60  = 7;
                $b_50  = 6;
                $b_40  = 5;
                $b_30  = 4;
                $b_20  = 3;
                $b_10  = 2;
            }else{
                $y_100 = 53;
                $y_95  = 51;
                $y_90  = 49;
                $l_85  = 46;
                $l_80  = 43;
                $j_78  = 41;
                $j_76  = 39;
                $j_74  = 37;
                $j_72  = 35;
                $j_70  = 33;
                $j_68  = 31;
                $j_66  = 29;
                $j_64  = 27;
                $j_62  = 25;
                $j_60  = 23;
                $b_50  = 21;
                $b_40  = 19;
                $b_30  = 17;
                $b_20  = 15;
                $b_10  = 13;
            }
            break;
        case 11:
            if($sex == 1){
                $y_100 = 17;
                $y_95  = 16;
                $y_90  = 15;
                $l_85  = 14;
                $l_80  = 13;
                $j_78  = '';
                $j_76  = 12;
                $j_74  = '';
                $j_72  = 11;
                $j_70  = '';
                $j_68  = 10;
                $j_66  = '';
                $j_64  = 9;
                $j_62  = '';
                $j_60  = 8;
                $b_50  = 7;
                $b_40  = 6;
                $b_30  = 5;
                $b_20  = 4;
                $b_10  = 3;
            }else{
                $y_100 = 54;
                $y_95  = 52;
                $y_90  = 50;
                $l_85  = 47;
                $l_80  = 44;
                $j_78  = 42;
                $j_76  = 40;
                $j_74  = 38;
                $j_72  = 36;
                $j_70  = 34;
                $j_68  = 32;
                $j_66  = 30;
                $j_64  = 28;
                $j_62  = 26;
                $j_60  = 24;
                $b_50  = 22;
                $b_40  = 20;
                $b_30  = 18;
                $b_20  = 16;
                $b_10  = 14;
            }
            break;
        case 12:
            if($sex == 1){
                $y_100 = 18;
                $y_95  = 17;
                $y_90  = 16;
                $l_85  = 15;
                $l_80  = 14;
                $j_78  = '';
                $j_76  = 13;
                $j_74  = '';
                $j_72  = 12;
                $j_70  = '';
                $j_68  = 11;
                $j_66  = '';
                $j_64  = 10;
                $j_62  = '';
                $j_60  = 9;
                $b_50  = 8;
                $b_40  = 7;
                $b_30  = 6;
                $b_20  = 5;
                $b_10  = 4;
            }else{
                $y_100 = 55;
                $y_95  = 53;
                $y_90  = 51;
                $l_85  = 48;
                $l_80  = 45;
                $j_78  = 43;
                $j_76  = 41;
                $j_74  = 39;
                $j_72  = 37;
                $j_70  = 35;
                $j_68  = 33;
                $j_66  = 31;
                $j_64  = 29;
                $j_62  = 27;
                $j_60  = 25;
                $b_50  = 23;
                $b_40  = 21;
                $b_30  = 19;
                $b_20  = 17;
                $b_10  = 15;
            }
            break;
        case 13:
        case 14:
            if($sex == 1){
                $y_100 = 19;
                $y_95  = 18;
                $y_90  = 17;
                $l_85  = 16;
                $l_80  = 15;
                $j_78  = '';
                $j_76  = 14;
                $j_74  = '';
                $j_72  = 13;
                $j_70  = '';
                $j_68  = 12;
                $j_66  = '';
                $j_64  = 11;
                $j_62  = '';
                $j_60  = 10;
                $b_50  = 9;
                $b_40  = 8;
                $b_30  = 7;
                $b_20  = 6;
                $b_10  = 5;
            }else{
                $y_100 = 56;
                $y_95  = 54;
                $y_90  = 52;
                $l_85  = 49;
                $l_80  = 46;
                $j_78  = 44;
                $j_76  = 42;
                $j_74  = 40;
                $j_72  = 38;
                $j_70  = 36;
                $j_68  = 34;
                $j_66  = 32;
                $j_64  = 30;
                $j_62  = 28;
                $j_60  = 26;
                $b_50  = 24;
                $b_40  = 22;
                $b_30  = 20;
                $b_20  = 18;
                $b_10  = 16;
            }
            break;
        case 15:
        case 16:
            if($sex == 1){
                $y_100 = 20;
                $y_95  = 19;
                $y_90  = 18;
                $l_85  = 17;
                $l_80  = 16;
                $j_78  = '';
                $j_76  = 15;
                $j_74  = '';
                $j_72  = 14;
                $j_70  = '';
                $j_68  = 13;
                $j_66  = '';
                $j_64  = 12;
                $j_62  = '';
                $j_60  = 11;
                $b_50  = 10;
                $b_40  = 9;
                $b_30  = 8;
                $b_20  = 7;
                $b_10  = 6;
            }else{
                $y_100 = 57;
                $y_95  = 55;
                $y_90  = 53;
                $l_85  = 50;
                $l_80  = 47;
                $j_78  = 45;
                $j_76  = 43;
                $j_74  = 41;
                $j_72  = 39;
                $j_70  = 37;
                $j_68  = 35;
                $j_66  = 33;
                $j_64  = 31;
                $j_62  = 29;
                $j_60  = 27;
                $b_50  = 25;
                $b_40  = 23;
                $b_30  = 21;
                $b_20  = 19;
                $b_10  = 17;
            }
            break;
        default:
            return ['num'=>0,'name'=>'未知'];
    }
    if($sex == 2 || ($sex == 1 && in_array($id,[3,4,5,6]))){
        if($num<$b_10){
            $return['name'] = '不及格';
            $return['num']  = 0;
        }else if($num >= $b_10 && $num < $b_20){
            $return['name'] = '不及格';
            $return['num']  = 10;
        }else if($num >= $b_20 && $num < $b_30){
            $return['name'] = '不及格';
            $return['num']  = 20;
        }else if($num >= $b_30 && $num < $b_40){
            $return['name'] = '不及格';
            $return['num']  = 30;
        }else if($num >= $b_40 && $num < $b_50){
            $return['name'] = '不及格';
            $return['num']  = 40;
        }else if($num >= $b_50 && $num < $j_60){
            $return['name'] = '不及格';
            $return['num']  = 50;
        }else if($num >= $j_60 && $num < $j_62){
            $return['name'] = '及格';
            $return['num']  = 60;
        }else if($num >= $j_62 && $num < $j_64){
            $return['name'] = '及格';
            $return['num']  = 62;
        }else if($num >= $j_64 && $num < $j_66){
            $return['name'] = '及格';
            $return['num']  = 64;
        }else if($num >= $j_66 && $num < $j_68){
            $return['name'] = '及格';
            $return['num']  = 66;
        }else if($num >= $j_68 && $num < $j_70){
            $return['name'] = '及格';
            $return['num']  = 68;
        }else if($num >= $j_70 && $num < $j_72){
            $return['name'] = '及格';
            $return['num']  = 70;
        }else if($num >= $j_72 && $num < $j_74){
            $return['name'] = '及格';
            $return['num']  = 72;
        }else if($num >= $j_74 && $num < $j_76){
            $return['name'] = '及格';
            $return['num']  = 74;
        }else if($num >= $j_76 && $num < $j_78){
            $return['name'] = '及格';
            $return['num']  = 76;
        }else if($num >= $j_78 && $num < $l_80){
            $return['name'] = '及格';
            $return['num']  = 78;
        }else if($num >= $l_80 && $num < $l_85){
            $return['name'] = '良好';
            $return['num']  = 80;
        }else if($num >= $l_85 && $num < $y_90){
            $return['name'] = '良好';
            $return['num']  = 85;
        }else if($num >= $y_90 && $num < $y_95){
            $return['name'] = '优秀';
            $return['num']  = 90;
        }else if($num >= $y_95 && $num < $y_100){
            $return['name'] = '优秀';
            $return['num']  = 95;
        }else if($num >= $y_100 ){
            $return['name'] = '优秀';
            $return['num']  = 100;
        }
    }else{
        if($id == 7){
            if($num<$b_30){
                $return['name'] = '不及格';
                $return['num']  = 0;
            }else if($num >= $b_30 && $num < $b_40){
                $return['name'] = '不及格';
                $return['num']  = 30;
            }else if($num >= $b_40 && $num < $b_50){
                $return['name'] = '不及格';
                $return['num']  = 40;
            }else if($num >= $b_50 && $num < $j_60){
                $return['name'] = '不及格';
                $return['num']  = 50;
            }else if($num >= $j_60 && $num < $j_62){
                $return['name'] = '及格';
                $return['num']  = 60;
            }else if($num >= $j_64 && $num < $j_66){
                $return['name'] = '及格';
                $return['num']  = 64;
            }else if($num >= $j_68 && $num < $j_70){
                $return['name'] = '及格';
                $return['num']  = 68;
            }else if($num >= $j_72 && $num < $j_74){
                $return['name'] = '及格';
                $return['num']  = 72;
            }else if($num >= $j_76 && $num < $j_78){
                $return['name'] = '及格';
                $return['num']  = 76;
            }else if($num >= $l_80 && $num < $l_85){
                $return['name'] = '良好';
                $return['num']  = 80;
            }else if($num >= $l_85 && $num < $y_90){
                $return['name'] = '良好';
                $return['num']  = 85;
            }else if($num >= $y_90 && $num < $y_95){
                $return['name'] = '优秀';
                $return['num']  = 90;
            }else if($num >= $y_95 && $num < $y_100){
                $return['name'] = '优秀';
                $return['num']  = 95;
            }else if($num >= $y_100 ){
                $return['name'] = '优秀';
                $return['num']  = 100;
            }
        }else if($id == 8){
            if($num<$b_20){
                $return['name'] = '不及格';
                $return['num']  = 0;
            }else if($num >= $b_20 && $num < $b_30){
                $return['name'] = '不及格';
                $return['num']  = 20;
            }else if($num >= $b_30 && $num < $b_40){
                $return['name'] = '不及格';
                $return['num']  = 30;
            }else if($num >= $b_40 && $num < $b_50){
                $return['name'] = '不及格';
                $return['num']  = 40;
            }else if($num >= $b_50 && $num < $j_60){
                $return['name'] = '不及格';
                $return['num']  = 50;
            }else if($num >= $j_60 && $num < $j_62){
                $return['name'] = '及格';
                $return['num']  = 60;
            }else if($num >= $j_64 && $num < $j_66){
                $return['name'] = '及格';
                $return['num']  = 64;
            }else if($num >= $j_68 && $num < $j_70){
                $return['name'] = '及格';
                $return['num']  = 68;
            }else if($num >= $j_72 && $num < $j_74){
                $return['name'] = '及格';
                $return['num']  = 72;
            }else if($num >= $j_76 && $num < $j_78){
                $return['name'] = '及格';
                $return['num']  = 76;
            }else if($num >= $l_80 && $num < $l_85){
                $return['name'] = '良好';
                $return['num']  = 80;
            }else if($num >= $l_85 && $num < $y_90){
                $return['name'] = '良好';
                $return['num']  = 85;
            }else if($num >= $y_90 && $num < $y_95){
                $return['name'] = '优秀';
                $return['num']  = 90;
            }else if($num >= $y_95 && $num < $y_100){
                $return['name'] = '优秀';
                $return['num']  = 95;
            }else if($num >= $y_100 ){
                $return['name'] = '优秀';
                $return['num']  = 100;
            }
        }else{
            if($num<$b_10){
                $return['name'] = '不及格';
                $return['num']  = 0;
            }else if($num >= $b_10 && $num < $b_20){
                $return['name'] = '不及格';
                $return['num']  = 10;
            }else if($num >= $b_20 && $num < $b_30){
                $return['name'] = '不及格';
                $return['num']  = 20;
            }else if($num >= $b_30 && $num < $b_40){
                $return['name'] = '不及格';
                $return['num']  = 30;
            }else if($num >= $b_40 && $num < $b_50){
                $return['name'] = '不及格';
                $return['num']  = 40;
            }else if($num >= $b_50 && $num < $j_60){
                $return['name'] = '不及格';
                $return['num']  = 50;
            }else if($num >= $j_60 && $num < $j_62){
                $return['name'] = '及格';
                $return['num']  = 60;
            }else if($num >= $j_64 && $num < $j_66){
                $return['name'] = '及格';
                $return['num']  = 64;
            }else if($num >= $j_68 && $num < $j_70){
                $return['name'] = '及格';
                $return['num']  = 68;
            }else if($num >= $j_72 && $num < $j_74){
                $return['name'] = '及格';
                $return['num']  = 72;
            }else if($num >= $j_76 && $num < $j_78){
                $return['name'] = '及格';
                $return['num']  = 76;
            }else if($num >= $l_80 && $num < $l_85){
                $return['name'] = '良好';
                $return['num']  = 80;
            }else if($num >= $l_85 && $num < $y_90){
                $return['name'] = '良好';
                $return['num']  = 85;
            }else if($num >= $y_90 && $num < $y_95){
                $return['name'] = '优秀';
                $return['num']  = 90;
            }else if($num >= $y_95 && $num < $y_100){
                $return['name'] = '优秀';
                $return['num']  = 95;
            }else if($num >= $y_100 ){
                $return['name'] = '优秀';
                $return['num']  = 100;
            }
        }
    }
    //加分
    if($return['num'] == 100 && in_array($id,[7,8,9,10,11,12,13,14,15,16])){
        $cha = intval($y_100 - $num);
        if($sex == 1){
            $jf10 = 10;
            $jf9  = 9;
            $jf8  = 8;
            $jf7  = 7;
            $jf6  = 6;
            $jf5  = 5;
            $jf4  = 4;
            $jf3  = 3;
            $jf2  = 2;
            $jf1  = 1;
        }else{
            $jf10 = 13;
            $jf9  = 12;
            $jf8  = 11;
            $jf7  = 10;
            $jf6  = 9;
            $jf5  = 8;
            $jf4  = 7;
            $jf3  = 6;
            $jf2  = 4;
            $jf1  = 2;
        }
        if($cha >= $jf1 && $cha < $jf2){
            $return['num'] += 1;
        }else if($cha >= $jf2 && $cha < $jf3){
            $return['num'] += 2;
        }else if($cha >= $jf3 && $cha < $jf4){
            $return['num'] += 3;
        }else if($cha >= $jf4 && $cha < $jf5){
            $return['num'] += 4;
        }else if($cha >= $jf5 && $cha < $jf6){
            $return['num'] += 5;
        }else if($cha >= $jf6 && $cha < $jf7){
            $return['num'] += 6;
        }else if($cha >= $jf7 && $cha < $jf8){
            $return['num'] += 7;
        }else if($cha >= $jf8 && $cha < $jf9){
            $return['num'] += 8;
        }else if($cha >= $jf9 && $cha < $jf10){
            $return['num'] += 9;
        }else if($cha >= $jf10){
            $return['num'] += 10;
        }
    }
    return $return;
}

//耐力跑
//小学五年级～六年级：50米×8往返跑；初中、高中、大学：1000米跑。
//小学五年级～六年级：50米×8往返跑；初中、高中、大学：800米跑
function sport_run_else_result($id,$num,$sex = 1)
{
    $return = [
        'name'  => '未知',
        'num'   => 0,
    ];
    switch ($id){
        case 5:
            if($sex == 1){
                $y_100 = 1.36;
                $y_95  = 1.39;
                $y_90  = 1.42;
                $l_85  = 1.45;
                $l_80  = 1.48;
                $j_78  = 1.51;
                $j_76  = 1.54;
                $j_74  = 1.57;
                $j_72  = 2.00;
                $j_70  = 2.03;
                $j_68  = 2.06;
                $j_66  = 2.09;
                $j_64  = 2.12;
                $j_62  = 2.15;
                $j_60  = 2.18;
                $b_50  = 2.22;
                $b_40  = 2.26;
                $b_30  = 2.30;
                $b_20  = 2.34;
                $b_10  = 2.38;
            }else{
                $y_100 = 1.41;
                $y_95  = 1.44;
                $y_90  = 1.47;
                $l_85  = 1.50;
                $l_80  = 1.53;
                $j_78  = 1.56;
                $j_76  = 1.59;
                $j_74  = 2.02;
                $j_72  = 2.05;
                $j_70  = 2.08;
                $j_68  = 2.11;
                $j_66  = 2.14;
                $j_64  = 2.17;
                $j_62  = 2.20;
                $j_60  = 2.23;
                $b_50  = 2.27;
                $b_40  = 2.31;
                $b_30  = 2.35;
                $b_20  = 2.39;
                $b_10  = 2.43;
            }
            break;
        case 6:
            if($sex == 1){
                $y_100 = 1.30;
                $y_95  = 1.33;
                $y_90  = 1.36;
                $l_85  = 1.39;
                $l_80  = 1.42;
                $j_78  = 1.45;
                $j_76  = 1.48;
                $j_74  = 1.51;
                $j_72  = 1.54;
                $j_70  = 1.57;
                $j_68  = 2.00;
                $j_66  = 2.03;
                $j_64  = 2.06;
                $j_62  = 2.09;
                $j_60  = 2.12;
                $b_50  = 2.16;
                $b_40  = 2.20;
                $b_30  = 2.24;
                $b_20  = 2.28;
                $b_10  = 2.32;
            }else{
                $y_100 = 1.37;
                $y_95  = 1.40;
                $y_90  = 1.43;
                $l_85  = 1.46;
                $l_80  = 1.49;
                $j_78  = 1.52;
                $j_76  = 1.55;
                $j_74  = 1.58;
                $j_72  = 2.01;
                $j_70  = 2.04;
                $j_68  = 2.07;
                $j_66  = 2.10;
                $j_64  = 2.13;
                $j_62  = 2.16;
                $j_60  = 2.19;
                $b_50  = 2.23;
                $b_40  = 2.27;
                $b_30  = 2.31;
                $b_20  = 2.35;
                $b_10  = 2.39;
            }
            break;
        case 7:
            if($sex == 1){
                $y_100 = 3.55;
                $y_95  = 4.05;
                $y_90  = 4.15;
                $l_85  = 4.22;
                $l_80  = 4.30;
                $j_78  = 4.35;
                $j_76  = 4.40;
                $j_74  = 4.45;
                $j_72  = 4.50;
                $j_70  = 4.55;
                $j_68  = 5.00;
                $j_66  = 5.05;
                $j_64  = 5.10;
                $j_62  = 5.15;
                $j_60  = 5.20;
                $b_50  = 5.40;
                $b_40  = 6.00;
                $b_30  = 6.20;
                $b_20  = 6.40;
                $b_10  = 7.00;
            }else{
                $y_100 = 3.35;
                $y_95  = 3.42;
                $y_90  = 3.49;
                $l_85  = 3.57;
                $l_80  = 4.05;
                $j_78  = 4.10;
                $j_76  = 4.15;
                $j_74  = 4.20;
                $j_72  = 4.25;
                $j_70  = 4.30;
                $j_68  = 4.35;
                $j_66  = 4.40;
                $j_64  = 4.45;
                $j_62  = 4.50;
                $j_60  = 4.55;
                $b_50  = 5.05;
                $b_40  = 5.15;
                $b_30  = 5.25;
                $b_20  = 5.35;
                $b_10  = 5.45;
            }
            break;
        case 8:
            if($sex == 1){
                $y_100 = 3.50;
                $y_95  = 3.55;
                $y_90  = 4.00;
                $l_85  = 4.07;
                $l_80  = 4.15;
                $j_78  = 4.20;
                $j_76  = 4.25;
                $j_74  = 4.30;
                $j_72  = 4.35;
                $j_70  = 4.40;
                $j_68  = 4.45;
                $j_66  = 4.50;
                $j_64  = 4.55;
                $j_62  = 5.00;
                $j_60  = 5.05;
                $b_50  = 5.25;
                $b_40  = 5.45;
                $b_30  = 6.05;
                $b_20  = 6.25;
                $b_10  = 6.45;
            }else{
                $y_100 = 3.30;
                $y_95  = 3.37;
                $y_90  = 3.44;
                $l_85  = 3.52;
                $l_80  = 4.00;
                $j_78  = 4.05;
                $j_76  = 4.10;
                $j_74  = 4.15;
                $j_72  = 4.20;
                $j_70  = 4.25;
                $j_68  = 4.30;
                $j_66  = 4.35;
                $j_64  = 4.40;
                $j_62  = 4.45;
                $j_60  = 4.50;
                $b_50  = 5.00;
                $b_40  = 5.10;
                $b_30  = 5.20;
                $b_20  = 5.30;
                $b_10  = 5.40;
            }
            break;
        case 9:
            if($sex == 1){
                $y_100 = 3.40;
                $y_95  = 3.45;
                $y_90  = 3.50;
                $l_85  = 3.57;
                $l_80  = 4.05;
                $j_78  = 4.10;
                $j_76  = 4.15;
                $j_74  = 4.20;
                $j_72  = 4.25;
                $j_70  = 4.30;
                $j_68  = 4.35;
                $j_66  = 4.40;
                $j_64  = 4.45;
                $j_62  = 4.50;
                $j_60  = 4.55;
                $b_50  = 5.15;
                $b_40  = 5.35;
                $b_30  = 5.55;
                $b_20  = 6.15;
                $b_10  = 6.35;
            }else{
                $y_100 = 3.25;
                $y_95  = 3.32;
                $y_90  = 3.39;
                $l_85  = 3.47;
                $l_80  = 3.55;
                $j_78  = 4.00;
                $j_76  = 4.05;
                $j_74  = 4.10;
                $j_72  = 4.15;
                $j_70  = 4.20;
                $j_68  = 4.25;
                $j_66  = 4.30;
                $j_64  = 4.35;
                $j_62  = 4.40;
                $j_60  = 4.45;
                $b_50  = 4.55;
                $b_40  = 5.05;
                $b_30  = 5.15;
                $b_20  = 5.25;
                $b_10  = 5.35;
            }
            break;
        case 10:
            if($sex == 1){
                $y_100 = 3.30;
                $y_95  = 3.35;
                $y_90  = 3.40;
                $l_85  = 3.47;
                $l_80  = 3.55;
                $j_78  = 4.00;
                $j_76  = 4.05;
                $j_74  = 4.10;
                $j_72  = 4.15;
                $j_70  = 4.20;
                $j_68  = 4.25;
                $j_66  = 4.30;
                $j_64  = 4.35;
                $j_62  = 4.40;
                $j_60  = 4.45;
                $b_50  = 5.05;
                $b_40  = 5.25;
                $b_30  = 5.45;
                $b_20  = 6.05;
                $b_10  = 6.25;
            }else{
                $y_100 = 3.24;
                $y_95  = 3.30;
                $y_90  = 3.36;
                $l_85  = 3.43;
                $l_80  = 3.50;
                $j_78  = 3.55;
                $j_76  = 4.00;
                $j_74  = 4.05;
                $j_72  = 4.10;
                $j_70  = 4.15;
                $j_68  = 4.20;
                $j_66  = 4.25;
                $j_64  = 4.30;
                $j_62  = 4.35;
                $j_60  = 4.40;
                $b_50  = 4.50;
                $b_40  = 5.00;
                $b_30  = 5.10;
                $b_20  = 5.20;
                $b_10  = 5.30;
            }
            break;
        case 11:
            if($sex == 1){
                $y_100 = 3.25;
                $y_95  = 3.30;
                $y_90  = 3.35;
                $l_85  = 3.42;
                $l_80  = 3.50;
                $j_78  = 3.55;
                $j_76  = 4.00;
                $j_74  = 4.05;
                $j_72  = 4.10;
                $j_70  = 4.15;
                $j_68  = 4.20;
                $j_66  = 4.25;
                $j_64  = 4.30;
                $j_62  = 4.35;
                $j_60  = 4.40;
                $b_50  = 5.00;
                $b_40  = 5.20;
                $b_30  = 5.40;
                $b_20  = 6.00;
                $b_10  = 6.20;
            }else{
                $y_100 = 3.22;
                $y_95  = 3.28;
                $y_90  = 3.34;
                $l_85  = 3.41;
                $l_80  = 3.48;
                $j_78  = 3.53;
                $j_76  = 3.58;
                $j_74  = 4.03;
                $j_72  = 4.08;
                $j_70  = 4.13;
                $j_68  = 4.18;
                $j_66  = 4.23;
                $j_64  = 4.28;
                $j_62  = 4.33;
                $j_60  = 4.38;
                $b_50  = 4.48;
                $b_40  = 4.58;
                $b_30  = 5.08;
                $b_20  = 5.18;
                $b_10  = 5.28;
            }
            break;
        case 12:
            if($sex == 1){
                $y_100 = 3.20;
                $y_95  = 3.25;
                $y_90  = 3.30;
                $l_85  = 3.37;
                $l_80  = 3.45;
                $j_78  = 3.50;
                $j_76  = 3.55;
                $j_74  = 4.00;
                $j_72  = 4.05;
                $j_70  = 4.10;
                $j_68  = 4.15;
                $j_66  = 4.20;
                $j_64  = 4.25;
                $j_62  = 4.30;
                $j_60  = 4.35;
                $b_50  = 4.55;
                $b_40  = 5.15;
                $b_30  = 5.35;
                $b_20  = 5.55;
                $b_10  = 6.15;
            }else{
                $y_100 = 3.20;
                $y_95  = 3.26;
                $y_90  = 3.32;
                $l_85  = 3.39;
                $l_80  = 3.46;
                $j_78  = 3.51;
                $j_76  = 3.56;
                $j_74  = 4.01;
                $j_72  = 4.06;
                $j_70  = 4.11;
                $j_68  = 4.16;
                $j_66  = 4.21;
                $j_64  = 4.26;
                $j_62  = 4.31;
                $j_60  = 4.36;
                $b_50  = 4.46;
                $b_40  = 4.56;
                $b_30  = 5.06;
                $b_20  = 5.16;
                $b_10  = 5.26;
            }
            break;
        case 13:
        case 14:
            if($sex == 1){
                $y_100 = 3.17;
                $y_95  = 3.22;
                $y_90  = 3.27;
                $l_85  = 3.34;
                $l_80  = 3.42;
                $j_78  = 3.47;
                $j_76  = 3.52;
                $j_74  = 3.57;
                $j_72  = 4.02;
                $j_70  = 4.07;
                $j_68  = 4.12;
                $j_66  = 4.17;
                $j_64  = 4.22;
                $j_62  = 4.27;
                $j_60  = 4.32;
                $b_50  = 4.52;
                $b_40  = 5.12;
                $b_30  = 5.32;
                $b_20  = 5.52;
                $b_10  = 6.12;
            }else{
                $y_100 = 3.18;
                $y_95  = 3.24;
                $y_90  = 3.30;
                $l_85  = 3.37;
                $l_80  = 3.44;
                $j_78  = 3.49;
                $j_76  = 3.54;
                $j_74  = 3.59;
                $j_72  = 4.04;
                $j_70  = 4.09;
                $j_68  = 4.14;
                $j_66  = 4.19;
                $j_64  = 4.24;
                $j_62  = 4.29;
                $j_60  = 4.34;
                $b_50  = 4.44;
                $b_40  = 4.54;
                $b_30  = 5.04;
                $b_20  = 5.14;
                $b_10  = 5.24;
            }
            break;
        case 15:
        case 16:
            if($sex == 1){
                $y_100 = 3.15;
                $y_95  = 3.20;
                $y_90  = 3.25;
                $l_85  = 3.32;
                $l_80  = 3.40;
                $j_78  = 3.45;
                $j_76  = 3.50;
                $j_74  = 3.55;
                $j_72  = 4.00;
                $j_70  = 4.05;
                $j_68  = 4.10;
                $j_66  = 4.15;
                $j_64  = 4.20;
                $j_62  = 4.25;
                $j_60  = 4.30;
                $b_50  = 4.50;
                $b_40  = 5.10;
                $b_30  = 5.30;
                $b_20  = 5.50;
                $b_10  = 6.10;
            }else{
                $y_100 = 3.16;
                $y_95  = 3.22;
                $y_90  = 3.28;
                $l_85  = 3.35;
                $l_80  = 3.42;
                $j_78  = 3.47;
                $j_76  = 3.52;
                $j_74  = 3.57;
                $j_72  = 4.02;
                $j_70  = 4.07;
                $j_68  = 4.12;
                $j_66  = 4.17;
                $j_64  = 4.22;
                $j_62  = 4.27;
                $j_60  = 4.32;
                $b_50  = 4.42;
                $b_40  = 4.52;
                $b_30  = 5.02;
                $b_20  = 5.12;
                $b_10  = 5.22;
            }
            break;
        default:
            return ['num'=>0,'name'=>'未知'];
    }
    $y_100 = str_to_second($y_100);
    $y_95 = str_to_second($y_95);
    $y_90 = str_to_second($y_90);
    $l_85 = str_to_second($l_85);
    $l_80 = str_to_second($l_80);
    $j_78 = str_to_second($j_78);
    $j_76 = str_to_second($j_76);
    $j_74 = str_to_second($j_74);
    $j_72 = str_to_second($j_72);
    $j_70 = str_to_second($j_70);
    $j_68 = str_to_second($j_68);
    $j_66 = str_to_second($j_66);
    $j_64 = str_to_second($j_64);
    $j_62 = str_to_second($j_62);
    $j_60 = str_to_second($j_60);
    $b_50 = str_to_second($b_50);
    $b_40 = str_to_second($b_40);
    $b_30 = str_to_second($b_30);
    $b_20 = str_to_second($b_20);
    $b_10 = str_to_second($b_10);

    if($num>$b_10){
        $return['name'] = '不及格';
        $return['num']  = 0;
    }else if($num <= $b_10 && $num > $b_20){
        $return['name'] = '不及格';
        $return['num']  = 10;
    }else if($num <= $b_20 && $num > $b_30){
        $return['name'] = '不及格';
        $return['num']  = 20;
    }else if($num <= $b_30 && $num > $b_40){
        $return['name'] = '不及格';
        $return['num']  = 30;
    }else if($num <= $b_40 && $num > $b_50){
        $return['name'] = '不及格';
        $return['num']  = 40;
    }else if($num <= $b_50 && $num > $j_60){
        $return['name'] = '不及格';
        $return['num']  = 50;
    }else if($num <= $j_60 && $num > $j_62){
        $return['name'] = '及格';
        $return['num']  = 60;
    }else if($num <= $j_62 && $num > $j_64){
        $return['name'] = '及格';
        $return['num']  = 62;
    }else if($num <= $j_64 && $num > $j_66){
        $return['name'] = '及格';
        $return['num']  = 64;
    }else if($num <= $j_66 && $num > $j_68){
        $return['name'] = '及格';
        $return['num']  = 66;
    }else if($num <= $j_68 && $num > $j_70){
        $return['name'] = '及格';
        $return['num']  = 68;
    }else if($num <= $j_70 && $num > $j_72){
        $return['name'] = '及格';
        $return['num']  = 70;
    }else if($num <= $j_72 && $num > $j_74){
        $return['name'] = '及格';
        $return['num']  = 72;
    }else if($num <= $j_74 && $num > $j_76){
        $return['name'] = '及格';
        $return['num']  = 74;
    }else if($num <= $j_76 && $num > $j_78){
        $return['name'] = '及格';
        $return['num']  = 76;
    }else if($num <= $j_78 && $num > $l_80){
        $return['name'] = '及格';
        $return['num']  = 78;
    }else if($num <= $l_80 && $num > $l_85){
        $return['name'] = '良好';
        $return['num']  = 80;
    }else if($num <= $l_85 && $num > $y_90){
        $return['name'] = '良好';
        $return['num']  = 85;
    }else if($num <= $y_90 && $num > $y_95){
        $return['name'] = '优秀';
        $return['num']  = 90;
    }else if($num <= $y_95 && $num > $y_100){
        $return['name'] = '优秀';
        $return['num']  = 95;
    }else if($num <= $y_100 ){
        $return['name'] = '优秀';
        $return['num']  = 100;
    }
    //加分
    if($return['num'] == 100 && in_array($id,[7,8,9,10,11,12,13,14,15,16])){
        $cha = intval($y_100 - $num);
        if($sex == 1){
            $jf10 = 35;
            $jf9  = 32;
            $jf8  = 29;
            $jf7  = 26;
            $jf6  = 23;
            $jf5  = 20;
            $jf4  = 16;
            $jf3  = 12;
            $jf2  = 8;
            $jf1  = 4;
        }else{
            $jf10 = 50;
            $jf9  = 45;
            $jf8  = 40;
            $jf7  = 35;
            $jf6  = 30;
            $jf5  = 25;
            $jf4  = 20;
            $jf3  = 15;
            $jf2  = 10;
            $jf1  = 5;
        }
        if($cha >= $jf1 && $cha < $jf2){
            $return['num'] += 1;
        }else if($cha >= $jf2 && $cha < $jf3){
            $return['num'] += 2;
        }else if($cha >= $jf3 && $cha < $jf4){
            $return['num'] += 3;
        }else if($cha >= $jf4 && $cha < $jf5){
            $return['num'] += 4;
        }else if($cha >= $jf5 && $cha < $jf6){
            $return['num'] += 5;
        }else if($cha >= $jf6 && $cha < $jf7){
            $return['num'] += 6;
        }else if($cha >= $jf7 && $cha < $jf8){
            $return['num'] += 7;
        }else if($cha >= $jf8 && $cha < $jf9){
            $return['num'] += 8;
        }else if($cha >= $jf9 && $cha < $jf10){
            $return['num'] += 9;
        }else if($cha >= $jf10){
            $return['num'] += 10;
        }
    }
    return $return;
}

//小数转秒
function str_to_second($a)
{
    $minutes = floor($a); // 获取整数部分，即分钟数
    $seconds = ($a - $minutes) * 100; // 获取小数部分，即秒数
    $totalSeconds = $minutes * 60 + $seconds; // 计算总秒数
    return $totalSeconds;
}
