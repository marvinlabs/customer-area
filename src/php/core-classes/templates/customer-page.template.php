<?php /** Template version: 1.0.0 */ ?>

<?php
$page_classes = array('cuar-page-' . $this->page_description['slug']);
if ($this->has_page_sidebar()) $page_classes[] = "cuar-page-with-sidebar";
else $page_classes[] = "cuar-page-without-sidebar";

$content_class = $this->page_description['slug'] == 'customer-dashboard' ? ' cuar-customer-dashboard-home' : ' cuar-customer-dashboard-page';
$content_class = $this->has_page_sidebar() ? $content_class . ' table-layout' : $content_class;
?>

<div class="cuar-page clearfix <?php echo implode(' ', $page_classes); ?>">
    <div class="cuar-page-header"><?php
        $this->print_page_header($args, $shortcode_content);
        ?></div>

    <div class="cuar-page-content <?php echo $content_class; ?>">
        <?php if ($this->has_page_sidebar()) { ?>
            <div class="cuar-page-content-main tray tray-center va-t clearfix"><?php
                $this->print_page_content($args, $shortcode_content);
                ?></div>
            <aside class="cuar-page-sidebar tray tray-right tray290 va-t clearfix">
                <?php
                $this->print_page_sidebar($args, $shortcode_content);
                ?></aside>
        <?php } else { ?>
            <div class="cuar-page-content-main clearfix"><?php
                $this->print_page_content($args, $shortcode_content);
                ?></div>
        <?php } ?>
    </div>

    <div class="cuar-page-footer clearfix"><?php
        $this->print_page_footer($args, $shortcode_content);
        ?></div>
</div>