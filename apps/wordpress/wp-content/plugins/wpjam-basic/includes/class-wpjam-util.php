<?php
class WPJAM_Compare{
	private $args;

	public function __construct($args){
		$this->args		= wp_parse_args($args, ['compare'=>'=', 'value'=>'']);
		$this->compare	= strtoupper($this->compare) ?: '=';

		if(in_array($this->compare, ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])){
			$this->value	= wp_parse_list($this->value);

			if(count($this->value) == 1){
				$this->value	= strval(current($this->value));
				$this->compare	= in_array($this->compare, ['IN', 'BETWEEN']) ? '=' : '!=';
			}else{
				$this->value	= array_map('strval', $this->value);	// JS Array.indexof is strict
			}
		}else{
			if(is_string($this->value)){
				$this->value	= trim($this->value);
			}
		}

		if($this->key && $this->postfix){
			$this->key	= $this->key.$this->postfix;
		}
	}

	public function __get($name){
		if($name == 'args'){
			return $this->args;
		}else{
			return $this->args[$name] ?? null;
		}
	}

	public function __set($name, $value){
		$this->args[$name]	= $value;
	}

	public function __isset($key){
		return $this->$name !== null;
	}

	public function compare($item){
		if($key = $this->key){
			if(is_object($item)){
				$value	= $item->$key ?? null;
			}elseif(is_array($item)){
				$value	= $item[$key] ?? null;
			}else{
				$value	= null;
			}

			if(is_null($value)){
				return null;	// 没有比较
			}
		}else{
			$value	= $item;
		}

		if(is_array($value)){
			if($this->compare == '='){
				return in_array($this->value, $value);
			}elseif($this->compare == '!='){
				return !in_array($this->value, $value);
			}elseif($this->compare == 'IN'){
				return array_intersect($value, $this->value) == $this->value;
			}elseif($this->compare == 'NOT IN'){
				return array_intersect($value, $this->value) == [];
			}
		}else{
			if($this->compare == '='){
				return $value == $this->value;
			}elseif($this->compare == '!='){
				return $value != $this->value;
			}elseif($this->compare == '>'){
				return $value > $this->value;
			}elseif($this->compare == '>='){
				return $value >= $this->value;
			}elseif($this->compare == '<'){
				return $value < $this->value;
			}elseif($this->compare == '<='){
				return $value <= $this->value;
			}elseif($this->compare == 'IN'){
				return in_array($value, $this->value);
			}elseif($this->compare == 'NOT IN'){
				return !in_array($value, $this->value);
			}elseif($this->compare == 'BETWEEN'){
				return $value > $this->value[0] && $value < $this->value[1];
			}elseif($this->compare == 'NOT BETWEEN'){
				return $value < $this->value[0] && $value > $this->value[1];
			}
		}

		return false;
	}
}

class WPJAM_Text{
	private $text;

	public function __construct($text, $strip_tags=false){
		if($strip_tags){
			$text	= wp_strip_all_tags($text);
			$text	= trim($text);
		}

		$this->text	= $text;
	}

	public function get_plain(){
		$text	= $this->text;

		if($text){
			$text	= str_replace(['"', '\''], '', $text);
			$text	= str_replace(["\r\n", "\n", "  "], ' ', $text);

			return trim($text);
		}

		return $text;
	}

	public function get_first_p(){
		$text	= $this->text;

		if($text){
			return trim((explode("\n", $text))[0]); 
		}

		return $text;
	}

	public function disallowed_check(){
		$words = explode("\n", get_option('disallowed_keys'));

		foreach((array)$words as $word){
			$word	= trim($word);

			if($word){
				$word	= preg_quote($word, '#');

				if(preg_match("#$word#i", $this->text)){
					return true;
				}
			}
		}

		return false;
	}

	public function unicode_decode(){
		// [U+D800 - U+DBFF][U+DC00 - U+DFFF]|[U+0000 - U+FFFF]
		return preg_replace_callback('/(\\\\u[0-9a-fA-F]{4})+/i', function($matches){
			return json_decode('"'.$matches[0].'"') ?: $matches[0];
			// return mb_convert_encoding(pack("H*", $matches[1]), 'UTF-8', 'UCS-2BE');
		}, $this->text);
	}

	public function zh_urlencode(){
		return preg_replace_callback('/[\x{4e00}-\x{9fa5}]+/u', function($matches){ 
			return urlencode($matches[0]); 
		}, $this->text);
	}

	public function strimwidth($start=0, $width=40, $trimmarker='...', $encoding='utf-8'){
		$text	= $this->get_plain();

		return $text ? mb_strimwidth($text, $start, $width, $trimmarker, $encoding) : '';
	}

	public function strip_invalid_text($charset='utf8mb4'){
		$regex	= '/
			(
				(?: [\x00-\x7F]                  # single-byte sequences   0xxxxxxx
				|   [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx';

		if($charset === 'utf8mb3' || $charset === 'utf8mb4'){
			$regex	.= '
			|   \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
				|   [\xE1-\xEC][\x80-\xBF]{2}
				|   \xED[\x80-\x9F][\x80-\xBF]
				|   [\xEE-\xEF][\x80-\xBF]{2}';
		}

		if($charset === 'utf8mb4'){
			$regex	.= '
				|    \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
				|    [\xF1-\xF3][\x80-\xBF]{3}
				|    \xF4[\x80-\x8F][\x80-\xBF]{2}';
		}

		$regex		.= '
			){1,40}                  # ...one or more times
			)
			| .                      # anything else
			/x';

		return preg_replace($regex, '$1', $this->text);
	}

	public function strip_4_byte_chars(){
		return preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $this->text);
		// return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $this->text);
	}

	public function strip_control_chars(){
		// 移除 除了 line feeds 和 carriage returns 所有控制字符
		return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F]/u', '', $this->text);
		// return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $this->text);
	}
}

class WPJAM_Array{
	private $data;

	public function __construct($data){
		$this->data	= $data;
	}

	public function get_data(){
		return $this->data;
	}

	public function first($callback=null){
		if($callback && is_callable($callback)){
			foreach($this->data as $key => $value){
				if(call_user_func($callback, $value, $key)){
					return $value;
				}
			}
		}else{
			return current($this->data);
		}
	}

	public function filter($callback, $mode=0, $arr=null){
		$arr	= $arr ?? $this->data;

		foreach($arr as $key => &$value){
			if(is_array($value)){
				$value	= $this->filter($callback, $mode, $value);
			}
		}

		return array_filter($arr, $callback, $mode);
	}

	public function get($key, $default=null){
		$keys	= is_array($key) ? $key : [$key];

		foreach($keys as $key){
			if(array_key_exists($key, $this->data)){
				return $this->data[$key];
			}
		}

		return $default;
	}

	public function pull($key, $default=null){
		$value	= $this->get($key, $default);

		$this->except($key);

		return $value;
	}

	public function push($data, $key=null){
		if(!is_array($data) || wp_is_numeric_array($data)){
			return false;
		}

		if(is_null($key)){
			$offset	= false;
		}else{
			$offset	= array_search($key, array_keys($this->data), true);
		}

		if($offset !== false){
			$this->data	= array_merge(array_slice($this->data, 0, $offset), $data, array_slice($this->data, $offset));
		}else{
			$this->data	= array_merge($this->data, $data);
		}

		return true;
	}

	public function merge($data, $arr=null){
		$arr	= $arr ?? $this->data;

		if(wp_is_numeric_array($arr) && wp_is_numeric_array($data)){
			return $arr + $data;
		}

		foreach($data as $key => $value){
			if((is_array($value) && !wp_is_numeric_array($value))
				&& (isset($arr[$key]) && is_array($arr[$key]) && !wp_is_numeric_array($arr[$key]))
			){
				$arr[$key]	= $this->merge($value, $arr[$key]);
			}else{
				$arr[$key]	= $value;
			}
		}

		return $arr;
	}

	public function except(...$keys){
		if(is_array($keys[0])){
			$keys	= $keys[0];
		}

		foreach($keys as $key){
			unset($this->data[$key]);
		}

		return $this->data;
	}

	function list_filter($args=[], $operator='AND'){	// 增强 wp_list_filter ，支持 show_if 判断
		if(empty($args)){
			return $this->data;
		}

		$operator	= strtoupper($operator);

		if(!in_array($operator, ['AND', 'OR', 'NOT'], true)){
			return [];
		}

		$count		= count($args);
		$filtered	= [];

		foreach($this->data as $key => $item){
			$matched	= 0;

			foreach($args as $m_key => $m_value){
				if(is_array($m_value) && !wp_is_numeric_array($m_value)){
					$show_if	= wp_parse_args($m_value, ['key'=>$m_key]);
				}else{
					$show_if	= ['key'=>$m_key, 'value'=>$m_value];
				}

				if(wpjam_show_if($item, $show_if)){
					$matched++;
				}
			}

			if(('AND' === $operator && $matched === $count)
				|| ('OR' === $operator && $matched > 0)
				|| ('NOT' === $operator && 0 === $matched)
			){
				$filtered[$key]	= $item;
			}
		}

		return $filtered;
	}

	public function list_sort($orderby='order', $order='DESC'){
		$index	= 0;
		$scores	= [];

		foreach($this->data as $key => $item){
			$value	= is_object($item) ? ($item->$orderby ?? 10) : ($item[$orderby] ?? 10);
			$index 	= $index+1;

			$scores[$key]	= [$orderby=>$value, 'index'=>$index];
		}

		$scores	= wp_list_sort($scores, [$orderby=>$order, 'index'=>'ASC'], '', true);

		return wp_array_slice_assoc($this->data, array_keys($scores));
	}

	public function list_flatten($depth=0, $args=[], $list=null){
		$list	= $list ?? $this->data;
		$flat	= [];

		$name		= $args['name'] ?? 'name'; 
		$children	= $args['children'] ?? 'children'; 

		foreach($list as $item){
			$item[$name]	= str_repeat('&emsp;', $depth).$item[$name];
			$flat[]			= $item;

			if(!empty($item[$children])){
				$flat	= array_merge($flat, $this->list_flatten($depth+1, $args, $item[$children]));
			}
		}

		return $flat;
	}

	public static function create($data){
		if(!is_array($data)){
			return null;
		}

		return new self($data);
	}
}

class WPJAM_Bit{
	protected $bit;

	public function __construct($bit=0){
		$this->bit	= $bit;
	}

	public function __get($name){
		return $name == 'bit' ? $this->bit : null;
	}

	public function __isset($name){
		return $name == 'bit';
	}

	public function has($bit){
		return ($this->bit & $bit) == $bit;
	}

	public function add($bit){
		$this->bit = $this->bit | (int)$bit;

		return $this->bit;
	}

	public function remove($bit){
		$this->bit = $this->bit & (~(int)$bit);

		return $this->bit;
	}
}

class WPJAM_Crypt{
	private $method		= 'aes-256-cbc';
	private $key 		= '';
	private $iv			= '';
	private $options	= OPENSSL_ZERO_PADDING;
	private $block_size	= 32;	// 注意 PHP 默认 aes cbc 算法的 block size 都是 16 位

	public function __construct($args=[]){
		foreach ($args as $key => $value) {
			if(in_array($key, ['key', 'method', 'options', 'iv', 'block_size'])){
				$this->$key	= $value;
			}
		}
	}

	public function encrypt($text){
		if($this->options == OPENSSL_ZERO_PADDING && $this->block_size){
			$text	= $this->pkcs7_pad($text, $this->block_size);	//使用自定义的填充方式对明文进行补位填充
		}

		return openssl_encrypt($text, $this->method, $this->key, $this->options, $this->iv);
	}

	public function decrypt($encrypted_text){
		try{
			$text	= openssl_decrypt($encrypted_text, $this->method, $this->key, $this->options, $this->iv);
		}catch(Exception $e){
			return new WP_Error('decrypt_aes_failed', 'aes 解密失败');
		}

		if($this->options == OPENSSL_ZERO_PADDING && $this->block_size){
			$text	= $this->pkcs7_unpad($text, $this->block_size);	//去除补位字符
		}

		return $text;
	}

	public static function pkcs7_pad($text, $block_size=32){	//对需要加密的明文进行填充 pkcs#7 补位
		//计算需要填充的位数
		$amount_to_pad	= $block_size - (strlen($text) % $block_size);
		$amount_to_pad	= $amount_to_pad ?: $block_size;

		//获得补位所用的字符
		return $text . str_repeat(chr($amount_to_pad), $amount_to_pad);
	}

	public static function pkcs7_unpad($text, $block_size){	//对解密后的明文进行补位删除
		$pad	= ord(substr($text, -1));

		if($pad < 1 || $pad > $block_size){
			$pad	= 0;
		}

		return substr($text, 0, (strlen($text) - $pad));
	}

	public static function weixin_pad($text, $appid){
		$random = self::generate_random_string(16);		//获得16位随机字符串，填充到明文之前
		return $random.pack("N", strlen($text)).$text.$appid;
	}

	public static function weixin_unpad($text, &$appid){	//去除16位随机字符串,网络字节序和AppId
		$text		= substr($text, 16, strlen($text));
		$len_list	= unpack("N", substr($text, 0, 4));
		$text_len	= $len_list[1];
		$appid		= substr($text, $text_len + 4);
		return substr($text, 4, $text_len);
	}

	public static function sha1(...$args){
		sort($args, SORT_STRING);

		return sha1(implode($args));
	}

	public static function generate_weixin_signature($token, &$timestamp='', &$nonce='', $encrypt_msg=''){
		$timestamp	= $timestamp ?: time();
		$nonce		= $nonce ?: self::generate_random_string(8);
		return self::sha1($encrypt_msg, $token, $timestamp, $nonce);
	}

	public static function generate_random_string($length){
		$alphabet	= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		$max		= strlen($alphabet);
		$token		= '';

		for($i = 0; $i < $length; $i++){
			$token	.= $alphabet[self::crypto_rand_secure(0, $max - 1)];
		}

		return $token;
	}

	private static function crypto_rand_secure($min, $max){
		$range	= $max - $min;

		if($range < 1){
			return $min;
		}

		$log	= ceil(log($range, 2));
		$bytes	= (int)($log / 8) + 1;		// length in bytes
		$bits	= (int)$log + 1;			// length in bits
		$filter	= (int)(1 << $bits) - 1;	// set all lower bits to 1

		do {
			$rnd	= hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd	= $rnd & $filter;	// discard irrelevant bits
		}while($rnd > $range);

		return $min + $rnd;
	}
}

class WPJAM_List_Cache{
	private $key;
	private $group;

	public function __construct($key, $group='wpjam_list_cache', $global=true){
		$this->key		= $key;
		$this->group	= $group;

		if($global){
			wp_cache_add_global_groups($group);
		}
	}

	// get($k);
	// add($item, $k)
	// empty()
	// set($k, $item)	update
	// remove($k) delete
	// increment($k, $offset=1)
	// decrement($k, $offset=1)
	public function __call($method, $args){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items	= wp_cache_get_with_cas($this->key, $this->group, $cas_token);

			if($items === false){
				wp_cache_add($this->key, [], $this->group, DAY_IN_SECONDS);

				$items	= wp_cache_get_with_cas($this->key, $this->group, $cas_token);
			}

			if($method == 'add'){
				$k	= $args[1] ?? null;
			}elseif($method != 'empty'){
				$k	= $args[0];
			}

			if($method == 'get'){
				return $items[$k] ?? false;
			}elseif($method == 'empty'){
				if($items == []){
					return [];
				}else{
					$items_reserved	= $items; 
					$items	= [];
				}
			}elseif($method == 'add'){
				if($k !== null){
					if(isset($items[$k])){
						return false;
					}

					$items[$k]	= $args[0];
				}else{
					$items[]	= $args[0];
				}
			}elseif($method == 'set' || $method == 'update'){
				$items[$k]	= $args[1];
			}elseif($method == 'remove' || $method == 'delete'){
				if(!isset($items[$k])){
					return false;
				}

				unset($items[$k]);
			}elseif($method == 'increment' || $method == 'decrement'){
				$offset	= $args[1] ?? 1;

				if($method == 'decrement'){
					$offset	= 0 - $offset;
				}

				$items[$k]	= $items[$k] ?? 0;
				$items[$k]	+= $offset;
			}else{
				return;
			}

			$result	= wp_cache_cas($cas_token, $this->key, $items, $this->group, DAY_IN_SECONDS);

			$retry	 -= 1;
		}while(!$result && $retry > 0);

		if($method == 'empty'){
			if($result){
				return $items_reserved;
			}else{
				return false;
			}
		}else{
			return $result;
		}
	}
}

class WPJAM_Cache{
	/* HTML 片段缓存
	Usage:

	if (!WPJAM_Cache::output('unique-key')) {
		functions_that_do_stuff_live();
		these_should_echo();
		WPJAM_Cache::store(3600);
	}
	*/
	public static function output($key) {
		$output	= get_transient($key);

		if(!empty($output)) {
			echo $output;
			return true;
		}else{
			ob_start();
			return false;
		}
	}

	public static function store($key, $cache_time='600') {
		$output = ob_get_flush();
		set_transient($key, $output, $cache_time);
		echo $output;
	}
}

class WPJAM_Image{
	public static function is_image($img_url){
		$ext_types	= wp_get_ext_types();
		$img_exts	= $ext_types['image'];
		$img_parts	= explode('?', $img_url);
		$img_url	= wpjam_remove_postfix($img_parts[0], '#');

		return preg_match('/\.('.implode('|', $img_exts).')$/i', $img_url);
	}

	public static function is_external($img_url, $scene=''){
		$site_url	= str_replace(['http://', 'https://'], '//', site_url());
		$status		= strpos($img_url, $site_url) === false;	

		return apply_filters('wpjam_is_external_image', $status, $img_url, $scene);
	}

	public static function parse_args($args, &$return){
		$args	= wp_parse_args($args, [
			'name'		=> '',
			'media'		=> false,
			'post_id'	=> 0,
		]);

		if(!empty($args['return'])){
			$return	= $args['return'];
		}else{
			$return	= $args['media'] ? 'id' : 'file';
		}

		return $args;
	}

	public static function return($id, $return){
		if($return == 'file'){
			return get_attached_file($id);
		}elseif($return == 'url'){
			return wp_get_attachment_url($id);
		}

		return $id;
	}

	public static function upload_bits($bits, $args){
		$args	= self::parse_args($args, $return);
		$upload	= wp_upload_bits($args['name'], null, $bits);

		if(!empty($upload['error'])){
			return new WP_Error('upload_bits_error', $upload['error']);
		}

		if($args['media']){
			$id	= wp_insert_attachment([
				'post_title'		=> explode('.', $args['name'])[0],
				'post_content'		=> '',
				'post_type'			=> 'attachment',
				'post_parent'		=> $args['post_id'],
				'post_mime_type'	=> $upload['type'],
				'guid'				=> $upload['url'],
			], $upload['file'], $args['post_id']);

			if(!is_wp_error($id)){
				wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
			}

			if($return == 'id'){
				return $id;
			}
		}

		return $upload[$return] ?? $upload;
	}

	public static function download_external($img_url, $args){
		$args	= self::parse_args($args, $return);
		$metas	= wpjam_get_by_meta('post', 'source_url', $img_url);

		if($metas){
			$id	= current($metas)['post_id'];

			if(get_post_type($id) == 'attachment'){
				return self::return($id, $return);
			}
		}

		$tmp_file	= download_url($img_url);

		if(is_wp_error($tmp_file)){
			return $tmp_file;
		}

		$name	= $args['name'];

		if(empty($name)){
			$type	= wp_get_image_mime($tmp_file);
			$name	= md5($img_url).'.'.(explode('/', $type)[1]);
		}

		$file_array	= ['name'=>$name,	'tmp_name'=>$tmp_file];

		if($args['media']){
			$id		= media_handle_sideload($file_array, $args['post_id']);

			if(is_wp_error($id)){
				@unlink($tmp_file);
			}else{
				update_post_meta($id, 'source_url', $img_url);
			}

			return self::return($id, $return);
		}else{
			$file	= wp_handle_sideload($file_array, ['test_form'=>false]);

			if(isset($file['error'])){
				@unlink($tmp_file);
				return new WP_Error('upload_error', $file['error']);
			}

			return $file[$return] ?? $file;
		}
	}

	public static function fetch_external(&$img_urls, $args){
		$args	= wp_parse_args($args, ['post_id'=>0, 'media'=>true, 'return'=>'url']);
		$search	= $replace	= [];
		
		foreach($img_urls as $i => $img_url){
			if($img_url && self::is_external($img_url, 'fetch')){
				$download	= self::download_external($img_url, $args);

				if(!is_wp_error($download)){
					$search[]	= $img_url;
					$replace[]	= $download;
				}	
			}
		}

		$img_urls	= $search;

		return $replace;
	}
}

class WPJAM_Notice{
	private $id;
	private $type;

	private function __construct($type='', $id=0){
		$this->type	= $type;
		$this->id	= $id;
	}

	public function get_store_key(){
		if(str_starts_with($this->type, 'user_')){
			return 'wpjam_'.wpjam_remove_prefix($this->type, 'user_').'s';
		}else{
			return 'wpjam_notices';
		}
	}

	public function __get($key){
		if($key == 'data'){
			$store_key	= $this->get_store_key();

			if($this->type == 'admin_notice'){
				$data	= is_multisite() ? get_blog_option($this->id, $store_key) : get_option($store_key);
			}else{
				$data	= get_user_meta($this->id, $store_key, true);
			}

			return $data ? array_filter($data, [$this, 'filter_item']) : [];
		}
	}

	public function __set($key, $value){
		if($key == 'data'){
			$store_key	= $this->get_store_key();

			if($this->type == 'admin_notice'){
				if(empty($value)){
					return is_multisite() ? delete_blog_option($this->id, $store_key) : delete_option($store_key);
				}else{
					return is_multisite() ? update_blog_option($this->id, $store_key, $value) : update_option($store_key, $value);
				}
			}else{
				if(empty($value)){
					return delete_user_meta($this->id, $store_key);
				}else{
					return update_user_meta($this->id, $store_key, $value);
				}
			}
		}
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function filter_item($item){
		if($item['time'] > time() - MONTH_IN_SECONDS * 3){
			return trim($item['notice']);
		}

		return false;
	}

	public function insert($item){
		$data	= $this->data;

		if(!is_array($item)){
			$item = ['notice'=>$item];
		}

		$key	= $item['key'] ?? '';
		$key	= $key ?: md5(maybe_serialize($item));

		$data[$key]	= wp_parse_args($item, ['notice'=>'', 'type'=>'error', 'time'=>time()]);

		$this->data	= $data;

		return true;
	}

	public function update($key, $item){
		if(isset($this->data[$key])){
			$this->data[$key]	= $item;
		}

		return true;
	}

	public function delete($key){
		$this->data	= wpjam_array_except($this->data, $key);

		return true;
	}

	private static $instances	= [];

	public static function get_instance($type, $id){
		$key	= $type.':'.$id;

		if(!isset(self::$instances[$key])){
			self::$instances[$key] = new self($type, $id);
		}

		return self::$instances[$key];
	}

	public static function get_current($type){
		if($type == 'admin_notice'){
			return self::get_instance($type, get_current_blog_id());
		}else{
			return self::get_instance($type, get_current_user_id());
		}
	}

	public static function delete_notice($notice_key){
		self::get_current('user_notice')->delete($notice_key);

		if(current_user_can('manage_options')){
			self::get_current('admin_notice')->delete($notice_key);
		}
	}

	public static function get_notices(){
		$notices	= (self::get_current('user_notice'))->data;

		if(current_user_can('manage_options')){
			$notices	= array_merge($notices, (self::get_current('admin_notice'))->data);
		}

		if($notices){
			uasort($notices, function($n, $m){ return $m['time'] <=> $n['time']; });
		}

		return $notices;
	}

	public static function ajax_delete(){
		$notice_key = wpjam_get_data_parameter('notice_key');
		$message_id = wpjam_get_data_parameter('message_id');

		if($notice_key){
			self::delete_notice($notice_key);

			wpjam_send_json(['notice_key'=>$notice_key]);
		}
	}

	public static function on_admin_notices(){
		$notice_key	= wpjam_get_parameter('notice_key');

		if($notice_key){
			self::delete_notice($notice_key);
		}

		$modal_notice	= '';

		foreach(self::get_notices() as $notice_key => $notice){
			$notice = wp_parse_args($notice, [
				'type'		=> 'info',
				'class'		=> 'is-dismissible',
				'admin_url'	=> '',
				'notice'	=> '',
				'title'		=> '',
				'modal'		=> 0,
			]);

			$admin_notice	= trim($notice['notice']);

			if($notice['admin_url']){
				$admin_notice	.= $notice['modal'] ? "\n\n" : ' ';
				$admin_notice	.= '<a style="text-decoration:none;" href="'.add_query_arg(compact('notice_key'), home_url($notice['admin_url'])).'">点击查看<span class="dashicons dashicons-arrow-right-alt"></span></a>';
			}

			$admin_notice	= wpautop($admin_notice).wpjam_get_page_button('delete_notice', ['data'=>compact('notice_key')]);

			if($notice['modal']){
				if(empty($modal_notice)){	// 弹窗每次只显示一条
					$modal_notice	= $admin_notice;
					$modal_title	= $notice['title'] ?: '消息';

					echo '<div id="notice_modal" class="hidden" data-title="'.esc_attr($modal_title).'">'.$modal_notice.'</div>';
				}
			}else{
				echo '<div class="notice notice-'.$notice['type'].' '.$notice['class'].'">'.$admin_notice.'</div>';
			}
		}
	}

	public static function add($item){	// 兼容函数
		return wpjam_add_admin_notice($item);
	}
}

class WPJAM_Var{
	public static function get_ip(){
		return $_SERVER['REMOTE_ADDR'] ??'';
	}

	public static function get_user_agent(){
		return $_SERVER['HTTP_USER_AGENT'] ?? '';
	}

	public static function get_referer(){
		return $_SERVER['HTTP_REFERER'] ?? '';
	}

	public static function parse_ip($ip=''){
		$ip	= $ip ?: self::get_ip();

		if($ip == 'unknown'){
			return false;
		}

		$ipdata	= IP::find($ip);

		return [
			'ip'		=> $ip,
			'country'	=> $ipdata['0'] ?? '',
			'region'	=> $ipdata['1'] ?? '',
			'city'		=> $ipdata['2'] ?? '',
			'isp'		=> '',
		];
	}

	public static function parse_user_agent($user_agent='', $referer=''){
		$user_agent	= $user_agent ?: self::get_user_agent();
		$user_agent	= $user_agent.' ';	// 为了特殊情况好匹配
		$referer	= $referer ?: self::get_referer();

		$os = $device =  $app = $browser = '';
		$os_version = $browser_version = $app_version = 0;

		if(strpos($user_agent, 'iPhone') !== false){
			$device	= 'iPhone';
			$os 	= 'iOS';
		}elseif(strpos($user_agent, 'iPad') !== false){
			$device	= 'iPad';
			$os 	= 'iOS';
		}elseif(strpos($user_agent, 'iPod') !== false){
			$device	= 'iPod';
			$os 	= 'iOS';
		}elseif(strpos($user_agent, 'Android') !== false){
			$os		= 'Android';

			if(preg_match('/Android ([0-9\.]{1,}?); (.*?) Build\/(.*?)[\)\s;]{1}/i', $user_agent, $matches)){
				if(!empty($matches[1]) && !empty($matches[2])){
					$os_version	= trim($matches[1]);

					$device		= $matches[2];

					if(strpos($device,';')!==false){
						$device	= substr($device, strpos($device,';')+1, strlen($device)-strpos($device,';'));
					}

					$device		= trim($device);
					// $build	= trim($matches[3]);
				}
			}
		}elseif(stripos($user_agent, 'Windows NT')){
			$os		= 'Windows';
		}elseif(stripos($user_agent, 'Macintosh')){
			$os		= 'Macintosh';
		}elseif(stripos($user_agent, 'Windows Phone')){
			$os		= 'Windows Phone';
		}elseif(stripos($user_agent, 'BlackBerry') || stripos($user_agent, 'BB10')){
			$os		= 'BlackBerry';
		}elseif(stripos($user_agent, 'Symbian')){
			$os		= 'Symbian';
		}else{
			$os		= 'unknown';
		}

		if($os == 'iOS'){
			if(preg_match('/OS (.*?) like Mac OS X[\)]{1}/i', $user_agent, $matches)){
				$os_version	= (float)(trim(str_replace('_', '.', $matches[1])));
			}
		}

		if(strpos($user_agent, 'MicroMessenger') !== false){
			if(strpos($referer, 'https://servicewechat.com') !== false){
				$app	= 'weapp';
			}else{
				$app	= 'weixin';
			}

			if(preg_match('/MicroMessenger\/(.*?)\s/', $user_agent, $matches)){
				$app_version = $matches[1];
			}

			if(preg_match('/NetType\/(.*?)\s/', $user_agent, $matches)){
				$net_type = $matches[1];
			}
		}elseif(strpos($user_agent, 'ToutiaoMicroApp') !== false || strpos($referer, 'https://tmaservice.developer.toutiao.com') !== false){
			$app	= 'bytedance';
		}

		if(strpos($user_agent, 'Lynx') !== false){
			$browser	= 'lynx';
		}elseif(stripos($user_agent, 'safari') !== false){
			$browser	= 'safari';

			if(preg_match('/Version\/(.*?)\s/i', $user_agent, $matches)){
				$browser_version	= (float)(trim($matches[1]));
			}
		}elseif(strpos($user_agent, 'Edge') !== false){
			$browser	= 'edge';

			if(preg_match('/Edge\/(.*?)\s/i', $user_agent, $matches)){
				$browser_version	= (float)(trim($matches[1]));
			}
		}elseif(stripos($user_agent, 'chrome')){
			$browser	= 'chrome';

			if(preg_match('/Chrome\/(.*?)\s/i', $user_agent, $matches)){
				$browser_version	= (float)(trim($matches[1]));
			}
		}elseif(stripos($user_agent, 'Firefox') !== false){
			$browser	= 'firefox';

			if(preg_match('/Firefox\/(.*?)\s/i', $user_agent, $matches)){
				$browser_version	= (float)(trim($matches[1]));
			}
		}elseif(strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false){
			$browser	= 'ie';
		}elseif(strpos($user_agent, 'Gecko') !== false){
			$browser	= 'gecko';
		}elseif(strpos($user_agent, 'Opera') !== false){
			$browser	= 'opera';
		}

		return compact('os', 'device', 'app', 'browser', 'os_version', 'browser_version', 'app_version');
	}
}

class IP{
	private static $ip = null;
	private static $fp = null;
	private static $offset = null;
	private static $index = null;
	private static $cached = [];

	public static function find($ip){
		if (empty( $ip ) === true) {
			return 'N/A';
		}

		$nip	= gethostbyname($ip);
		$ipdot	= explode('.', $nip);

		if ($ipdot[0] < 0 || $ipdot[0] > 255 || count($ipdot) !== 4) {
			return 'N/A';
		}

		if (isset( self::$cached[$nip] ) === true) {
			return self::$cached[$nip];
		}

		if (self::$fp === null) {
			self::init();
		}

		$nip2 = pack('N', ip2long($nip));

		$tmp_offset	= (int) $ipdot[0] * 4;
		$start		= unpack('Vlen',
			self::$index[$tmp_offset].self::$index[$tmp_offset + 1].self::$index[$tmp_offset + 2].self::$index[$tmp_offset + 3]);

		$index_offset = $index_length = null;
		$max_comp_len = self::$offset['len'] - 1024 - 4;
		for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8) {
			if (self::$index[$start].self::$index[$start+1].self::$index[$start+2].self::$index[$start+3] >= $nip2) {
				$index_offset = unpack('Vlen',
					self::$index[$start+4].self::$index[$start+5].self::$index[$start+6]."\x0");
				$index_length = unpack('Clen', self::$index[$start+7]);

				break;
			}
		}

		if ($index_offset === null) {
			return 'N/A';
		}

		fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 1024);

		self::$cached[$nip] = explode("\t", fread(self::$fp, $index_length['len']));

		return self::$cached[$nip];
	}

	private static function init(){
		if(self::$fp === null){
			self::$ip = new self();

			self::$fp = fopen(WP_CONTENT_DIR.'/uploads/17monipdb.dat', 'rb');
			if (self::$fp === false) {
				throw new Exception('Invalid 17monipdb.dat file!');
			}

			self::$offset = unpack('Nlen', fread(self::$fp, 4));
			if (self::$offset['len'] < 4) {
				throw new Exception('Invalid 17monipdb.dat file!');
			}

			self::$index = fread(self::$fp, self::$offset['len'] - 4);
		}
	}

	public function __destruct(){
		if(self::$fp !== null){
			fclose(self::$fp);
		}
	}
}