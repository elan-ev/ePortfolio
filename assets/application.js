(function ($) {
    STUDIP.domReady(() => {

        if($('meta[name=is_eportfolio]').attr('content') && $('a.cw-navigation-settings')) {
            $('a.cw-navigation-settings').remove()
            console.log($('meta[name=is_eportfolio]').attr('content'));
        }
    })
}(jQuery));