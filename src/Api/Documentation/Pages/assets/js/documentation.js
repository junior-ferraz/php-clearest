$(function () {
    $('.route .header .tab').click(function (e) {
        var currentTab = $(this);
        var method = currentTab.data('method');
        var route = currentTab.parent().parent().parent();
        var allTabs = $('.tab', route);
        var allMethods = $('.methods .method', route);
        var selectedMethod = $('.methods .'+method, route);
        allTabs.removeClass('selected');
        currentTab.addClass('selected');
        allMethods.hide();
        selectedMethod.toggle(200);
        e.stopPropagation();
    });

    $('.route .header').click(function (e) {
        var tabs = $('.tab', $(this));
        var methods = $('.methods .method', $(this).parent());
        tabs.removeClass('selected');
        methods.hide();
    })

    $('.enum').mouseover(function () {
        var element = $(this);
        var enumClass = element.data('class');
        element.balloon({
            html: true,
            position: 'right',
            contents: 'Loading...',
            url: '?render=enum&class='+enumClass,
            css: {
                maxWidth: '350px'
            }
        });
    });

    $('.property').balloon({
        position: 'right',
        css: {
            padding: '8px',
            maxWidth: '350px'
        }
    });

    $('.file').balloon({
        contents: 'File to upload. This field require the request to have a content-type header set as multipart/form-data',
        position: 'right',
        css: {
            padding: '8px',
            maxWidth: '200px'
        }
    });

    $('.date').balloon({
        position: 'right',
        css: {
            padding: '8px',
            maxWidth: '200px'
        }
    });
});