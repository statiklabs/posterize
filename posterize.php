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
      $this->options = get_option('posterize');
      (!is_array($this->options) && !empty($this->options)) ? $this->options = unserialize($this->options) : $this->options = false;

      register_activation_hook(__FILE__, array(&$this, 'install'));	
      
      add_action('draft_to_publish', array(&$this, 'send'));
      add_action('pending_to_publish', array(&$this, 'send'));	
    
    if(is_admin()){
      wp_enqueue_style('style', WP_PLUGIN_URL . '/posterize/css/styles.css');
  		wp_enqueue_script("jquery"); 
  		wp_enqueue_script('javascript', WP_PLUGIN_URL . '/posterize/js/posterize.js');
      add_action('admin_menu', array(&$this, 'adminMenu'));
      add_filter('plugin_row_meta', array(&$this, 'links'),10,2);
      add_action( 'wp_ajax_get_sites', array(&$this, 'getSites' ));
		
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
          <div>
             <div class="fl">
               <label for="email">Posterous Email</label><br />
               <input type="text" name="email" id="email" value="<?php echo $this->options['email']; ?>" class="text-field" tabindex="1">
            </div>
            <div class="fr desc">The email address you use when login into the <a href="https://posterous.com/main/login">Posterous site</a> .</div>
            <div class="clear"></div>
          </div>
          <div>
             <div class="fl">
               <label for="password">Posterous Password</label><br />
               <input type="password" name="password" id="password" value="<?php echo $this->options['password'] ?>" class="text-field" tabindex="2">
            </div>
            <div class="fr desc">The password you use when login into the <a href="https://posterous.com/main/login">Posterous site</a>.</div>
            <div class="clear"></div>
          </div>
        </div>
        <div class="section">
          <h2>Posterous Site Info <a href="/wp-admin/admin-ajax.php?action=get_sites" class="get-sites-link">Refresh Site List</a></h2>
          <div class="site-info">
             <?php if(!is_array($this->options['sites']) && !empty($this->options['sites'])){?>
             <?php }else{ ?>
                <h4> <a href="/wp-admin/admin-ajax.php?action=get_sites" class="get-sites-link">Click here</a> to see list of your Posterous Sites.</h4>
            <?php }?>
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
        </div>
        <input type="submit" value="<?php _e('Save Settings') ?>" tabindex="5" />
      </form>
    <?php
  }
  
  function getSites() {
     global $userdata;

     $ch = curl_init(); 
     curl_setopt($ch, CURLOPT_URL, 'http://posterous.com/api/getsites'); 
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
     curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
     curl_setopt($ch, CURLOPT_USERPWD, "".$_POST['email'].":".$_POST['password']."") ;

     $xml = curl_exec($ch); 
     curl_close($ch);

     $xml = simplexml_load_string($xml);
     $data = get_object_vars($root);

     $sites = array();


     echo var_dump($xml);;
     die();
  }
  
  function send() {
     if($this->options['email']!='' && $this->options['password']){
        global $userdata;
        get_currentuserinfo();

        $post = get_post($post_ID);
        $title = urlencode($post->post_title);
   	  $tags = array();
   	  $posttags = get_the_tags($post_ID);
   	  if ($posttags) {
   		foreach($posttags as $tag) {
   			$tags[] = $tag->name; 
   		}
   	  }
        if($this->options['post_type']=="2"){
           $body = urlencode(nl2br($post->post_content));
        }else{
           $body = urlencode('<a href="'.get_permalink($post_ID).'">'.$post->post_title.'</a>');
        }
   	  $source = urlencode('Posterize');
   	  $sourceLink = urlencode('http://statikpulse.com/posterize');


        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, 'http://posterous.com/api/newpost?source='.$source.'&sourceLink='.$sourceLink.'&site_id='.$this->options['site_id'].'&title='.$title.'&body='.$body.'&tags='.implode(',', $tags)); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
        curl_setopt($ch, CURLOPT_USERPWD, "".$this->options['email'].":".$this->options['password']."") ;

        $data = curl_exec($ch); 
        curl_close($ch);
     }
   }
}

$posterize = new Posterize();

?>
