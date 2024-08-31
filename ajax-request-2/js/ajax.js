jQuery(function($) {
     
     $('form#profile-form').on('submit', function(e) {
          e.preventDefault();

         $.post(
          simpleAuthAjax.ajax_url, 
          $(this). serialize() + '&_wpnonce=' + simpleAuthAjax.nonce,
          function(response) {
          if (response.success) {
               $('#profile-update-message').html(response.data.message);
          } else {
               $('#profile-update-message').html('Error updating profile.');
          }
          
         });
          
     });

     $('form#simple-auth-login-form').on('submit', function(e) {
          e.preventDefault();
          var formData = $(this).serialize() + '&_wpnonce=' + simpleAuthAjax.nonce;

          wp.ajax.post('simple-auth-login-form',formData, 
               $(this). serialize()
          ).done(function(response) {
               $('#login-message').html(response.message);

               setTimeout(function() {
                    window.location.reload();
               }, 2000);
               
          }).fail(function(err) {
             console.log('failed', err);
             $('#login-message').html(err.message);
             
          });


     }); 
     
});
