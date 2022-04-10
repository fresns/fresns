/*!
 * Fresns (https://fresns.org)
 * Copyright 2021-Present Jarvis Tang
 * Licensed under the Apache-2.0 license
 */

/* Tooltips */
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// FsLang trans
function trans(key, replace = {}) {
    let translation = key.split('.').reduce((t, i) => {
        if (!t.hasOwnProperty(i)) {
            return key;
        }
        return t[i];
    }, window.translations || []);

    for (var placeholder in replace) {
        translation = translation.replace(`:${placeholder}`, replace[placeholder]);
    }

    return translation;
}

// set timeout toast hide
const setTimeoutToastHide = () => {
    $('.toast.show').each((k, v) => {
        setTimeout(function () {
            $(v).hide();
        }, 1500);
    });
};
setTimeoutToastHide();

// tips
window.tips = function (message, code = 200) {
    let html = `<div aria-live="polite" aria-atomic="true" class="position-fixed top-50 start-50 translate-middle" style="z-index:99">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                    <img src="/static/images/icon.png" width="20px" height="20px" class="rounded me-2" alt="Fresns">
                    <strong class="me-auto">Fresns</strong>
                    <small>${code}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            <div class="toast-body">${message}</div>
        </div>
    </div>`;
    $('div.fresns-tips').prepend(html);
    setTimeoutToastHide();
};

// set
$(document).ready(function () {
    // select2
    $('.select2').select2({
        theme: 'bootstrap-5',
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });

    $('.select2')
        .closest('form')
        .on('reset', function (ev) {
            var targetJQForm = $(ev.target);
            setTimeout(
                function () {
                    this.find('.select2').trigger('change');
                }.bind(targetJQForm),
                0
            );
        });

    // preview image
    $('.preview-image').click(function () {
        let url = $(this).siblings('.imageUrl').val();
        $('#imageZoom').find('img').attr('src', url);
        $('#imageZoom').modal('show');
    });

    // selectImageType
    $('.selectImageType li').click(function () {
        let inputname = $(this).data('name');

        $(this).parent().siblings('.showSelectTypeName').text($(this).text());
        $(this).parent().siblings('input').css('display', 'none');
        $(this)
            .parent()
            .siblings('.' + inputname)
            .removeAttr('style');
    });
});
