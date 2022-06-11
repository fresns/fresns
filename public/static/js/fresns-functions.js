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

/* Fresns Token */
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
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
        let url = $(this).data('url');
        if (url) {
            $('#imageZoom').find('img').attr('src', url);
            $('#imageZoom').modal('show');
        }
    });

    // selectInputType
    $('.selectInputType li').click(function () {
        let showClass = $(this).data('name');
        let hideClass = 'inputUrl';
        if (showClass == 'inputUrl') {
            hideClass = 'inputFile';
        }

        $(this).parent().siblings('.showSelectTypeName').text($(this).text());
        $(this).parent().siblings('.' + hideClass).hide();
        $(this).parent().siblings('.' + showClass).show();
    });

    $('.delete-file').click(function() {
        $(this).siblings('.file-value').val('');
        window.tips(trans('tips.deleteSuccess')); //FsLang
    });

    $('#langModal').on('show.bs.modal', function(e) {
        let target = $(e.relatedTarget);
        let languages = target.data('languages');
        let key = target.data('key');

        $(this).find('input[name=key]').val(key)

        if (languages) {
            let $this = $(this);
            languages.map(function (language, index) {
                $this.find("input[name='languages[" + language.lang_tag + "]'").val(language.lang_content);
            });
        }
    });

    $('#langDescModal').on('show.bs.modal', function(e) {
        let target = $(e.relatedTarget);
        let languages = target.data('languages');
        let key = target.data('key');

        $(this).find('input[name=key]').val(key)

        if (languages) {
            let $this = $(this);
            languages.map(function (language, index) {
                $this.find("textarea[name='languages[" + language.lang_tag + "]'").val(language.lang_content);
            });
        }
    });

    $('#addPlugin').click(function () {
        let templateId = $(this).data('template');
        let index = $(this).data('count');
        let key = $(this).data('key');

        template = $($(templateId).html()).clone();
        template.find('input.plugin-code').attr('name', key+'['+(index+ 1)+'][code]')
        template.find('select.plugin-unikey').attr('name', key+'['+(index+ 1)+'][plugin]')

        $(this).data('count', index+1)

        $(this).parent().parent().find('.plugin-box').append(template);
    });

    $(document).on('click', '.delete-plugin', function() {
        $(this).parent().remove()
    });
});
