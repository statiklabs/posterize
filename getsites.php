<?php

global $userdata;

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, 'http://posterous.com/api/getsites'); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
curl_setopt($ch, CURLOPT_USERPWD, "".$_GET['posterous_email'].":".$_GET['posterous_password']."") ;

$xml = curl_exec($ch); 
curl_close($ch);

$root = simplexml_load_string($xml);
$data = get_object_vars($root);

$sites = array();

if(!$data['site']){
   print '<div class="error"><strong>Error:</strong> Check your username and password</div>';
}else{   

   foreach ($data['site'] as $keys => $values) {
      
       foreach ($values as $key => $value) {
                  $value = str_replace("\r\n", '<br />', $value);
                  $value = str_replace("\r", '<br />', $value);
                  $value = str_replace("\n", '<br />', $value);
                  $sites['site'][$keys][$key] = $value;
              }
    
   }
   $html = '<table border="0" cellspacing="1" cellpadding="5" class="sites_table"><tr><th>site id</th><th>Site Name</th></tr>';
   foreach($sites['site'] as $site){
      $html .= '<tr><td>'.$site['id'].'</td><td><a href="'.$site['url'].'" target="blank">'.$site['name'].'</a></td></tr>';
   }
   $html .= '</table>';
   print $html;
}

?>