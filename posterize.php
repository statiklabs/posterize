<?php
/*
Plugin Name: Posterize
Plugin URI: http://statikpulse.com/posterize
Description: This plugin will automatically cross-post your Wordpress blog entry to your Posterous site. 
Version: 2.2
Author: Yan Sarazin 
Author URI: http://statikpulse.com
*/

/*  Copyright 2011  Yan Sarazin  (email : yan@statikpulse.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require('posterous-api.php');

class Posterize {

	// version
	var $version;
	var $options = array();

	function __construct() {
		$defaults = array(
			'email' => '',
			'password' => '',
			'post_type' => 1, 
			'site_id' => 0,
			'sites' => array(),
			'posts' => array(),
			'excluded_cateogies' => array()
			);

		$this->options = is_array(get_option( 'posterize' ) ) ? array_merge($defaults, get_option('posterize')) : $defaults;

		add_action('publish_post', array(&$this, 'send'));

		if(is_admin()){
			wp_enqueue_style('style', WP_PLUGIN_URL . '/posterize/css/styles.css');
			wp_enqueue_script("jquery"); 
			wp_enqueue_script('javascript', WP_PLUGIN_URL . '/posterize/js/posterize.js');
			add_action('admin_menu', array(&$this, 'adminMenu'));
			add_filter('plugin_row_meta', array(&$this, 'links'),10,2);
			add_action( 'wp_ajax_get_sites', array(&$this, 'getSites' ));
		}
	}

	function links($links, $file){
		if( $file == 'posterize/posterize.php') {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=posterize-settings' ) . '">' . __('Settings') . '</a>';
			$links[] = '<a href="http://wordpress.org/tags/posterize?forum_id=10">' . __('Support') . '</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QC745TKR6AHBS" target="_blank">Donate</a>';
		}
		return $links;
	}

	function adminMenu(){
		add_options_page('Posterize Settings', 'Posterize', 'administrator', 'posterize-settings', array(&$this, 'adminPage') );
	}

	function adminPage(){
		if (!empty($_POST)) {

			$this->options['email'] = $_POST['email'];
			$this->options['password'] = $_POST['password'];
			$this->options["post_type"] = $_POST['post_type'];
			$this->options['excluded_categories'] = split(',', $_POST['excluded_categories']);
			if($_POST['site_id']){
				$this->options["site_id"] = $_POST['site_id'];
			}
			
			update_option('posterize', $this->options);

			echo '<div id="message" class="updated fade"><p><strong>' . __('Options saved.', 'posterize') . '</strong></p></div>';
		}
	?>
		<form method="post" action="<?php echo get_bloginfo('url'); ?>/wp-admin/options-general.php?page=posterize-settings" id="posterize_settings_form" name="posterize_settings_form">      
		<?php wp_nonce_field('update-options'); ?>
		<h1>Posterize Settings</h1>
		<div class="section">
			<h2>Posterous Login Info</h2>
			<div>
				<div class="fl">
					<label for="email">Posterous Email</label><br />
					<input type="text" name="email" id="email" value="<?php if ( isset( $this->options['email'] ) ) { echo $this->options['email']; } ?>" class="text-field" tabindex="1">
				</div>
				<div class="fr desc">The email address you use when login into the <a href="https://posterous.com/main/login">Posterous site</a> .</div>
				<div class="clear"></div>
			</div>
			<div>
				<div class="fl">
					<label for="password">Posterous Password</label><br />
					<input type="password" name="password" id="password" value="<?php if ( isset( $this->options['password'] ) ) { echo $this->options['password']; } ?>" class="text-field" tabindex="2">
				</div>
				<div class="fr desc">The password you use when login into the <a href="https://posterous.com/main/login">Posterous site</a>.</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="section">
			<h2>Posterous Site Info <a href="<?php echo get_bloginfo('url'); ?>/wp-admin/admin-ajax.php?action=get_sites" class="get-sites-link">Refresh Site List</a></h2>
			<div>
				<div class="fl site-info">
			<?php if(is_array($this->options['sites']) && !empty($this->options['sites'])){
				foreach($this->options["sites"] as $site) {
					?>
					<input type="radio" name="site_id" value="<?php echo $site["id"]; ?>" <?php if($this->options["site_id"] == $site["id"]){ ?>checked="true"<?php }?>> <?php echo $site["name"]; ?><br />
				<?php } ?>
				<?php }else{ ?>
					<h4> <a href="<?php echo get_bloginfo('url'); ?>/wp-admin/admin-ajax.php?action=get_sites" class="get-sites-link">Click here</a> to see list of your Posterous Sites.</h4>
					<?php }?>
				</div>
				<div class="fr desc">Select the Posterous site you would like to post to.</div>
				<div class="clear"></div>
			</div>
		</div>        
		<div class="section">
			<h2>Extras</h2>
			<div>
				<div class="fl">
					<label for="post_type">Post Type</label><br />
					<input type="radio" name="post_type" id="post_type" value="1" tabindex="3" <?php if($this->options['post_type'] == "1") { ?>checked="true"<?php }?>> 1. Link Back to Post<br />
					<input type="radio" name="post_type" id="post_type" value="2" tabindex="4" <?php if($this->options['post_type'] == "2") { ?>checked="true"<?php }?>> 2. Post Full Content<br />
				</div>
				<div class="fr desc">How you want the content to be posted on your Posterous site.
					<ol>
						<li>A link back to your blog post.</li>
						<li>The full content will be posted.</li>
					</ol>
				</div>
				<div class="clear"></div>
			</div>
			<div>
				<div class="fl">
					<label for="excluded_categories">Exclude Categories</label><br />
					<input type="text" name="excluded_categories" id="excluded_categories" value="<?php if ( isset( $this->options['excluded_categories'] ) ) { echo join(',', $this->options['excluded_categories']); } ?>" class="text-field" tabindex="5">
				</div>
				<div class="fr desc">Comma-delimited list of category IDs to exclude from auto posting. <span>Ex: 1,10,13</span>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<input type="submit" value="<?php _e('Save Settings') ?>" tabindex="5" class="button-secondary action" />
	</form>

		<?php
	}

	function getSites() {
		global $userdata;

		$api = new PosterousAPI($_POST['email'], $_POST['password']);
		header('Content-type: text/html');

		try {
			$xml = $api->getsites();
		}
		catch(Exception $e) {
			print $e->getMessage();
			die;
		}

		$this->options["sites"] = array();
		$html = array();
		foreach($xml->{'site'} as $site) {
			array_push($this->options["sites"], array(
				"id" => trim($site->id), 
				"name" => trim($site->name),
				"url" => trim($site->url), 
				"hostname" => trim($site->hostname), 
				"private" => trim($site->private),
				"primary" => trim($site->primary),
				"commentsenabled" => trim($site->commentsenabled)
				)
				);
			$html[] = '<input type="radio" name="site_id" value="' . trim($site->id) . '" '. ($site->id == $this->options["site_id"] || count($xml->{'site'}) == 1 ? 'checked="true"' : '') .'> ' . trim($site->name) . '<br />';
		}


		update_option('posterize', $this->options);
		echo implode($html);
		die();
	}

	function send($post_ID) {
		global $userdata;
		get_currentuserinfo();

		$post = get_post($post_ID);
		$categories = array();
		$postcategories = get_the_category($post_ID);
		if ($postcategories) {
			foreach($postcategories as $category) {
				$categories[] = $category->cat_ID; 
			}
		}
		$matches = array_intersect($categories, $this->options['excluded_categories']);
		if(empty($matches)){
			$tags = array();
			$posttags = get_the_tags($post_ID);
			if ($posttags) {
				foreach($posttags as $tag) {
					$tags[] = $tag->name; 
				}
			}

		
			if($this->options['post_type']=="2"){
				$body = nl2br($post->post_content);
			}else{
				$body = '<a href="'.get_permalink($post_ID).'">'.$post->post_title.'</a>';
			}

			$api = new PosterousAPI($this->options["email"], $this->options["password"]);

			try {
				if(array_key_exists($post->ID, $this->options['posts'])) {
					$xml = $api->updatepost( array( 'post_id' => $this->options['posts'][$post->ID]['id'], 'title' => $post->post_title, 'body' => $body ) );
				} else {
					$xml = $api->newpost( array( 'site_id' => $this->options['site_id'], 'title' => $post->post_title, 'body' => $body, 'tags' => implode(',', $tags), 'source' => 'Posterize', 'sourceLink' => 'http://statikpulse.com/posterize' ) );
					$this->options["posts"][$post->ID] = array("id" => trim($xml->{'post'}->id), "url" => trim($xml->{'post'}->url));
					update_option('posterize', $this->options);
				}

			}
			catch(Exception $e) {
				print $e->getMessage();
				die;
			}
		}
	}
}

$posterize = new Posterize();

?>
