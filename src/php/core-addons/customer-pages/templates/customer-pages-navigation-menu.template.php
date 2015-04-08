<?php /** Template version: 1.0.0 */ ?>

<nav class="cuar-navbar cuar-navbar-default" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="cuar-container">
        <div class="cuar-navbar-header">
            <button type="button" class="cuar-navbar-toggle" data-toggle="collapse" data-target=".cuar-nav-container">
                <span class="cuar-sr-only"><?php __('Toggle navigation', 'cuar'); ?></span>
                <span class="cuar-icon-bar"></span>
                <span class="cuar-icon-bar"></span>
                <span class="cuar-icon-bar"></span>
            </button>
        </div>        
		<?php wp_nav_menu( $nav_menu_args ); ?>		
	</div>
</nav>
	
<?php echo $this->get_subpages_menu(); ?>