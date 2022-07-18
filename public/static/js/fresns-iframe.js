/*!
 * Fresns (https://fresns.org)
 * Copyright 2021-Present Jarvis Tang
 * Licensed under the Apache-2.0 license
 */

function sign() {
    let sign;
    $.ajaxSettings.async = false;
    $.get('/api/engine/url-sign', false, function (res) {
        sign = res.sign;
    });
    $.ajaxSettings.async = true;

    return sign;
}

(function ($) {
    $('#fresnsModal.fresnsExtensions').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            title = button.data('title'),
            reg = /\{[^\}]+\}/g,
            url = button.data('url'),
            replaceJson = button.data(),
            searchArr = url.match(reg);

        if (searchArr) {
            searchArr.forEach(function (v) {
                let attr = v.substring(1, v.length - 1);
                if (replaceJson[attr]) {
                    url = url.replace(v, replaceJson[attr]);
                } else {
                    if (v === '{sign}') {
                        url = url.replace('{sign}', sign());
                    } else {
                        url = url.replace(v, '');
                    }
                }
            });
        }

        $(this).find('.modal-title').empty().html(title);
        let inputHtml = `<iframe src="` + url + `" class="iframe-modal"></iframe>`;
        $(this).find('.modal-body').empty().html(inputHtml);

        // iFrame Resizer
        // http://davidjbradshaw.github.io/iframe-resizer/
        let isOldIE = navigator.userAgent.indexOf('MSIE') !== -1;
        $('#fresnsModal.fresnsExtensions iframe').on('load', function () {
            $(this).iFrameResize({
                autoResize: true,
                minHeight: 500,
                heightCalculationMethod: isOldIE ? 'max' : 'lowestElement',
                scrolling: true,
            });
        });
    });
})(jQuery);
