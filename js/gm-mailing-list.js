(function (jQuery, Drupal, drupalSettings) {
  Drupal.behaviors.donation_paypal_ponctuel = {
    attach: function (context) {
      jQuery('.gm-mailing-list-form .form-item.form-item-email input, .gm-mailing-list-form .form-item.form-item-code-postal input', context).each(function () {
        var $input = jQuery(this);

          $input.focus(function(){
            jQuery(this).parents('.form-item').addClass('focused');
          });
          
          $input.blur(function(){
            var inputValue = jQuery(this).val();
            if ( inputValue == "" ) {
              jQuery(this).removeClass('filled');
              jQuery(this).parents('.form-item').removeClass('focused');  
            } else {
              jQuery(this).addClass('filled');
            }
          })
      });
    }
  }
})(jQuery, Drupal, drupalSettings);