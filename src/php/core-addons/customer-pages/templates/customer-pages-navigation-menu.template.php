<?php /** Template version: 1.0.0 */ ?>

<nav class="navbar navbar-default" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".cuar-nav-container">
                <span class="sr-only"><?php __('Toggle navigation', 'cuar'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>        
		<?php wp_nav_menu( $nav_menu_args ); ?>		
	</div>
</nav>
	
<?php echo $this->get_subpages_menu(); ?>