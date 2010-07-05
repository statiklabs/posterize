<?php
/*
Plugin Name: Posterize
Plugin URI: http://statikpulse.com/posterize
Description: This plugin will automatically cross-post your Wordpress blog entry to your Posterous site. 
Version: 2.2
Author: StatikPulse 
Author URI: http://statikpulse.com
*/

/*  Copyright 2010  StatikPulse  (email : yan@statikpulse.com)

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

class Posterize {

	function __construct() {
	  register_activation_hook(__FILE__, array(&$this, 'install'));		
    
    if(is_admin()){
      wp_enqueue_style('style', WP_PLUGIN_URL . '/posterize/css/styles.css');
  		wp_enqueue_script("jquery"); 
  		wp_enqueue_script('javascript', WP_PLUGIN_URL . '/posterize/js/posterize.js');
      add_action('admin_menu', array(&$this, 'adminMenu'));
      add_filter('plugin_row_meta', array(&$this, 'links'),10,2);
    }
  }
  
  function install() {
		//add default options
		$default = array(
					'email' => '',
					'password' => '',
					'post_type' => 1
					);
					
		if(!is_array($this->options)) {
			$this->options = array();
		}
		
		foreach($default as $k => $v) {
			if(empty($this->options[$k])) {
				$this->options[$k] = $v;
			}
		}
		
		update_option('posterize', serialize($this->options));
		
		return true;
	}
	
  function links($links, $file){
     if( $file == 'posterize/posterize.php') {
        $links[] = '<a href="' . admin_url( 'options-general.php?page=posterize-settings' ) . '">' . __('Settings') . '</a>';
        $links[] = '<a href="http://statikpulse.com/forums">' . __('Support') . '</a>';
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
		
			update_option('posterize', serialize($this->options));
			
			echo '<div id="message" class="updated fade"><p><strong>' . __('Options saved.', 'posterize') . '</strong></p></div>';
		}
    ?>
      <form method="post" action="/wp-admin/options-general.php?page=posterize-settings" id="posterize_settings_form" name="posterize_settings_form">      
        <?php wp_nonce_field('update-options'); ?>
        <h1>Posterize Settings</h1>
        <div class="section">
          <h2>Posterous Login Info</h2>
          <p>
            <label for="email">Posterous Email</label><br />
            <input type="text" name="email" id="email" value="<?php echo $this->options['email']; ?>" class="text-field" tabindex="1">
            <span>The email address you use when login into the <a href="https://posterous.com/main/login">Posterous site</a> .</span>
          </p>
          <p>
            <label for="password">Posterous Password</label><br />
            <input type="password" name="password" id="password" value="<?php echo $this->options['password'] ?>" class="text-field" tabindex="2">
            <span>The password you use when login into the <a href="https://posterous.com/main/login">Posterous site</a>.</span>
          </p>
        </div>
        
        <div class="section">
          <h2>Extras</h2>
          <p>
            <label for="post_type">Post Type</label><br />
            <input type="radio" name="post_type" id="post_type" value="1" tabindex="3" <?php if($this->options['post_type'] == "1") { ?>selected="selected"<?php }?>> Link back to post<br />
            <input type="radio" name="post_type" id="post_type" value="2" tabindex="4" <?php if($this->options['post_type'] == "2") { ?>selected="selected"<?php }?>> Post Full Content<br />
            <span>Select if you would rather post the full blog content or a link back to your post.</span>
          </p>
        </div>
        <input type="submit" value="<?php _e('Save Settings') ?>" tabindex="5" />
      </form>
    <?php
  }

}

$posterize = new Posterize();

?>
