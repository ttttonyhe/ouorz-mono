<?php
// 根据 IP 地址获取地区
function wpjam_get_ipdata($ip = '')
{
    if (!$ip) $ip = wpjam_get_ip();

    if ($ip == 'unknown') {
        return false;
    }

    $mon_ipdata = wpjam_get_17mon_ipdata($ip);

    $ipdata = array(
        'ip' => $ip,
        'country' => isset($mon_ipdata['0']) ? $mon_ipdata['0'] : '',
        'region' => isset($mon_ipdata['1']) ? $mon_ipdata['1'] : '',
        'city' => isset($mon_ipdata['2']) ? $mon_ipdata['2'] : '',
        'isp' => '',
        'last_update' => current_time('timestamp')
    );

    return $ipdata;

}

function wpjam_get_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
    return '';
}

function wpjam_get_17mon_ipdata($ip)
{
    if (!class_exists('IP')) {
        include(WPJAM_BASIC_PLUGIN_DIR . 'include/ip.php');
    }
    return IP::find($ip);
}

// function wpjam_get_taobao_ipdata($ip){
// 	$url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;

// 	$response = wpjam_remote_request($url, array(), array('errcode'=>'code', 'errmsg'=>'data'));

// 	if(is_wp_error($response)){
// 		return $response;
// 	}

// 	return $response['data'];
// }

// function wpjam_get_baidu_ipdata($ip){

// 	$url		= 'http://apis.baidu.com/apistore/iplookupservice/iplookup?ip='.$ip;
// 	$response	= wpjam_baidu_api_remote_request($url);

// 	if(is_wp_error($response)){
// 		return $response;
// 	}

// 	return $response['retData'];
// }

// 根据 User Agent 获取手机系统和型号
function wpjam_get_ua_data($ua = '')
{
    if (!$ua) $ua = wpjam_get_ua();
    $ua = $ua . ' ';    // 为了特殊情况好匹配

    $os = $os_ver = $device = $build = $weixin_ver = $net_type = '';

    if (preg_match('/MicroMessenger\/(.*?)\s/', $ua, $matches)) {
        $weixin_ver = $matches[1];
    }

    if (preg_match('/NetType\/(.*?)\s/', $ua, $matches)) {
        $net_type = $matches[1];
    }

    if (stripos($ua, 'iPod')) {
        $device = 'iPod';
        $os = 'iOS';
        $os_ver = wpjam_get_ios_version($ua);
        //$build	= wpjam_get_ios_build($ua);
    } elseif (stripos($ua, 'iPad')) {
        $device = 'iPad';
        $os = 'iOS';
        $os_ver = wpjam_get_ios_version($ua);
        //$build	= wpjam_get_ios_build($ua);
    } elseif (stripos($ua, 'iPhone')) {
        $device = 'iPhone';
        $os = 'iOS';
        $os_ver = wpjam_get_ios_version($ua);
        //wo$build	= wpjam_get_ios_build($ua);
    } elseif (stripos($ua, 'Android')) {
        $os = 'Android';

        if (preg_match('/Android ([0-9\.]{1,}?); (.*?) Build\/(.*?)[\)\s;]{1}/i', $ua, $matches)) {
            if (!empty($matches[1]) && !empty($matches[2])) {
                $os_ver = trim($matches[1]);
                $device = $matches[2];
                if (strpos($device, ';') !== false) {
                    $device = substr($device, strpos($device, ';') + 1, strlen($device) - strpos($device, ';'));
                }
                $device = trim($device);
                $build = trim($matches[3]);
            }
        }

    } elseif (stripos($ua, 'Windows NT')) {
        $os = 'Windows';
    } elseif (stripos($ua, 'Macintosh')) {
        $os = 'Macintosh';
    } elseif (stripos($ua, 'Windows Phone')) {
        $os = 'Windows Phone';
    } elseif (stripos($ua, 'BlackBerry') || stripos($ua, 'BB10')) {
        $os = 'BlackBerry';
    } elseif (stripos($ua, 'Symbian')) {
        $os = 'Symbian';
    } else {
        $os = 'unknown';
    }

    return compact("os", "os_ver", "device", "build", "weixin_ver", "net_type");
}

function wpjam_get_ua()
{
    return isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 254) : '';
}

// 获取 iOS 版本
function wpjam_get_ios_version($ua)
{
    if (preg_match('/OS (.*?) like Mac OS X[\)]{1}/i', $ua, $matches)) {
        return trim($matches[1]);
    }
    return null;
}

function wpjam_get_ios_build($ua)
{
    if (preg_match('/Mobile\/(.*?)\s/i', $ua, $matches)) {
        return trim($matches[1]);
    }
    return null;
}
