<?php
/** Template version: 3.2.0
 *
 * -= 3.2.0 =-
 * - Replace clearfix CSS classes with cuar-clearfix
 *
 * -= 3.1.0 =-
 * - Improve sidebar javascript UI - added some selectors
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php
$page_classes = array('cuar-page-' . $this->page_description['slug']);
if ($this->has_page_sidebar()) $page_classes[] = "cuar-page-with-sidebar";
else $page_classes[] = "cuar-page-without-sidebar";

$content_class = $this->page_description['slug'];
$content_class = $this->has_page_sidebar() ? $content_class . ' table-layout' : $content_class;

$sidebar_attributes = apply_filters('cuar/core/page/sidebar-attributes', array(
    'data-tray-height-base' => 'window',
    'data-tray-height-substract' => '#wpadminbar,#header,#cuar-js-content-container>.cuar-toolbar,#cuar-js-content-container>.cuar-menu-container,#cuar-js-content-container>.cuar-page-header,#cuar-js-content-container>.cuar-page-footer',
    'data-tray-height-minimum' => 400,
    'data-tray-mobile' => '#cuar-js-mobile-sidebar'
));
$sidebar_attributes_inline = '';
foreach ($sidebar_attributes as $att => $v) {
    $sidebar_attributes_inline .= ' ' . $att . '="' . $v . '"';
}
?>

<div class="cuar-page cuar-clearfix <?php echo implode(' ', $page_classes); ?>">
    <div class="cuar-page-header"><?php
        $this->print_page_header($args, $shortcode_content);
        ?></div>

    <div class="cuar-page-content <?php echo $content_class; ?>">
        <?php if ($this->has_page_sidebar()) { ?>
            <div id="cuar-js-page-content" class="cuar-page-content-main tray tray-center tray-center-on-left va-t cuar-clearfix">
                <div id="cuar-js-page-content-wrapper">
                    <?php
                    $this->print_page_content($args, $shortcode_content);
                    ?>
                </div>
            </div>
            <aside id="cuar-js-tray" class="cuar-page-sidebar tray tray-right va-t cuar-clearfix"<?php echo $sidebar_attributes_inline; ?>>
                <div id="cuar-js-tray-scroller" class="tray-scroller">
                    <div id="cuar-js-tray-scroller-wrapper">
                        <?php $this->print_page_sidebar($args, $shortcode_content); ?>
                    </div>
                </div>
            </aside>
        <?php } else { ?>
            <div class="cuar-page-content-main cuar-clearfix"><?php
                $this->print_page_content($args, $shortcode_content);
                ?></div>
        <?php } ?>
    </div>

    <div id="cuar-js-mobile-sidebar"></div>

    <div class="cuar-page-footer cuar-clearfix"><?php
        $this->print_page_footer($args, $shortcode_content);
        ?></div>
</div>