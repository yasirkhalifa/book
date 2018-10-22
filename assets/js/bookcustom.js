jQuery(document).ready(function() {
            jQuery(function() {
                jQuery("#slider-range").slider({
                    range: true,
                    orientation: "horizontal",
                    min: 0,
                    max: 10000,
                    values: [0, 10000],
                    step: 1,
                    slide: function(event, ui) {
                        if (ui.values[0] == ui.values[1]) {
                            return false;
                        }
                        jQuery("#min_price").val(ui.values[0]);
                        jQuery("#max_price").val(ui.values[1]);
                    }
                });
                jQuery("#min_price").val(jQuery("#slider-range").slider());
                jQuery("#max_price").val(jQuery("#slider-range").slider());
            });
 
 jQuery("p").click(function(){
        jQuery(this).hide();
    });
            jQuery(".bookser").click(function() {
                var bTitle = jQuery("#btitle").val();
                var bAuthor = jQuery("#bauthor").val();
                var bookCate = jQuery('select[name="bookcate"] option:selected').val();
                var bookPubli = jQuery('select[name="bookpubli"] option:selected').val();
                 var min_price = jQuery("#min_price").val();
                 var max_price = jQuery("#max_price").val();
               // var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                 var ajaxurl = "../wp-admin/admin-ajax.php";
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'forBook_Topfilter',
                        bTitle: bTitle,
                        bAuthor: bAuthor,
                        bookCate: bookCate,
                        bookPubli: bookPubli,
                        min_price: min_price,
                        max_price: max_price
                    },
                    success: function(response) {
                        jQuery('.findbook').html(response);
                    }
                });
            });
        });