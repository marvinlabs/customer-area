;(function($, window, undefined)
{
    "use strict";

    $(document).ready(function()
    {
        // Panel Close

        $('body').on('click', '.cuar-panel a[data-toggle="remove"]', function(ev)
        {
            ev.preventDefault();

            var $panel = $(this).closest('.cuar-panel'),
                $panel_parent = $panel.parent();

            $panel.remove();

            if($panel_parent.children().length == 0)
            {
                $panel_parent.remove();
            }
        });



        // Panel Reload
        $('body').on('click', '.cuar-panel a[data-toggle="reload"]', function(ev)
        {
            ev.preventDefault();

            var $panel = $(this).closest('.cuar-panel');

            // This is just a simulation, nothing is going to be reloaded
            $panel.append('<div class="cuar-panel-disabled"><div class="loader-1"></div></div>');

            var $pd = $panel.find('.cuar-panel-disabled');

            setTimeout(function()
            {
                $pd.fadeOut('fast', function()
                {
                    $pd.remove();
                });

            }, 500 + 300 * (Math.random() * 5));
        });



        // Panel Expand/Collapse Toggle
        $('.cuar-panel').on('click', 'a[data-toggle="panel"]', function(ev)
        {
            ev.preventDefault();

            var $panel = $(this).closest('.cuar-panel');

            $panel.toggleClass('cuar-collapsed');
        });

        // Bind Select2 on .cuar-select2 elements
        $(".cuar-select2").select2();

    });

})(jQuery, window);