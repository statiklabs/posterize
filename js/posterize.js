var Posterize = {
   get_sites: function(username, password){
      $('#sites').hide();
      $.ajax({
         type: "GET",
         url: plugin_url+'/getsites.php',
         data: $('form#posterize_settings_form').serialize(),
         success: function(data){
           $('#sites').html(data);
         },
         complete: function(){
            $('#sites').slideDown('slow');
         }
       });
       //$('#sites').slideDown('slow');
       return false;
   }
}