jQuery(document).ready(function(){
  jQuery('a.get-sites-link').click(function(){
    jQuery.ajax({
      url: jQuery(this).attr('href'),
      type: 'POST', 
      data: jQuery('form#posterize_settings_form').serialize(),
      success: function(html){
        jQuery('.site-info').html(html);
      }
    });
    return false;
  })
})