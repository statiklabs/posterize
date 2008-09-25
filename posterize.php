<?php
/*
Plugin Name: Posterize
Plugin URI: http://yansarazin.com/plugins/posterize
Description: This plugin will automatically send an email to Posterous that will link to your post. 
Version: 1.0.1
Author: Yan Sarazin
Author URI: http://yansarazin.com
*/

/*  Copyright 2008  Yan Sarazin  (email : yan.sarazin@gmail.com)

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



function email_posterous($post_ID)  {
    global $userdata;
    get_currentuserinfo();
      
    $post = get_post($post_ID);
    $recipient = 'posterous@posterous.com';
    $subject = $post->post_title;
    $body = '<a href="'.get_permalink($post_ID).'">'.$post->post_title.'</a>';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= "From: " . $userdata->user_email . "\n" . "Return-Path: " . $userdata->user_email . "\n" . "Reply-To: " . $userdata->user_email . "\n";
    
    if($post->post_type == 'post'){
    	mail($recipient, $subject, $body, $headers);
    }
    return $post_ID;
}

add_action('draft_to_publish', 'email_posterous');
add_action('pending_to_publish', 'email_posterous');
?>
