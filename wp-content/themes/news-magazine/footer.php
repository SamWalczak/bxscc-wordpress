<?php

global $wdwt_front;
$footer_text_enable = $wdwt_front->get_param('footer_text_enable');
$footer_text = $wdwt_front->get_param('footer_text');
?>
<div id="footer">
    <div class="container">
		<div id="footer_eft">
          <?php if ( is_active_sidebar( 'sidebar-3' ) ) { ?>
				<div class="footer-sidbar">
                    <div id="sidebar-footer" >
                      <?php  dynamic_sidebar( 'sidebar-3' ); 	?>
                    </div>	
                </div>     	
          <?php } ?>
		</div>
		 
		<?php if ( is_active_sidebar( 'sidebar-4' ) ) { ?>
		<div class="footer-sidbar" id="sidebar4">
			<div id="sidebar-right-footer" >
			  <?php  dynamic_sidebar( 'sidebar-4' ); 	?>
			  <div class="clear"></div>
			</div>	
		</div>     	
        <?php } ?>
    </div>
</div>
<div id="footer-bottom">
	  <span id="copyright"><?php if($footer_text_enable) echo stripslashes($footer_text);  ?></span>
</div>
<?php  wp_footer();  ?>
</body>
</html>