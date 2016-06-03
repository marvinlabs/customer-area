<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Initial template
 *
 */ ?>

<script type="text/javascript">
    <!--
    (function ($) {
        "use strict";
        $(document).ready(function ($) {

            // Init Select2
            if ($.isFunction($.fn.fancytree)) {
                $('#cuar-terms-tree-<?php echo $this->id_base; ?>').fancytree({
                    extensions: ["glyph"],
                    activeVisible: true, // Make sure, active nodes are visible (expanded).
                    aria: false, // Enable WAI-ARIA support.
                    autoActivate: true, // Automatically activate a node when it is focused (using keys).
                    autoCollapse: true, // Automatically collapse all siblings, when a node is expanded.
                    autoScroll: true, // Automatically scroll nodes into visible area.
                    clickFolderMode: 3, // 1:activate, 2:expand, 3:activate and expand, 4:activate (dblclick expands)
                    checkbox: false, // Show checkboxes.
                    debugLevel: 2, // 0:quiet, 1:normal, 2:debug
                    disabled: false, // Disable control
                    focusOnSelect: false, // Set focus when node is checked by a mouse click
                    escapeTitles: false, // Escape `node.title` content for display
                    generateIds: false, // Generate id attributes like <span id='fancytree-id-KEY'>
                    glyph: {
                        map: {
                            doc: "fa fa-file-o",
                            docOpen: "fa fa-file-o",
                            checkbox: "fa fa-square-o",
                            checkboxSelected: "fa fa-check-square-o",
                            checkboxUnknown: "fa fa-square",
                            dragHelper: "fa arrow-right",
                            dropMarker: "fa long-arrow-right",
                            error: "fa fa-warning",
                            expanderClosed: "fa fa-caret-right",
                            expanderLazy: "fa fa-angle-right",
                            expanderOpen: "fa fa-caret-down",
                            folder: "fa fa-folder-o",
                            folderOpen: "fa fa-folder-open-o",
                            loading: "fa fa-spinner fa-pulse"
                        }
                    },
                    idPrefix: "cuar-fancynode_", // Used to generate node id´s like <span id='fancytree-id-<key>'>.
                    icon: function(event, data){
                        // if( data.node.isFolder() ) {
                        //   return "fa fa-book";
                        // }
                    }, // Display node icons.
                    keyboard: false, // Support keyboard navigation.
                    keyPathSeparator: "/", // Used by node.getKeyPath() and tree.loadKeyPath().
                    minExpandLevel: 1, // 1: root node is not collapsible
                    quicksearch: false, // Navigate to next node by typing the first letters.
                    selectMode: 2, // 1:single, 2:multi, 3:multi-hier
                    tabindex: "", // Whole tree behaves as one single control
                    titlesTabbable: false, // Node titles can receive keyboard focus,
                    toggleEffect: false,
                    activate: function(event, data) {
                        var node = data.node;
                        // Use <a> href and target attributes to load the content:
                        if( node.data.href && !node.isFolder() ){
                            // Open target
                            window.open(node.data.href, node.data.target);
                        }
                    }
                });
            }

        });
    })(jQuery);
    //-->
</script>
