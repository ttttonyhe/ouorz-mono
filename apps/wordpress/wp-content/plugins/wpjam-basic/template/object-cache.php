<?php
if(
	// (isset($_POST['action']) && $_POST['action'] == 'query-attachments') ||
	(isset($_GET['debug']) && $_GET['debug'] == 'sql')
){
	return;
}

if(!defined('WP_CACHE_KEY_SALT')){
	define('WP_CACHE_KEY_SALT', '');
}

if(class_exists('Memcached')){
	function wp_cache_add($key, $data, $group='', $expire=0){
		global $wp_object_cache;
		return $wp_object_cache->add($key, $data, $group, (int)$expire);
	}

	function wp_cache_cas($cas_token, $key, $data, $group='', $expire=0){
		global $wp_object_cache;
		return $wp_object_cache->cas($cas_token, $key, $data, $group, (int)$expire);
	}

	function wp_cache_close(){
		global $wp_object_cache;
		return $wp_object_cache->close();
	}

	function wp_cache_decr($key, $offset=1, $group='', $initial_value=0, $expire=0){
		global $wp_object_cache;
		return $wp_object_cache->decr($key, $offset, $group, $initial_value, $expire);
	}

	function wp_cache_delete($key, $group=''){
		global $wp_object_cache;
		return $wp_object_cache->delete($key, $group);
	}

	function wp_cache_flush(){
		global $wp_object_cache;
		return $wp_object_cache->flush();
	}

	function wp_cache_get($key, $group='', $force=false, &$found=null){
		global $wp_object_cache;
		return $wp_object_cache->get($key, $group, $force, $found);
	}
	
	function wp_cache_get_multiple($keys, $group='', $force=false){
		global $wp_object_cache;
		return $wp_object_cache->get_multiple($keys, $group, $force);
	}

	function wp_cache_set_multiple($data, $group='', $expire=0){
		global $wp_object_cache;
		return $wp_object_cache->set_multiple($data, $group, $expire);
	}

	function wp_cache_delete_multiple($keys, $group=''){
		global $wp_object_cache;
		return $wp_object_cache->delete_multiple($keys, $group);
	}

	function wp_cache_get_with_cas($key, $group='', &$cas_token=null){
		global $wp_object_cache;
		return $wp_object_cache->get_with_cas($key, $group, $cas_token);
	}

	function wp_cache_incr($key, $offset=1, $group='', $initial_value=0, $expire=0){
		global $wp_object_cache;
		return $wp_object_cache->incr($key, $offset, $group, $initial_value, $expire);
	}

	if(!isset($_GET['debug']) || $_GET['debug'] != 'sql'){
		function wp_cache_init(){
			global $wp_object_cache;
			$wp_object_cache	= new WP_Object_Cache();
		}
	}

	function wp_cache_replace($key, $data, $group='', $expire=0){
		global $wp_object_cache;
		return $wp_object_cache->replace($key, $data, $group, (int)$expire);
	}

	function wp_cache_set($key, $data, $group='', $expire=0){
		global $wp_object_cache;
		return $wp_object_cache->set($key, $data, $group, (int)$expire);
	}

	function wp_cache_switch_to_blog($blog_id){
		global $wp_object_cache;
		return $wp_object_cache->switch_to_blog($blog_id);
	}

	function wp_cache_add_global_groups($groups){
		global $wp_object_cache;
		$wp_object_cache->add_global_groups($groups);
	}

	function wp_cache_add_non_persistent_groups($groups){
		global $wp_object_cache;
		$wp_object_cache->add_non_persistent_groups($groups);
	}

	function wp_cache_get_stats(){
		global $wp_object_cache;
		return $wp_object_cache->get_stats();
	}

	class WP_Object_Cache{
		private $cache 	= [];
		private $mc		= null;

		// public $cache_hits		= 0;
		// public $cache_misses	= 0;

		private $blog_prefix;
		private $global_prefix;

		protected $global_groups	= [];
		protected $non_persistent_groups	= [];

		protected function filter_expiration($expire, $group, $id){
			return apply_filters('wpjam_cache_expiration', $expire, $group, $id);
		}

		protected function action($action, $id, $group, $data, $expire=0){
			$key	= $this->build_key($id, $group);

			if($this->is_non_persistent_group($group)){
				if($action == 'add'){
					if($this->internal_exists($key)){
						return false;
					}
				}elseif($action == 'replace'){
					if(!$this->internal_exists($key)){
						return false;
					}
				}elseif($action == 'increment' || $action == 'decrement'){
					$current	= $this->get_from_internal($key);

					if($action == 'increment'){
						$data	= $current+$data;
					}else{
						$data	= $current-$data;
					}

					if($data < 0){
						$data	= 0;
					}
				}

				return $this->add_to_internal($key, $data);
			}else{
				$expire	= $this->filter_expiration($expire, $group, $id);

				if($action == 'set'){
					$result	= $this->mc->set($key, $data, $expire);
				}elseif($action == 'add'){
					$result	= $this->mc->add($key, $data, $expire);
				}elseif($action == 'replace'){
					$result	= $this->mc->replace($key, $data, $expire);
				}elseif($action == 'increment'){
					$result	= $data = $this->mc->increment($key, $data);
				}elseif($action == 'decrement'){
					$result	= $data = $this->mc->decrement($key, $data);
				}

				$result_code	= $this->mc->getResultCode();

				if($result_code === Memcached::RES_SUCCESS){
					$this->add_to_internal($key, $data);
				}else{
					$this->delete_from_internal($key);

					if($result_code != Memcached::RES_NOTSTORED){
						// trigger_error($result_code.' '.var_export($result,true));
					}
				}
				
				return $result;
			}
		}

		public function add($id, $data, $group='default', $expire=0){
			if(wp_suspend_cache_addition()){
				return false;
			}

			return $this->action('add', $id, $group, $data, $expire);
		}

		public function replace($id, $data, $group='default', $expire=0){
			return $this->action('replace', $id, $group, $data, $expire);
		}

		public function set($id, $data, $group='default', $expire=0){
			return $this->action('set', $id, $group, $data, $expire);
		}

		public function incr($id, $offset=1, $group='default', $initial_value=0, $expire=0){
			$this->action('add', $id, $group, $initial_value, $expire);
			return $this->action('increment', $id, $group, $offset);
		}

		public function decr($id, $offset=1, $group='default', $initial_value=0, $expire=0){
			$this->action('add', $id, $group, $initial_value, $expire);
			return $this->action('decrement', $id, $group, $offset);
		}

		public function cas($cas_token, $id, $data, $group='default', $expire=0){
			$key	= $this->build_key($id, $group);
			$expire	= $this->filter_expiration($expire, $group, $id);

			$this->delete_from_internal($key);

			return $this->mc->cas($cas_token, $key, $data, $expire);
		}

		public function delete($id, $group='default'){
			$key	= $this->build_key($id, $group);
		
			$this->delete_from_internal($key);

			if($this->is_non_persistent_group($group)){
				return true;
			}

			return $this->mc->delete($key);
		}

		public function flush(){
			$this->cache	= [];
			return $this->mc->flush();
		}

		public function get($id, $group='default', $force=false, &$found=null){
			$key	= $this->build_key($id, $group);
			
			if($this->internal_exists($key) && !$force){
				$found	= true;
				return $this->get_from_internal($key);
			}elseif($this->is_non_persistent_group($group)){
				$found	= false;
				return false;
			}
			
			$value	= $this->mc->get($key);
			
			if($this->mc->getResultCode() == Memcached::RES_NOTFOUND){
				$found	= false;
			}else{
				$found	= true;

				$this->add_to_internal($key, $value);
			}

			return $value;
		}

		public function get_with_cas($id, $group='default', &$cas_token=null){
			$key	= $this->build_key($id, $group);
			
			if(defined('Memcached::GET_EXTENDED')){
				$result	= $this->mc->get($key, null, Memcached::GET_EXTENDED);

				if($this->mc->getResultCode() == Memcached::RES_NOTFOUND){
					return false;
				}else{
					$cas_token 	= $result['cas'];
					return $result['value'];
				}
			}else{
				$result	= $this->mc->get($key, null, $cas_token);

				if($this->mc->getResultCode() == Memcached::RES_NOTFOUND){
					return false;
				}else{
					return $result;
				}
			}
		}

		public function get_multiple($ids, $group='default', $force=false){
			$caches	= [];
			$keys	= [];

			$persistent_group	= !$this->is_non_persistent_group($group);

			if(!$persistent_group || !$force){
				foreach($ids as $id){
					$key	= $this->build_key($id, $group);
					$value	= $this->get_from_internal($key);

					$caches[$id]	= $value;
					$keys[$id]		= $key;

					if($persistent_group && !$this->internal_exists($key)){
						$force	= true;
					}
				}

				if(!$persistent_group || !$force){
					return $caches;
				}
			}

			$results	= $this->mc->getMulti(array_values($keys));

			foreach($keys as $id => $key){
				if($results && isset($results[$key])){
					$caches[$id]	= $results[$key];
					$this->add_to_internal($key, $caches[$id]);
				}else{
					$caches[$id]	= false;
					$this->delete_from_internal($key);
				}
			}

			return $caches;
		}

		public function set_multiple($data, $group='default', $expire=0){
			$items	= [];

			foreach($data as $id => $value){
				$key			= $this->build_key($id, $group);
				$items[$key]	= $value;

				$this->add_to_internal($key, $value);
			}

			if($this->is_non_persistent_group($group)){
				return true;
			}

			$expire	= $this->filter_expiration($expire, $group, array_keys($data));
			$result	= $this->mc->setMulti($items, $expire);

			$result_code	= $this->mc->getResultCode();

			if($result_code !== Memcached::RES_SUCCESS){
				foreach($items as $key => $value){
					$this->delete_from_internal($key);
				}

				if($result_code != Memcached::RES_NOTSTORED){
					// trigger_error($result_code.' '.var_export($result,true));
				}
			}

			return $result;	
		}

		public function delete_multiple($ids, $group='default'){
			$keys	= [];

			foreach($ids as $id){
				$keys[]	= $key = $this->build_key($id, $group);

				$this->delete_from_internal($key);
			}

			if($this->is_non_persistent_group($group)){
				return true;
			}
			
			return $this->mc->deleteMulti($keys);
		}

		public function add_global_groups($groups){
			$groups	= (array)$groups;
			$groups	= array_fill_keys($groups, true);

			$this->global_groups	= array_merge($this->global_groups, $groups);
		}

		public function add_non_persistent_groups($groups){
			$groups	= (array)$groups;
			$groups	= array_fill_keys($groups, true);

			$this->non_persistent_groups	= array_merge($this->non_persistent_groups, $groups);
		}

		public function switch_to_blog($blog_id){
			if(is_multisite()){
				$blog_id	= (int)$blog_id;

				$this->blog_prefix	= $blog_id.':';
			}else{
				global $table_prefix;

				$this->blog_prefix	= $table_prefix.':';	
			}
		}

		private function internal_exists($key){
			return $this->cache && isset($this->cache[$key]) && $this->cache[$key] !== false;
		}

		private function get_from_internal($key){
			if(!$this->internal_exists($key)){
				return false;
			}

			if(is_object($this->cache[$key])){
				return clone $this->cache[$key];
			}

			return $this->cache[$key];
		}

		private function add_to_internal($key, $value){
			if(is_object($value)){
				$value	= clone $value;
			}

			$this->cache[$key]	= $value;

			return true;
		}

		private function delete_from_internal($key){
			unset($this->cache[$key]);
		}

		private function is_non_persistent_group($group){
			$group	= $group ?: 'default';
			return isset($this->non_persistent_groups[$group]);
		}

		private function build_key($id, $group='default'){
			$group	= $group ?: 'default';
			$prefix	= isset($this->global_groups[$group]) ? $this->global_prefix : $this->blog_prefix;

			return preg_replace('/\s+/', '', WP_CACHE_KEY_SALT.$prefix.$group.':'.$id);
		}

		public function get_stats(){
			return $this->mc->getStats();
		}

		public function get_mc(){
			return $this->mc;
		}

		public function failure_callback($host, $port){
		}

		public function close(){
			$this->mc->quit();
		}

		public function __construct(){
			$this->mc=new Memcached();
			$this->mc ->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

			if(!$this->mc->getServerList()){
				$this->mc->addServer('127.0.0.1', 11211, 100);
			}

			// global $memcached_servers;

			// if(isset($memcached_servers)){
			// 	$buckets=$memcached_servers;
			// }else{
			// 	$buckets=array('127.0.0.1');
			// }

			// reset($buckets);
			// if(is_int(key($buckets))){
			// 	$buckets=array('default' => $buckets);
			// }

			// foreach($buckets as $bucket => $servers){
			// 	$this->mc[ $bucket ]=new Memcached($bucket);
			// 	$this->mc[ $bucket ] ->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

			// 	if(count($this->mc[ $bucket ]->getServerList())){
			// 		continue;
			// 	}

			// 	$instances=array();
			// 	foreach($servers as $server){
			// 		@list($node, $port)=explode(':', $server.":");
			// 		if(empty($port)){
			// 			$port=ini_get('memcache.default_port');
			// 		}
			// 		$port=(int)($port);
			// 		if(! $port){
			// 			$port=11211;
			// 		}

			// 		$instances[]=array($node, $port, 1);
			// 	}
			// 	$this->mc[ $bucket ]->addServers($instances);
			// }

			if(is_multisite()){
				$this->blog_prefix		= get_current_blog_id().':';
				$this->global_prefix	= '';
			}else{
				global $table_prefix;

				$this->blog_prefix		= $table_prefix.':';

				if(defined('CUSTOM_USER_TABLE') && defined('CUSTOM_USER_META_TABLE')){
					$this->global_prefix	= '';
				}else{
					$this->global_prefix	= $table_prefix.':';	
				}	
			}

			// $this->cache_hits   =& $this->stats['get'];
			// $this->cache_misses =& $this->stats['add'];
		}
	}
}