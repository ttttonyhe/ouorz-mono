<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function wpjam_list_table( $args = array() ){
	return new WPJAM_List_Table( $args );
}

class WPJAM_List_Table extends WP_List_Table {

	private $columns;
	private $sortable_columns;
	private $options_columns;
	private $actions_column;
	private $bulk_actions;
	private $style;
	private $fixed;
	private $plural;
	private $singular;
	private $per_page = 0;
	

	public function __construct( $args = array() ){
		$current_screen	= get_current_screen();

		$args = wp_parse_args( $args, array(
			'screen'				=> $current_screen,
			'plural'				=> '',
			'singular'				=> '',
			'columns'				=> array(),
			'sortable_columns'		=> array(),
			'actions_column'		=> '',
			'bulk_actions'			=> array(),
			'item_callback'			=> '',
			'per_page'				=> array(),
			'views'					=> '',
			'style'					=> '',
			'fixed'					=> true
		) );

		$this->plural				= $args['plural'];
		$this->singular				= $args['singular'];
		$this->columns				= $args['columns'];
		$this->sortable_columns		= $args['sortable_columns'];
		$this->bulk_actions			= $args['bulk_actions'];
		$this->actions_column		= $args['actions_column'];
		$this->style				= $args['style'];
		$this->fixed				= ($args['fixed'])?'fixed':'';

		$this->set_columns();

		if($args['per_page']){
			if(is_array($args['per_page'])){
				add_screen_option( 'per_page', $args['per_page']);	// 选项
			}elseif (is_numeric($args['per_page'])) {
				$this->per_page = $args['per_page'];				// 直接设定了值
			}
		}

		if($args['item_callback']){
			add_filter($this->singular.'_item_callback', $args['item_callback']);
		}

		if($args['views']){
			add_filter('views_'.$current_screen->id,$args['views']);
		}

		add_action('admin_head', array($this, 'admin_head'));

		parent::__construct( $args );
	}

	protected function get_table_classes() {
		return array( 'widefat', 'striped', $this->fixed, $this->plural);
	}

	public function admin_head(){
		echo '<style type="text/css">'.$this->style.'</style>';
	}

	public function get_plural(){
		return $this->plural;
	}

	public function get_singular(){
		return $this->singular;
	}

	public function get_columns(){
		return apply_filters($this->singular.'_columns', $this->columns);
	}

	public function get_sortable_columns(){
		return $this->sortable_columns;
	}

	public function get_bulk_actions() {
		return $this->bulk_actions;
	}

	public function get_per_page(){

		if($this->per_page){
			return $this->per_page;
		}

		$user_id	= get_current_user_id();
		$screen		= $this->screen;
		$per_page_option	= $screen->get_option('per_page', 'option');

		if($per_page_option){
			$per_page = get_user_meta($user_id, $per_page_option, true);
			
			if ( empty ( $per_page) || $per_page < 1 ) { 
			    $per_page = $screen->get_option( 'per_page', 'default' );
			}

			return $per_page;
		}else{
			return 50;
		}
	}

	public function get_offset(){
		return ($this->get_pagenum()-1) * $this->get_per_page();
	}

	public function get_limit(){
		return $this->get_offset().','.$this->get_per_page();
	}

	public function single_row( $item ) {
		// if($this->item_callback){
		// 	$item = call_user_func($this->item_callback, $item);
		// }

		$item = apply_filters( $this->singular.'_item_callback', (array)$item );

		if($this->options_columns){
			foreach ($this->options_columns as $key => $options) {
				$item[$key]	= isset($options[$item[$key]])?$options[$item[$key]]:$item[$key];
			}
		}

		static $row_class = '';
		$row_class	= ($row_class == '')?' class="alternate"':'';
		$style		= isset($item['style'])?' style="'.$item['style'].'"':'';

		echo '<tr' . $row_class . $style . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	private function set_columns(){
		$form_fields	= wpjam_get_form_fields(true);

		if(!$form_fields){
			return;
		}

		$new_columns = array();
		$this->options_columns	= array();

		if(isset($this->columns['cb'])){
			$new_columns['cb'] = $this->columns['cb'];
			unset($this->columns['cb']);
		}

		foreach($form_fields as $key => $field){
			if($field['type'] == 'fieldset'){
				foreach ($field['fields'] as $sub_key => $sub_field) {
					if(!empty($sub_field['show_admin_column'])){
						$new_columns[$sub_key] = $sub_field['title'];
						if(!empty($sub_field['options'])){
							$this->options_columns[$sub_key] = $sub_field['options'];
						}
						if(!empty($sub_field['sortable_column'])){
							$this->sortable_columns[$sub_key] = $sub_key;
						}
					}
				}
			}else{
				if(!empty($field['show_admin_column'])){
					$new_columns[$key] = $field['title'];
					if(!empty($field['options'])){
						$this->options_columns[$key] = $field['options'];
					}
					if(!empty($field['sortable_column'])){
						$this->sortable_columns[$key] = $key;
					}
				}
			}
		}

		$this->columns = array_merge($new_columns, $this->columns);
	}

	public function column_default($item, $column_name){
		if($this->actions_column == $column_name && isset($item['row_actions'])){
			return $item[$column_name].$this->row_actions($item['row_actions'], false);
		}else{
			return $item[$column_name];
		}
	}

	public function column_cb($item){
		$name = isset($item['name'])?strip_tags($item['name']):$item['id'];
		return '<label class="screen-reader-text" for="cb-select-' . $item['id'] . '">' . sprintf( __( 'Select %s' ), $name ) . '</label>'
				. '<input type="checkbox" name="ids[]" value="' . $item['id'] . '" id="cb-select-' . $item['id'] . '" />';
	}

	public function row_actions($actions, $always_visible = true){
		return parent::row_actions($actions, $always_visible);
	}

	public function prepare_items($items='', $total_items=0){
		$this->items	= $items;
		$per_page		= $this->get_per_page();

		if($total_items){
			$this->set_pagination_args( array(
				'total_items'	=> $total_items,
				'per_page'		=> $per_page,
				'total_pages'	=> ceil($total_items/$per_page)
			) );
		}
	}

	public function display($args = array()){
		global $plugin_page, $current_tab;

		if($wpjam_page 	= wpjam_get_admin_page($plugin_page)){
			$builtin_parent_pages	= wpjam_get_builtin_parent_pages();
			$parent_slug 			= isset($wpjam_page['parent_slug'])?$wpjam_page['parent_slug']:'';

			if($parent_slug && isset($builtin_parent_pages[$parent_slug])){
				$form_url 	= $builtin_parent_pages[$parent_slug];
			}else{
				$form_url	= 'admin.php';
			}

			$form_url	= (is_network_admin())?network_admin_url($form_url):admin_url($form_url);
		}

		extract( wp_parse_args( $args, array(
			'search'	=> true,
		) ) );

		wpjam_admin_errors();
		
		$this->views();
?>
<form action="<?php echo $form_url; ?>" method="get">
	<input type="hidden" id="page" name="page" value="<?php echo $plugin_page;?>">
	<?php if(isset($current_tab)){?>
	<input type="hidden" id="tab" name="tab" value="<?php echo $current_tab;?>">
	<?php } ?>
	<?php if(!empty($_GET['post_type'])){?>
	<input type="hidden" id="post_type" name="post_type" value="<?php echo $_GET['post_type'];?>">
	<?php } ?>
	<?php do_action( 'wpjam_list_table_hidden_fields', $plugin_page ); ?>
	<?php if( ($search && $this->_pagination_args) || isset($_GET['s']) ) {
		$this->search_box('搜索', $this->_args['singular']);
	} ?>
	<?php parent::display(); ?>
</form>
<?php
	}
}

add_filter('set-screen-option', 'wpjam_set_screen_option', 10, 3);
function wpjam_set_screen_option($status, $option, $value) {
	if ( isset($_GET['page']) ) {	// 如果插件页面就返回呗
		return $value;
	}else{
		return $status;
	}
}