<?php
/*
Plugin Name: SEO
Plugin URI: http://wpjam.net/item/wpjam-basic/
Description: 简单快捷的 WordPress SEO 功能。
Version: 1.0
*/

add_action("wp_head","wpjam_seo_head");
function wpjam_seo_head(){
	global $wpjam_seo_options;
	if(!$wpjam_seo_options){
		$wpjam_seo_options = get_option('wpjam_seo_options');
	}
	
	global $paged;

	if(is_search()){
		//do nothing
	}elseif(is_singular()){
		if(wpjam_basic_get_setting('seo_individual')){
			$post_id = get_the_ID();
			if(!$meta_description = addslashes_gpc(get_post_meta($post_id,'seo_description',true))){
				$meta_description = addslashes_gpc(get_the_excerpt());
			}
			
			if(!$meta_keywords = addslashes_gpc(get_post_meta($post_id,'seo_keywords',true))){
				$meta_keywords = array();
				if($tags = get_the_tags($post_id)){
					foreach ($tags as $tag ) {
			        	$meta_keywords[] = $tag->name;
			    	}
			    	if($meta_keywords){
			    		$meta_keywords = implode(',', $meta_keywords);
			    	}
				}
			}
		}else{
			$meta_description = addslashes_gpc(get_the_excerpt());
		}
		//$meta_author = get_the_author_meta( 'display_name', $post->post_author );
		//$meta_revised = get_the_modified_time('l, F jS, Y, g:i a');
	}elseif($paged<2){
		if((is_home())) {
			$meta_description	= wpjam_basic_get_setting('seo_home_description');
			$meta_keywords		= wpjam_basic_get_setting('seo_home_keywords');
			$module = get_query_var('module');
			if(empty($module)){
				$canonical_link 	= home_url();
			}
		}elseif(is_tag() || is_category() || is_tax()){
			if(wpjam_basic_get_setting('seo_individual')){
				$term_id		= get_queried_object_id();
				$meta_keywords	= addslashes_gpc(get_term_meta($term_id,'seo_keywords',true));
				if(!$meta_description = addslashes_gpc(get_term_meta($term_id,'seo_description',true))){
					$meta_description = wpjam_get_plain_text(term_description());
				}
			}else{
				$meta_description = wpjam_get_plain_text(term_description());
			}
		}elseif(is_post_type_archive()){
			$post_type = get_queried_object();
			//var_dump($post_type);
			//$post_type = get_post_type_object( get_query_var( 'post_type' ) );
			if($post_type){
				if(!$meta_description = wpjam_basic_get_setting('seo_'.$post_type->name.'_description')){
					$meta_description = $post_type->description;
				}
				$meta_keywords = wpjam_basic_get_setting('seo_'.$post_type->name.'_keywords');
			}
	    }
	}

	if(is_singular() || is_home() || is_tag() || is_category() || is_tax() || is_post_type_archive()){
		$meta_robots = "index,follow";
	}elseif(is_404() || is_search()){
		$meta_robots = "noindex,noarchive";
	}elseif(is_archive()){
		$meta_robots = "noarchive";
	}

	if ( !empty( $meta_description )){
		echo "<meta name='description' content='{$meta_description }' />\n";
	}
	if ( !empty( $meta_keywords )){
		echo "<meta name='keywords' content='{$meta_keywords }' />\n";
	}
	if ( !empty( $meta_robots ) ){
		echo "<meta name='robots' content='{$meta_robots}' />\n";
	}
	if ( !empty( $canonical_link ) ){
		echo "<link rel='canonical' href='{$canonical_link}' />\n";
	}
	//if ( !empty( $meta_author ) ){
	//	echo "<meta name='author' content='" . esc_attr( $meta_author ) . "' />\n";	
	//}
	//if ( !empty( $meta_revised ) ){
	//	echo "<meta name='revised' content='{$meta_revised}' />\n";
	//}
}

add_filter('wp_title', 'wpjam_seo_title',99);
function wpjam_seo_title($title){
	global $paged;
	if(is_singular()){
		if(wpjam_basic_get_setting('seo_individual')){
			if($seo_title = get_post_meta(get_the_ID(),'seo_title',true)){
				return $seo_title;
			}
		}
	}elseif($paged<2){
		if(is_home()){
			if(wpjam_basic_get_setting('seo_home_title')){
				return wpjam_basic_get_setting('seo_home_title');
			}
		}elseif(is_tag() || is_category() || is_tax()){
			if(wpjam_basic_get_setting('seo_individual')){
				$term_id	= get_queried_object_id();
				if($seo_title	= get_term_meta($term_id,'seo_title',true)){
					return $seo_title;
				}
			}
		}elseif(is_post_type_archive()){
			$post_type = get_queried_object();
			if(wpjam_basic_get_setting('seo_'.$post_type->name.'_title')){
				return wpjam_basic_get_setting('seo_'.$post_type->name.'_title');
			}
		}
	}
	return $title;
}

add_filter('robots_txt', 'wpjam_robots_txt',10,2);
function wpjam_robots_txt($output, $public){
	if ( '0' == $public ) {
		return "Disallow: /\n";
	} else {
		return wpjam_basic_get_setting('seo_robots');
	}
}

add_filter('wpjam_rewrite_rules', 'wpjam_seo_sitemap_rewrite_rules');
function wpjam_seo_sitemap_rewrite_rules($wpjam_rewrite_rules){
	$wpjam_rewrite_rules['sitemap\.xml$']				= 'index.php?module=sitemap';
	$wpjam_rewrite_rules['sitemap-([0-9]{4})\.xml$']	= 'index.php?module=sitemap&action=$matches[1]';
	return $wpjam_rewrite_rules;
}

add_filter('wpjam_template', 'wpjam_seo_sitemap_template', 10, 3);
function wpjam_seo_sitemap_template($wpjam_template, $module, $action){
	if($module == 'sitemap'){
		return WPJAM_BASIC_PLUGIN_DIR.'template/sitemap.php';
	}
	return $wpjam_template;
}