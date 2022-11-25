<?php
Class WPJAM_Mcrypt{
	private $key;
	private $algorithm				= MCRYPT_RIJNDAEL_256;
	private $algorithm_directory	= '';
	private $mode 					= MCRYPT_MODE_ECB;
	private $mode_directory			= '';
	private $iv						= '';

	public function __construct($key, $args=array()){
		$this->key					= $key;
		$this->algorithm			= isset($args['algorithm'])?$args['algorithm']:$this->algorithm;
		$this->algorithm_directory	= isset($args['algorithm_directory'])?$args['algorithm_directory']:$this->algorithm_directory;
		$this->mode					= isset($args['mode'])?$args['mode']:$this->mode;
		$this->mode_directory		= isset($args['mode_directory'])?$args['mode_directory']:$this->mode_directory;
		$this->iv					= isset($args['iv'])?$args['iv']:'';
	}

	public function encrypt($text){
		$module		= mcrypt_module_open($this->algorithm, $this->algorithm_directory, $this->mode, $this->mode_directory);
		if(empty($this->iv)){
			$iv_size	= mcrypt_enc_get_iv_size($module);
			$this->iv	= mcrypt_create_iv($iv_size, MCRYPT_RAND);
		}

		mcrypt_generic_init($module, $this->key, $this->iv);

		$encrypted_text	= mcrypt_generic($module, $text);

		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);

		return trim(base64_encode($encrypted_text));
	}

	public function decrypt($encrypted_text){
		$encrypted_text	= base64_decode($encrypted_text);

		$module		= mcrypt_module_open($this->algorithm, $this->algorithm_directory, $this->mode, $this->mode_directory);
		
		if(empty($this->iv)){
			$iv_size	= mcrypt_enc_get_iv_size($module);
			$this->iv	= mcrypt_create_iv($iv_size, MCRYPT_RAND);
		}

		mcrypt_generic_init($module, $this->key, $this->iv);
		
		$decrypted_text	= mdecrypt_generic($module, $encrypted_text);

		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);

		return trim($decrypted_text);
	}
}