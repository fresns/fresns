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

// copy url
function copyToClipboard(element) {
    var $temp = $('<input>');
    $('body').append($temp);
    $temp.val($(element).text()).select();
    document.execCommand('copy');
    $temp.remove();
    window.tips(trans('tips.copySuccess')); //FsLang
}

// set
$(document).ready(function () {
    // upgrade
    $('#upgradeButton').click(function () {
        if ($(this).data('upgrading')) {
            $('#upgrade').modal('show');
        } else {
            $('#upgradeConfirm').modal('show');
        }
    });

    $('#upgradeForm').submit(function () {
        $('#upgradeConfirm').modal('hide');
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: $(this).attr('action'),
            success: function (response) {
                $('#upgradeButton').removeClass('btn-primary').addClass('btn-info').text(trans('panel.upgrade_being')); //FsLang

                $('#upgradeButton').data('upgrading', true);

                $('#upgrade').modal('show');
            },
            error: function (response) {
                window.tips(response.responseJSON.message);
            },
        });

        return false;
    });

    var upgradeTimer = null;

    function checkUpgradeStep(action) {
        if (!action) {
            return;
        }
        $.ajax({
            method: 'get',
            url: action,
            success: function (response) {
                let upgradeStep = response.upgrade_step;

                if (!upgradeStep || upgradeStep == 6) {
                    location.reload();
                    return;
                }

                let step = $('#upgrade').find('#upgrade' + upgradeStep);
                step.find('i').remove();
                step.prepend('<i class="upgrade-step spinner-border spinner-border-sm me-2" role="status"></i>');

                step.prevAll().map((index, completeStep) => {
                    $(completeStep).find('i').remove();
                    $(completeStep).prepend('<i class="bi bi-check-lg text-success me-2"></i>');
                });
            },
        });
    }

    $('#upgrade').on('show.bs.modal', function (e) {
        let button = $('#upgradeButton');
        let action = button.data('action');

        if (!upgradeTimer) {
            checkUpgradeStep(action);
            upgradeTimer = setInterval(checkUpgradeStep, 5000, action);
        }
    });

    $('#fresnsUpgrade').click(function () {
        $.ajax({
            method: 'post',
            url: $(this).data('action'),
            success: function (response) {
                window.tips(response.message);
            },
        });
    });

    // select2
    $('.select2').select2({
        theme: 'bootstrap-5',
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });

    // check if a multilingual field has a value
    $('.check-names').submit(function () {
        let names = [];
        $(this)
            .find('.name-input')
            .map((index, item) => {
                names.push($(item).val());
            });
        names = names.filter((val) => val != '');

        if (names.length == 0) {
            $('.name-button').addClass('is-invalid');
            return false;
        } else {
            $('.name-button').removeClass('is-invalid');
        }
    });

    // preview image
    $('.preview-image').click(function () {
        let url = $(this).siblings('.imageUrl').val();
        $('#imageZoom').find('img').attr('src', url);
        $('#imageZoom').modal('show');
    });

    // admin config
    $('#adminConfig .update-backend-url').change(function () {
        let domain = $('#adminConfig').find('input[name=domain]').val();
        let path = $('#adminConfig').find('input[name=path]').val();
        $('#adminConfig')
            .find('#backendUrl')
            .text(domain + '/fresns/' + path);
    });

    // update session key
    $('#updateKey').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            id = button.data('id'),
            name = button.data('name'),
            type = button.data('type'),
            isEnable = button.data('is_enable'),
            pluginUnikey = button.data('plugin_unikey'),
            action = button.data('action'),
            platformId = button.data('platform_id');

        $(this).find('form').attr('action', action);
        $(this).find('#key_platform').val(platformId);
        $(this).find('#key_name').val(name);
        $(this)
            .find('input:radio[name=type][value="' + type + '"]')
            .prop('checked', true)
            .click();

        if (type == 3) {
            $(this).find('#key_plugin').prop('required', true);
        } else {
            $(this).find('#key_plugin').prop('required', false);
        }
        $(this)
            .find('input:radio[name=is_enable][value="' + isEnable + '"]')
            .prop('checked', true);
        $(this).find('#key_plugin').val(pluginUnikey);
    });

    $('#updateKey,#createKey')
        .find('input[name=type]')
        .change(function () {
            if ($(this).val() == 3) {
                $(this).closest('form').find('select[name=plugin_unikey]').prop('required', true);
            } else {
                $(this).closest('form').find('select[name=plugin_unikey]').prop('required', false);
            }
        });

    // reset session app secret
    $('#resetSecret').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            appId = button.data('app_id'),
            name = button.data('name'),
            action = button.data('action');

        $(this).find('form').attr('action', action);
        $(this).find('.app-id').text(appId);
        $(this).find('.modal-title').text(name);
    });

    // delete session key
    $('#deleteKey').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            appId = button.data('app_id'),
            name = button.data('name'),
            action = button.data('action');

        $(this).find('form').attr('action', action);
        $(this).find('.app-id').text(appId);
        $(this).find('.modal-title').text(name);
    });

    $('.select-continent').change(function () {
        let areas = $(this).data('children');
        let continent = $(this).val();
        areas = areas.filter((area) => {
            if (area.continentId == continent) {
                return true;
            }
            return false;
        });

        let childrenSelect = $(this).next();
        childrenSelect.find('option').remove();

        areas.map((area) => {
            childrenSelect.append('<option value="' + area.code + '">' + area.name + ' > ' + area.code + '</option>');
        });
    });

    // set default language
    $('input[name="default_language"]').change(function () {
        $.ajax({
            method: 'post',
            url: $(this).data('action'),
            data: {
                default_language: $(this).val(),
                _method: 'put',
            },
            success: function (response) {
                window.tips(response.message);
                window.location.reload();
            },
        });
    });

    $(document).on('click', 'input.rank-num', function () {
        return false;
    });

    $(document).on('change', 'input.rank-num', function () {
        $.ajax({
            method: 'post',
            url: $(this).data('action'),
            data: {
                rank_num: $(this).val(),
                _method: 'put',
            },
            success: function (response) {
                window.tips(response.message);
            },
        });
        return false;
    });

    // update language menu
    $('#updateLanguageMenu').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            language = button.data('language'),
            action = button.data('action');

        let status = language.areaStatus ? 1 : 0;
        let isEnable = language.isEnable ? 1 : 0;

        $(this).find('form').attr('action', action);
        $(this).find('input[name=rank_num]').val(language.rankNum);
        $(this).find('input[name=old_lang_tag]').val(language.langTag);
        $(this).find('select[name=lang_code]').val(language.langCode);
        $(this)
            .find('input:radio[name=area_status][value="' + status + '"]')
            .prop('checked', true)
            .click();
        $(this).find('select[name=continent_id]').val(language.continentId);

        let continentSelect = $(this).find('select[name=continent_id]');
        continent = language.continentId;
        let areas = continentSelect.data('children');
        areas = areas.filter((area) => {
            if (area.continentId == continent) {
                return true;
            }
            return false;
        });

        let childrenSelect = continentSelect.next();
        childrenSelect.find('option').remove();

        areas.map((area) => {
            childrenSelect.append('<option value="' + area.code + '">' + area.name + ' > ' + area.code + '</option>');
        });

        $(this).find('select[name=area_code]').val(language.areaCode);
        $(this).find('select[name=length_units]').val(language.lengthUnits);
        $(this).find('select[name=date_format]').val(language.dateFormat);
        $(this).find('input[name=time_format_minute]').val(language.timeFormatMinute);
        $(this).find('input[name=time_format_hour]').val(language.timeFormatHour);
        $(this).find('input[name=time_format_day]').val(language.timeFormatDay);
        $(this).find('input[name=time_format_month]').val(language.timeFormatMonth);
        $(this)
            .find('input:radio[name=is_enable][value="' + isEnable + '"]')
            .prop('checked', true)
            .click();
    });

    // update language
    $('#updateLanguage').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            langTag = button.data('lang_tag'),
            langTagDesc = button.data('lang_tag_desc'),
            content = button.data('content'),
            key = button.data('key'),
            title = button.data('title'),
            action = button.data('action');

        $(this).find('form').attr('action', action);
        $(this).find('.lang-label').text(langTagDesc);
        $(this).find('.modal-title').text(title);
        $(this).find('input[name=lang_tag]').val(langTag);
        $(this).find('input[name=lang_key]').val(key);
        $(this).find('textarea[name=content]').val(content);
    });

    $('#updateLanguageForm').submit(function () {
        $('#updateLanguage').modal('hide');
        let content = $(this).find('textarea[name=content]').val();
        let langTag = $(this).find('input[name=lang_tag]').val();
        let langKey = $(this).find('input[name=lang_key]').val();

        $.ajax({
            method: 'post',
            url: $(this).attr('action'),
            data: {
                lang_tag: langTag,
                content: content,
                _method: 'put',
            },
            success: function (response) {
                $('#policyTabContent')
                    .find("[data-lang_tag='" + langTag + "'][data-key='" + langKey + "']")
                    .data('content', content);
                window.tips(response.message);
            },
        });
        return false;
    });

    // user connect
    $('#addConnect').click(function () {
        let template = $('#connectTemplate');
        $('.connect-box').append(template.html());
    });

    function hasDuplicates(array) {
        var valuesSoFar = Object.create(null);
        for (var i = 0; i < array.length; ++i) {
            var value = array[i];
            if (value in valuesSoFar) {
                return true;
            }
            valuesSoFar[value] = true;
        }
        return false;
    }
    //user config
    $('#userConfigForm').submit(function () {
        let connectItems = $(this).find('select[name="connects[]"]');
        let connects = [];
        connectItems.map((index, item) => {
            connects.push($(item).val());
        });

        if (hasDuplicates(connects)) {
            window.tips(trans('tips.account_connect_services_error')); //FsLang

            return false;
        }
    });

    // use config delete  connect
    $(document).on('click', '.delete-connect', function () {
        $(this).parent().remove();
    });

    // Generic processing (name multilingual) start
    $('.name-lang-parent').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }

        let button = $(e.relatedTarget);
        let action = button.data('action');
        let params = button.data('params');
        let names = button.data('names');
        let defaultName = button.data('default-name');

        $(this).parent('form').attr('action', action);
        $(this).parent('form').find('input[name=update_name]').val(0);
        $(this)
            .parent('form')
            .find('input[name=_method]')
            .val(params ? 'put' : 'post');
        $(this).parent('form').trigger('reset');

        if (!params) {
            $(this).find('.name-button').text(trans('panel.table_name')); //FsLang
            return;
        }

        $(this)
            .find('.name-button')
            .text(defaultName ? defaultName : params.name ? params.name : trans('panel.table_name')); //FsLang

        if (names) {
            names.map((name, index) => {
                $(this)
                    .parent('form')
                    .find("input[name='names[" + name.lang_tag + "]'")
                    .val(name.lang_content);
            });
        }
    });

    $('.name-lang-parent').on('hide.bs.modal', function (e) {
        $(this).data('is_back', false);
    });

    $('.name-lang-modal').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }

        let button = $(e.relatedTarget);
        var parent = button.data('parent');
        if (!parent) {
            return;
        }

        var $this = $(this);
        $(this).on('hide.bs.modal', function (e) {
            if (parent) {
                let defaultName = $(this).find('.text-primary').closest('tr').find('.name-input').val();
                if (!defaultName) {
                    defaultName = $(this)
                        .find('.name-input')
                        .filter(function () {
                            return $(this).val() != '';
                        })
                        .first()
                        .val();
                }

                $(parent).find('.name-button').text(defaultName);

                $(parent).data('is_back', true);
                $this.parent('form').find('input[name=update_name]').val(1);
                $(parent).modal('show');
            }
        });
    });

    $('.update-name-modal').on('hide.bs.modal', function (e) {
        defaultName = $(this)
            .find('.name-input')
            .filter(function () {
                return $(this).val() != '';
            })
            .first()
            .val();

        $('.name-button').text(defaultName);
    });

    $('.description-lang-modal').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }

        let button = $(e.relatedTarget);
        var parent = button.data('parent');
        if (!parent) {
            return;
        }

        var $this = $(this);
        $(this).on('hide.bs.modal', function (e) {
            if (parent) {
                let defaultDesc = $(this).find('.text-primary').closest('tr').find('.desc-input').val();
                if (!defaultDesc) {
                    defaultDesc = $(this)
                        .find('.desc-input')
                        .filter(function () {
                            return $(this).val() != '';
                        })
                        .first()
                        .val();
                }

                $(parent).find('.desc-button').text(defaultDesc);
                $(parent).data('is_back', true);
                $this.parent('form').find('input[name=update_description]').val(1);
                $(parent).modal('show');
            }
        });
    });
    // Generic processing (name multilingual) end

    // map config
    $('#createMap').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }

        let button = $(e.relatedTarget);
        let params = button.data('params');

        $(this).parent('form').trigger('reset');

        if (!params) {
            return;
        }
        let configParams = button.data('config_params');

        if (params.icon_file_url) {
            $(this).find('input[name=icon_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        }
        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('select[name=plugin_unikey]').val(params.plugin_unikey);
        $(this).find('select[name=parameter]').val(params.parameter);
        $(this).find('input[name=app_id]').val(configParams.appId);
        $(this).find('input[name=app_key]').val(configParams.appKey);
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();
    });

    $('#configLangModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            languages = button.data('languages'),
            itemKey = button.data('item_key'),
            action = button.data('action');

        $(this).find('form').attr('action', action);
        $(this).find('form').trigger('reset');
        $(this).find('input[name=update_config]').val(itemKey);

        if (languages) {
            let $this = $(this);
            languages.map(function (language, index) {
                $this.find("input[name='languages[" + language.lang_tag + "]'").val(language.lang_content);
            });
        }
    });

    // sticker
    $('#stickerGroupCreateModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');

        if (!params) {
            return;
        }

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.image_file_url) {
            $(this).find('input[name=image_url]').val(params.image_file_url);
            $(this).find('input[name=image_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        } else {
            $(this).find('input[name=image_url]').val('');
        }

        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('input[name=code]').val(params.code);
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();
    });

    $('#offcanvasSticker').on('show.bs.offcanvas', function (e) {
        let button = $(e.relatedTarget);
        let stickers = button.data('stickers');
        let parent_id = button.data('parent_id');

        $('#stickerList').empty();
        $(this).parent('form').find('input[name=parent_id]').val(parent_id);
        $('#offcanvasStickerLabel button').data('parent_id', parent_id);

        if (!stickers) {
            return;
        }
        let template = $($('#stickerData').html());

        stickers.map((sticker) => {
            let stickerTemplate = template.clone();

            stickerTemplate
                .find('input.sticker-rank')
                .attr('name', 'rank_num[' + sticker.id + ']')
                .val(sticker.rank_num);

            stickerTemplate.find('.sticker-img').attr('src', sticker.image_file_url);
            stickerTemplate.find('.sticker-code').html(sticker.code);

            stickerTemplate.find('input.sticker-enable').attr('name', 'enable[' + sticker.id + ']');
            if (sticker.is_enable) {
                stickerTemplate.find('input.sticker-enable').attr('checked', 'checked');
            }
            $('#stickerList').append(stickerTemplate);
        });
    });

    $('#stickerModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let parent_id = button.data('parent_id');

        $(this).find('input[name=parent_id]').val(parent_id);
    });

    $(document).on('click', '.delete-sticker', function () {
        $(this).closest('tr').remove();
    });

    $('#blockWordModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');
        let action = button.data('action');

        $(this).find('form').attr('action', action);
        $(this)
            .find('form')
            .find('input[name=_method]')
            .val(params ? 'put' : 'post');

        if (!params) {
            $(this).find('form').trigger('reset');
            return;
        }

        $(this).find('input[name=word]').val(params.word);
        $(this).find('input[name=replace_word]').val(params.replace_word);
        $(this).find('select[name=content_mode]').val(params.content_mode);
        $(this).find('select[name=user_mode]').val(params.user_mode);
        $(this).find('select[name=dialog_mode]').val(params.dialog_mode);
    });

    // user_roles
    $('#emptyColor').change(function () {
        if ($(this).is(':checked')) {
            $('.choose-color').hide();
        } else {
            $('.choose-color').show();
        }
    });

    $('#createRoleModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');
        let defaultName = button.data('default-name');

        $('.choose-color').show();

        if (!params) {
            return;
        }

        $(this).find('select[name=type]').val(params.type);
        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('input[name=name]').val(params.name);
        if (params.is_display_name) {
            $(this).find('input[name=is_display_name]').attr('checked', 'checked');
        }
        if (params.is_display_icon) {
            $(this).find('input[name=is_display_icon]').attr('checked', 'checked');
        }

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        } else {
            $(this).find('input[name=icon_url]').val('');
        }

        if (params.nickname_color) {
            $(this).find('input[name=nickname_color]').val(params.nickname_color);

            $('.choose-color').show();
            $(this).find('input[name=no_color]').prop('checked', false);
        } else {
            $('.choose-color').hide();
            $(this).find('input[name=no_color]').prop('checked', true);
        }
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();
    });

    $('#deleteRoleModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let action = button.data('action');
        let params = button.data('params');

        $(this).find('form').attr('action', action);

        $(this).find('input[name=name]').val(params.name);
        $(this).find('#chooseRole').children('.role-option').prop('disabled', false);
        $(this)
            .find('#chooseRole')
            .find('option[value=' + params.id + ']')
            .prop('disabled', true);
    });

    $('#addCustomPerm').click(function () {
        let template = $('#customPerm').clone();
        $('#addCustomPermTr').before(template.contents());
    });

    $('.delete-custom-perm').click(function () {
        $(this).closest('tr').remove();
    });

    // config post select
    $('#post_limit_type').change(function () {
        var value = $('#post_limit_type  option:selected').val();
        if (value == 1) {
            $('#post_datetime_setting').collapse('show');
            $('#post_time_setting').collapse('hide');
        } else if (value == 2) {
            $('#post_datetime_setting').collapse('hide');
            $('#post_time_setting').collapse('show');
        }
    });

    $('#comment_limit_type').change(function () {
        var value = $('#comment_limit_type  option:selected').val();
        if (value == 1) {
            $('#comment_datetime_setting').collapse('show');
            $('#comment_time_setting').collapse('hide');
        } else if (value == 2) {
            $('#comment_datetime_setting').collapse('hide');
            $('#comment_time_setting').collapse('show');
        }
    });

    // plugin setting
    $('.plugin-manage').click(function () {
        $.ajax({
            method: 'post',
            url: $(this).data('action'),
            data: {
                _method: 'patch',
                is_enable: $(this).data('enable')
            },
            success: function (response) {
                window.tips(response.message);
                location.reload();
            },
        });
    });

    $('#uninstallConfirm').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
            window.pluginName = button.data('name');
            window.clearDataDesc = button.data('clear_data_desc');
            window.url = button.data('action');

        $(this).find('.modal-title').text( window.pluginName );
        $(this).find('.form-check-label').text( window.clearDataDesc );
    });

    $('#uninstallStepModal').on('show.bs.modal', function (e) {
        $(this).find('.modal-title').text(window.pluginName);

    });

    $('#uninstallStepModal').on('click', '.btn-secondary',function (e) {
        location.reload();
    });

    $('.uninstall-plugin').click(function () {
        var clearData = $("#uninstallConfirm").find('#uninstallData').prop("checked");
        if (clearData ){
            clearData = 1
        }
        else {
            clearData = 0
        }
        window.uninstallMessage = trans('tips.uninstallFailure'); //FsLang
        $.ajax({
            method: 'post',
            url: window.url + '&clearData=' + clearData,
            data: {
                _method: 'delete'
            },
            success: function (response) {
                window.uninstallMessage = response;
                $('#uninstallStepModal').find('#artisan_output').text(response);
            },
        });
    });

    // theme set
    $('#themeSetting').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let action = button.data('action');
        let params = button.data('params');
        let pcPlugin = button.data('pc_plugin');
        let mobilePlugin = button.data('mobile_plugin');

        $(this).find('form').attr('action', action);
        $(this)
            .find('#pcTheme')
            .attr('name', params.unikey + '_Pc');
        $(this)
            .find('#mobileTheme')
            .attr('name', params.unikey + '_Mobile');

        $(this).find('#pcTheme').val(pcPlugin);
        $(this).find('#mobileTheme').val(mobilePlugin);
    });

    // change default homepage
    $('.update-config').change(function () {
        $.ajax({
            method: 'post',
            url: $(this).data('action'),
            data: {
                _method: 'put',
                item_value: $(this).data('item_value'),
            },
            success: function (response) {
                window.tips(response.message);
            },
        });
    });

    // menu edit
    $('#menuEdit').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            isEnable = button.data('is_enable'),
            noConfig = button.data('no_config');
        action = button.data('action');
        config = button.data('config');

        if (noConfig) {
            $(this).find('.default-config').hide();
        } else {
            $(this).find('.default-config').show();
        }

        $(this).find('form').attr('action', action);
        $(this).find('textarea[name=config]').val(JSON.stringify(config));
        $(this)
            .find('input:radio[name=is_enable][value="' + isEnable + '"]')
            .prop('checked', true);
    });

    $('#menuLangModal').on('shown.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            languages = button.data('languages'),
            action = button.data('action');

        $(this).find('form').trigger('reset');
        $(this).find('form').attr('action', action);

        if (languages) {
            languages.map((language, index) => {
                $(this)
                    .find("input[name='languages[" + language.lang_tag + "]'")
                    .val(language.lang_content);
            });
        }
    });
    $('#menuLangTextareaModal').on('shown.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            languages = button.data('languages'),
            action = button.data('action');

        $(this).find('form').trigger('reset');
        $(this).find('form').attr('action', action);

        if (languages) {
            languages.map((language, index) => {
                $(this)
                    .find("textarea[name='languages[" + language.lang_tag + "]'")
                    .val(language.lang_content);
            });
        }
    });

    $(document).on('click', '.delete-lang-pack', function () {
        $(this).closest('tr').remove();
    });

    $('#addLangPack').click(function () {
        let template = $('#languagePackTemplate');
        $('.lang-pack-box').append(template.html());
    });

    // wallet services
    $('.wallet-modal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');

        if (!params) {
            return;
        }

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        } else {
            $(this).find('input[name=icon_url]').val('');
        }
        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('select[name=plugin_unikey]').val(params.plugin_unikey);
        $(this).find('input[name=parameter]').val(params.parameter);
        if (params.name) {
            $(this).find('.name-button').text(params.name);
        }
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();
    });

    // group edit
    $('.edit-group-category').click(function () {
        let action = $(this).data('action');
        let params = $(this).data('params');
        let form = $('#createCategoryModal').parent('form');

        form.find('input[name=_method]').val(params ? 'put' : 'post');
        form.attr('action', action);
        form.trigger('reset');
        form.find('.name-button').text(trans('panel.table_name')); //FsLang
        form.find('.desc-button').text(trans('panel.table_description')); //FsLang

        if (params) {
            $('#createCategoryModal').find('input[name=rank_num]').val(params.rank_num);
            $('#createCategoryModal')
                .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
                .prop('checked', true);
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
            $('#createCategoryModal').find('input[name=cover_url]').val(params.cover_file_url);
            $('#createCategoryModal').find('input[name=cover_url]').css('display', 'block');
            $('#createCategoryModal').find('input[name=banner_url]').val(params.banner_file_url);
            $('#createCategoryModal').find('input[name=banner_url]').css('display', 'block');

            let names = $(this).data('names');
            let defaultName = $(this).data('default-name');
            let defaultDesc = $(this).data('default-desc');
            let descriptions = $(this).data('descriptions');
            $('#createCategoryModal')
                .find('.name-button')
                .text(defaultName ? defaultName : params.name ? params.name : trans('panel.table_name')); //FsLang
            $('#createCategoryModal')
                .find('.desc-button')
                .text(
                    defaultDesc
                        ? defaultDesc
                        : params.description
                        ? params.description
                        : trans('panel.table_description')
                ); //FsLang

            if (names) {
                names.map((name) => {
                    $('#createCategoryModal')
                        .parent('form')
                        .find("input[name='names[" + name.lang_tag + "]'")
                        .val(name.lang_content);
                });
            }
            if (descriptions) {
                descriptions.map((description) => {
                    $('#createCategoryModal')
                        .parent('form')
                        .find("textarea[name='descriptions[" + description.lang_tag + "]'")
                        .val(description.lang_content);
                });
            }
        }
        $('#createCategoryModal').modal('show');

        return false;
    });

    $('.delete-group-category').click(function () {
        $.ajax({
            method: 'post',
            dataType: 'json',
            url: $(this).data('action'),
            data: {
                _method: 'delete',
            },
            success: function (response) {
                window.tips(response.message);
                location.reload();
            },
            error: function (response) {
                window.tips(response.responseJSON.message);
            },
        });
        return false;
    });

    $('#moveModal').on('shown.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let action = button.data('action');
        let params = button.data('params');

        $(this).data('group_id', params.id);
        $(this).find('form').attr('action', action);
        $(this).find('form').trigger('reset');
        $(this).find('input[name=current_group]').val(params.name);
    });

    $('#moveModal .choose-category').change(function () {
        $('.choose-group').find('option:not(:disabled)').remove();
        var currentGroupId = $('#moveModal').data('group_id');

        $.ajax({
            method: 'get',
            url: $(this).data('action'),
            data: {
                category_id: $(this).val(),
            },
            success: function (response) {
                response.map((item) => {
                    if (item.id != currentGroupId) {
                        $('.choose-group').append('<option value="' + item.id + '">' + item.name + '</option>');
                    }
                });
            },
        });
    });

    $('.group-user-select2').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '/fresns/users/search',
            dataType: 'json',
            data: function (params) {
                return {
                    keyword: params.term,
                    page: params.page || 1,
                };
            },
            processResults: function (data, params) {
                params.current_page = params.current_page || 1;
                let results = data.data.map((item) => {
                    return {
                        id: item.id,
                        text: item.nickname + '(@' + item.username + ')',
                    };
                });

                return {
                    results: results,
                    pagination: {
                        more: params.current_page * 30 < data.total,
                    },
                };
            },
        },
    });

    $('#groupModal').on('shown.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }
        let button = $(e.relatedTarget);
        let action = button.data('action');
        let params = button.data('params');

        let form = $(this).parents('form');
        let selectAdmin = form.find('select[name="permission[admin_users][]"]');

        form.attr('action', action);
        form.find('input[name=_method]').val(params ? 'put' : 'post');

        form.trigger('reset');
        form.find('.name-button').text(trans('panel.table_name')); //FsLang
        form.find('.desc-button').text(trans('panel.table_description')); //FsLang
        selectAdmin.find('option').remove();

        if (!params) {
            return;
        }

        form.find('select[name=parent_id]').val(params.parent_id);
        form.find('input[name=rank_num]').val(params.rank_num);
        form.find('select[name=plugin_unikey]').val(params.plugin_unikey);

        let names = button.data('names');
        let descriptions = button.data('descriptions');
        let defaultName = $(this).data('default-name');
        let defaultDesc = $(this).data('default-desc');
        let adminUsers = button.data('admin_users');

        $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
        $('.inputFile').css('display', 'none');
        form.find('input[name=cover_url]').val(params.cover_file_url);
        form.find('input[name=cover_url]').css('display', 'block');
        form.find('input[name=banner_url]').val(params.banner_file_url);
        form.find('input[name=banner_url]').css('display', 'block');
        form.find('input:radio[name=type_mode][value="' + params.type_mode + '"]')
            .prop('checked', true)
            .click();

        form.find('select[name=plugin_unikey]').val(params.plugin_unikey);

        if (adminUsers) {
            adminUsers.map((user) => {
                let text = user.nickname + '(@' + user.username + ')';
                let newOption = new Option(text, user.id, true, true);
                form.find('select[name="permission[admin_users][]"]').append(newOption);
            });
        }
        form.find('select[name="permission[admin_users][]"]').val(params.permission.admin_users).change();
        form.find('input:radio[name=type_find][value="' + params.type_find + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name=type_follow][value="' + params.type_follow + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name=is_recommend][value="' + params.is_recommend + '"]')
            .prop('checked', true)
            .click();

        let permission = params.permission;
        let publishPost = permission.publish_post ? permission.publish_post : 0;
        let publishPostReview = permission.publish_post_review ? 1 : 0;
        let publishComment = permission.publish_comment ? permission.publish_comment : 0;
        let publishCommentReview = permission.publish_comment_review ? 1 : 0;

        form.find('input:radio[name="permission[publish_post]"][value="' + publishPost + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name="permission[publish_post_review]"][value="' + publishPostReview + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name="permission[publish_comment]"][value="' + publishComment + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name="permission[publish_comment_review]"][value="' + publishCommentReview + '"]')
            .prop('checked', true)
            .click();

        form.find('select[name="permission[publish_post_roles][]"]').val(params.permission.publish_post_roles).change();
        form.find('select[name="permission[publish_comment_roles][]"]')
            .val(params.permission.publish_comment_roles)
            .change();
        form.find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();

        form.find('.name-button').text(
            defaultName ? defaultName : params.name ? params.name : trans('panel.table_name')
        ); //FsLang
        form.find('.desc-button').text(
            defaultDesc ? defaultDesc : params.description ? params.description : trans('panel.table_description')
        ); //FsLang
        if (names) {
            names.map((name) => {
                form.find("input[name='names[" + name.lang_tag + "]'").val(name.lang_content);
            });
        }
        if (descriptions) {
            descriptions.map((description) => {
                form.find("textarea[name='descriptions[" + description.lang_tag + "]'").val(description.lang_content);
            });
        }
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

    //expend-edit-modal
    $('.expend-edit-modal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');

        if (!params) {
            return;
        }
        $('#inlineCheckbox1').removeAttr('checked');
        $('#inlineCheckbox2').removeAttr('checked');
        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('select[name=plugin_unikey]').val(params.plugin_unikey);
        $(this).find('input[name=parameter]').val(params.parameter);
        $(this).find('input[name=editor_number]').val(params.editor_number);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        }

        if (params.roles) {
            $(this).find('select[name="roles[]"]').val(params.roles.split(',')).change();
        }
        scene = params.scene.split(',');
        for (var i = 0; i < scene.length; i++) {
            if (scene[i] == 1) {
                $('#inlineCheckbox1').attr('checked', 'checked');
            }
            if (scene[i] == 2) {
                $('#inlineCheckbox2').attr('checked', 'checked');
            }
        }

        if (params.name) {
            $(this).find('.name-button').text(params.name);
        }
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();
    });

    //expend-manage-modal
    $('.expend-manage-modal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');

        if (!params) {
            return;
        }
        $('#inlineCheckbox1').removeAttr('checked');
        $('#inlineCheckbox2').removeAttr('checked');
        $('#inlineCheckbox3').removeAttr('checked');

        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('select[name=plugin_unikey]').val(params.plugin_unikey);
        $(this).find('input[name=parameter]').val(params.parameter);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        }

        $(this).find('select[name="roles[]"]').val('').change();
        if (params.roles) {
            $(this).find('select[name="roles[]"]').val(params.roles.split(',')).change();
        }
        scene = params.scene.split(',');
        for (var i = 0; i < scene.length; i++) {
            if (scene[i] == 1) {
                $('#inlineCheckbox1').attr('checked', 'checked');
            }
            if (scene[i] == 2) {
                $('#inlineCheckbox2').attr('checked', 'checked');
            }
            if (scene[i] == 3) {
                $('#inlineCheckbox3').attr('checked', 'checked');
            }
        }

        if (params.name) {
            $(this).find('.name-button').text(params.name);
        }
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();

        $(this)
            .find('input:radio[name=is_group_admin][value="' + params.is_group_admin + '"]')
            .prop('checked', true)
            .click();

        if (params.is_group_admin == 0) {
            $('#usage_setting').addClass('show');
        } else {
            $('#usage_setting').removeClass('show');
        }
    });

    //expend-profile-modal
    $('.expend-profile-modal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');

        if (!params) {
            return;
        }
        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('select[name=plugin_unikey]').val(params.plugin_unikey);
        $(this).find('input[name=parameter]').val(params.parameter);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        }

        if (params.roles) {
            $(this).find('select[name="roles[]"]').val(params.roles.split(',')).change();
        }

        if (params.name) {
            $(this).find('.name-button').text(params.name);
        }
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();
    });

    //expend-feature-modal
    $('.expend-feature-modal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');

        if (!params) {
            return;
        }
        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('select[name=plugin_unikey]').val(params.plugin_unikey);
        $(this).find('input[name=parameter]').val(params.parameter);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        }

        if (params.roles) {
            $(this).find('select[name="roles[]"]').val(params.roles.split(',')).change();
        }

        if (params.name) {
            $(this).find('.name-button').text(params.name);
        }
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();
    });

    $('#parentGroupId').on('change', function () {
        let selectedOption = $(this).find('option:selected');

        let children = selectedOption.data('children');

        $('#childGroup').find('option:not(:disabled)').remove();

        if (children) {
            children.map((item) => {
                $('#childGroup').append('<option value="' + item.id + '">' + item.name + '</option>');
            });
        }

        $('#childGroup').removeAttr('style');
    });

    $('#search_group_id').on('change', function () {
        $('.groupallsearch option').each(function () {
            $(this).prop('selected', '');
        });
        $('.alloption').css('display', 'none');
        let search_group_id = $('#search_group_id option:selected').val();
        if (search_group_id) {
            $('.childsearch' + search_group_id).removeAttr('style');
        }
    });

    //expend-group-modal
    $('.expend-group-modal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');
        let group = button.data('group');
        if (!params) {
            return;
        }

        $('#parentGroupId').val(group.parent_id);
        $('#parentGroupId').change();
        $('#childGroup').val(group.id);

        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('select[name=plugin_unikey]').val(params.plugin_unikey);
        $(this).find('input[name=parameter]').val(params.parameter);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        }

        if (params.roles) {
            $(this).find('select[name="roles[]"]').val(params.roles.split(',')).change();
        }

        if (params.name) {
            $(this).find('.name-button').text(params.name);
        }
        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true)
            .click();
    });

    // expend type edit
    $('#createTypeModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');
        if (!params) {
            return;
        }

        let dataSources = params.data_sources;
        let postList = dataSources.postLists ? dataSources.postLists.pluginUnikey : null;
        let postFollow = dataSources.postFollows ? dataSources.postFollows.pluginUnikey : null;
        let postNearby = dataSources.postNearbys ? dataSources.postNearbys.pluginUnikey : null;

        $(this).find('input[name=rank_num]').val(params.rank_num);
        $(this).find('select[name=plugin_unikey]').val(params.plugin_unikey);
        if (postList) {
            $(this).find('select[name=post_list]').val(postList);
        }
        if (postFollow) {
            $(this).find('select[name=post_follow]').val(postFollow);
        }
        if (postNearby) {
            $(this).find('select[name=post_nearby]').val(postNearby);
        }

        $(this)
            .find('input:radio[name=is_enable][value="' + params.is_enable + '"]')
            .prop('checked', true);
    });

    // panel types edit
    $('#sortNumberModal').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }
        let button = $(e.relatedTarget);
        let params = button.data('params');
        let action = button.data('action');

        $(this).find('.sort-item').remove();
        $(this).find('form').attr('action', action);

        let template = $('#sortTemplate').contents();
        params = JSON.parse(params);
        $(this).data('languages', null);

        params.map((param) => {
            let titles = new Object();
            let descriptions = new Object();
            param.intro.map((item) => {
                titles[item.langTag] = item.title;
                descriptions[item.langTag] = item.description;
            });

            let sortTemplate = template.clone();
            sortTemplate.find('input[name="ids[]"]').val(param.id);
            sortTemplate.find('.sort-title').data('languages', param.intro);
            sortTemplate.find('input[name="titles[]"]').val(JSON.stringify(titles));
            sortTemplate.find('.sort-description').data('languages', param.intro);
            sortTemplate.find('input[name="descriptions[]"]').val(JSON.stringify(descriptions));

            sortTemplate.insertBefore($(this).find('.add-sort-tr'));
        });

        $('#sortNumberTitleLangModal');
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

    // content type data source
    $('#sortNumberModal .add-sort').click(function () {
        let template = $('#sortTemplate').clone();

        $(template.html()).insertBefore($('#sortNumberModal').find('.add-sort-tr'));
    });

    $(document).on('click', '.delete-sort-number', function () {
        $(this).closest('tr').remove();
    });

    $('#sortNumberTitleLangModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let languages = button.data('languages');
        $(this).find('form').trigger('reset');
        $(this).data('button', e.relatedTarget);

        if (!languages) {
            return;
        }

        languages.map((language) => {
            $(this)
                .find('input[name=' + language.langTag)
                .val(language.title);
        });
    });

    $('#sortNumberTitleLangModal').on('hide.bs.modal', function (e) {
        let button = $($(this).data('button'));
        $('#sortNumberModal').data('is_back', true);
        $('#sortNumberModal').modal('show');

        let titles = $(this).find('form').serializeArray();
        let data = new Object();
        titles.map((title) => {
            data[title.name] = title.value;
        });
        button.siblings('input').val(JSON.stringify(data));
    });

    $('#sortNumberDescLangModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let languages = button.data('languages');
        $(this).find('form').trigger('reset');
        $(this).data('button', e.relatedTarget);

        if (!languages) {
            return;
        }

        languages.map((language) => {
            $(this)
                .find('input[name=' + language.langTag)
                .val(language.description);
        });
    });

    $('#sortNumberDescLangModal').on('hide.bs.modal', function (e) {
        let button = $($(this).data('button'));
        $('#sortNumberModal').data('is_back', true);
        $('#sortNumberModal').modal('show');

        let descriptions = $(this).find('form').serializeArray();
        let data = new Object();
        descriptions.map((description) => {
            data[description.name] = description.value;
        });
        button.siblings('input').val(JSON.stringify(data));
    });

    $('#sortNumberModal').on('hide.bs.modal', function (e) {
        $(this).data('is_back', false);
    });

    // panel types edit end
    $('#importBlockWords').click(function () {
        console.log($('#importBlockInput'));
        $('#importBlockInput').click();
    });

    $('#importBlockInput').change(function () {
        $(this).closest('form').submit();
    });

    $('#emailModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let languages = button.data('languages');
        let isEnable = button.data('enable');
        let action = button.data('action');
        let title = button.data('title');

        $(this).find('form').trigger('reset');
        $(this)
            .find('input:radio[name=is_enable][value="' + isEnable + '"]')
            .prop('checked', true);
        $(this).find('form').attr('action', action);
        $(this).find('.modal-title').text(title);

        if (languages) {
            languages.map((language) => {
                $(this)
                    .find("input[name='titles[" + language.langTag + "]'")
                    .val(language.title);
                $(this)
                    .find("textarea[name='contents[" + language.langTag + "]'")
                    .val(language.content);
            });
        }
    });

    $('#smsModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let languages = button.data('languages');
        let isEnable = button.data('enable');
        let action = button.data('action');
        let title = button.data('title');

        $(this).find('form').trigger('reset');
        $(this)
            .find('input:radio[name=is_enable][value="' + isEnable + '"]')
            .prop('checked', true);
        $(this).find('form').attr('action', action);
        $(this).find('.modal-title').text(title);

        if (languages) {
            languages.map((language) => {
                $(this)
                    .find("input[name='sign_names[" + language.langTag + "]'")
                    .val(language.signName);
                $(this)
                    .find("input[name='template_codes[" + language.langTag + "]'")
                    .val(language.templateCode);
                $(this)
                    .find("input[name='code_params[" + language.langTag + "]'")
                    .val(language.codeParam);
            });
        }
    });

    $('.delete-button').click(function () {
        $('#deleteConfirm').data('button', $(this));
        $('#deleteConfirm').modal('show');
        return false;
    });

    $('#deleteSubmit').click(function () {
        let button = $('#deleteConfirm').data('button');
        button.parent('form').submit();
        $('#deleteConfirm').modal('hide');
    });

    // datetime select
    $('#publishPost').submit(function () {
        let type = $(this).find('select[name=post_limit_type]').val();
        let start = 0;
        let end = 0;

        if (type == 1) {
            start = $(this).find('input[name=post_limit_period_start]').val();
            end = $(this).find('input[name=post_limit_period_end]').val();

            if (end <= start) {
                window.tips(trans('tips.post_datetime_select_range_error')); //FsLang
                return false;
            }
        } else {
            start = $(this).find('input[name=post_limit_cycle_start]').val();
            end = $(this).find('input[name=post_limit_cycle_end]').val();
        }

        if (!start || !end) {
            window.tips(trans('tips.post_datetime_select_error')); //FsLang
            return false;
        }
    });

    $('#publishComment').submit(function () {
        let type = $(this).find('select[name=comment_limit_type]').val();
        let start = 0;
        let end = 0;
        if (type == 1) {
            start = $(this).find('input[name=comment_limit_period_start]').val();
            end = $(this).find('input[name=comment_limit_period_end]').val();

            if (end <= start) {
                window.tips(trans('tips.comment_datetime_select_range_error')); //FsLang
                return false;
            }
        } else {
            start = $(this).find('input[name=comment_limit_cycle_start]').val();
            end = $(this).find('input[name=comment_limit_cycle_end]').val();
        }

        if (!start || !end) {
            window.tips(trans('tips.comment_datetime_select_error')); //FsLang
            return false;
        }
    });

    $('#rolePermission').submit(function () {
        let postStatus = $(this).find('input[name="permission[post_limit_status]"]:checked').val();

        if (postStatus == 1) {
            let postType = $('#post_limit_type').val();
            let postStart = 0;
            let postEnd = 0;
            if (postType == 1) {
                postStart = $(this).find('input[name="permission[post_limit_period_start]"]').val();
                postEnd = $(this).find('input[name="permission[post_limit_period_end]"]').val();

                if (postEnd <= postStart) {
                    window.tips(trans('tips.post_datetime_select_range_error')); //FsLang
                    return false;
                }
            } else {
                postStart = $(this).find('input[name="permission[post_limit_cycle_start]"]').val();
                postEnd = $(this).find('input[name="permission[post_limit_cycle_end]"]').val();
            }

            if (!postStart || !postEnd) {
                window.tips(trans('tips.post_datetime_select_error')); //FsLang
                return false;
            }
        }

        let commentStatus = $(this).find('input[name="permission[comment_limit_status]"]:checked').val();

        if (commentStatus == 1) {
            let commentType = $('#comment_limit_type').val();
            let commentStart = 0;
            let commentEnd = 0;
            if (commentType == 1) {
                commentStart = $(this).find('input[name="permission[comment_limit_period_start]"]').val();
                commentEnd = $(this).find('input[name="permission[comment_limit_period_end]"]').val();

                if (commentEnd <= commentStart) {
                    window.tips(trans('tips.comment_datetime_select_range_error')); //FsLang
                    return false;
                }
            } else {
                commentStart = $(this).find('input[name="permission[comment_limit_cycle_start]"]').val();
                commentEnd = $(this).find('input[name="permission[comment_limit_cycle_end]"]').val();
            }

            if (!commentStart || !commentEnd) {
                window.tips(trans('tips.comment_datetime_select_error')); //FsLang
                return false;
            }
        }
    });
});
