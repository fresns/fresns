/*!
 * Fresns (https://fresns.org)
 * Copyright 2021-Present Jevan Tang
 * Licensed under the Apache-2.0 license
 */

/* Tooltips */
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// copyright-year
var yearElement = document.querySelector('.copyright-year');
var currentDate = new Date();
var currentYear = currentDate.getFullYear();
if (yearElement) {
    yearElement.textContent = currentYear;
}

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
    let html = `<div aria-live="polite" aria-atomic="true" class="position-fixed top-50 start-50 translate-middle" style="z-index:99999">
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

const sleep = (delay = 500) => {
    let t = Date.now();
    while (Date.now() - t <= delay) {
        continue;
    }
};

// progress
window.progress = {
    total: 100,
    valuenow: 0,
    speed: 1000,
    progressElement: null,
    stop: false,
    html: function (){
        return `<div class="progress-bar" role="progressbar" style="width: ${progress.valuenow}%" aria-valuenow="${progress.valuenow}" aria-valuemin="0" aria-valuemax="100">${progress.valuenow}</div>`
    },
    setProgressElement: function (pe){
        console.log(pe,111)
        this.progressElement = pe;
        return this;
    },
    init: function () {
        this.total = 100;
        this.valuenow = 0;
        this.progressElement = null;
        this.stop = false
        return this;
    },
    work: function () {
        $(this.progressElement).show();
        this.add(progress);
    },
    add: function (obj) {
        if (obj.stop !== true && obj.valuenow < obj.total) {
            let num = parseFloat(obj.total) - parseFloat(obj.valuenow);
            obj.valuenow = (parseFloat(obj.valuenow) + parseFloat(num / 100)).toFixed(2);
            obj.progressElement.empty().append(obj.html())
        } else {
            obj.progressElement.empty().append(obj.html())
            return;
        }
        setTimeout(function(){
            obj.add(obj)
        }, obj.speed)
    },
    exit: function () {
        this.stop = true;
        $(this.progressElement).hide();
        sleep(1000)
        return this;
    },
    done: function () {
        this.valuenow = this.total;
        sleep(1000)
        return this;
    },
    clearHtml: function () {
        this.progressElement?.empty();
    }
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

// reload page
function reloadPage()
{
    location.reload();
}

function progressDown() {
    progress.done();
}

function progressExit() {
    progress.exit();
}

// settings
$(document).ready(function () {
    // progress bar
    $(".fresns-modal").on('show.bs.modal', function() {
        $('.ajax-progress-submit').show().removeAttr("disabled");
        $(".ajax-progress").empty();
    })

    $(".ajax-progress-submit").on('click', function(event) {
        event.preventDefault();

        // set progress
        progress.init().setProgressElement($('.ajax-progress').removeClass('d-none')).work();
    })

    // fresns upgrade button
    $('#autoUpgradeButton').click(function () {
        if ($(this).data('upgrading')) {
            $('#autoUpgradeStepModal').modal('show');
        } else {
            $('#autoUpgradeModal').modal('show');
        }
    });
    $('#manualUpgradeButton').click(function () {
        if ($(this).data('upgrading')) {
            $('#manualUpgradeStepModal').modal('show');
        } else {
            $('#manualUpgradeModal').modal('show');
        }
    });

    // fresns auto upgrade form
    $('#autoUpgradeForm').submit(function () {
        $('#autoUpgradeModal').modal('hide');
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: $(this).attr('action'),
            success: function (response) {
                $('#manualUpgradeButton').addClass('d-none');
                $('#manualUpgradeGuide').addClass('d-none');

                $('#autoUpgradeButton').removeClass('btn-primary').addClass('btn-info').text(trans('tips.upgrade_in_progress')); //FsLang

                $('#autoUpgradeButton').data('upgrading', true);

                $('#autoUpgradeStepModal').modal('show');
            },
            error: function (response) {
                window.tips(response.responseJSON.message);
            },
        });

        return false;
    });

    // fresns manual upgrade form
    $('#manualUpgradeForm').submit(function () {
        $('#manualUpgradeModal').modal('hide');
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: $(this).attr('action'),
            success: function (response) {
                $('#autoUpgradeButton').addClass('d-none');

                $('#manualUpgradeButton').removeClass('btn-primary').addClass('btn-info').text(trans('tips.upgrade_in_progress')); //FsLang

                $('#manualUpgradeButton').data('upgrading', true);

                $('#manualUpgradeStepModal').modal('show');
            },
            error: function (response) {
                window.tips(response.responseJSON.message);
            },
        });

        return false;
    });

    var upgradeTimer = null;

    // check upgrade step
    function checkAutoUpgradeStep(action) {
        if (!action) {
            return;
        }
        $.ajax({
            method: 'get',
            url: action,
            success: function (response) {
                let autoUpgradeStep = response.autoUpgradeStep || 6,
                    autoUpgradeTip = response.autoUpgradeTip;

                let step = $('#autoUpgradeStepModal').find('#auto-upgrade-' + autoUpgradeStep);
                step.find('i').remove();
                step.prepend('<i class="upgrade-step spinner-border spinner-border-sm me-2" role="status"></i>');

                step.prevAll().map((index, completeStep) => {
                    $(completeStep).find('i').remove();
                    $(completeStep).prepend('<i class="bi bi-check-lg text-success me-2"></i>');
                });

                if (autoUpgradeStep == 0) {
                    step.find('i').remove();
                    step.prepend('<i class="bi bi-x-lg text-danger me-2"></i>');

                    $('#autoUpgradeButton').addClass('btn-danger').removeClass('btn-info').text(trans('tips.installFailure')); //FsLang
                    $('#autoUpgradeButton').data('upgrading', true);
                    $('#autoUpgradeTip').removeClass('d-none').text(autoUpgradeTip);

                    $('#upgradeStepModal').data('installFailure', 1);
                    clearInterval(upgradeTimer);
                    return;
                }

                if (!autoUpgradeStep || autoUpgradeStep == 6) {
                    step.find('i').remove();
                    step.prepend('<i class="bi bi-check-lg text-success me-2"></i>');

                    $('#autoUpgradeButton').addClass('btn-light').removeClass('btn-info').text(trans('tips.upgradeSuccess')); //FsLang
                    $('#autoUpgradeButton').data('upgrading', true);

                    $('#upgradeStepModal').data('upgradeSuccess', 1);
                    clearInterval(upgradeTimer);
                    return;
                }
            },
        });
    }
    function checkManualUpgradeStep(action) {
        if (!action) {
            return;
        }
        $.ajax({
            method: 'get',
            url: action,
            success: function (response) {
                let manualUpgradeStep = response.manualUpgradeStep || 7,
                    manualUpgradeTip = response.manualUpgradeTip;

                let step = $('#manualUpgradeStepModal').find('#manual-upgrade-' + manualUpgradeStep);
                step.find('i').remove();
                step.prepend('<i class="upgrade-step spinner-border spinner-border-sm me-2" role="status"></i>');

                step.prevAll().map((index, completeStep) => {
                    $(completeStep).find('i').remove();
                    $(completeStep).prepend('<i class="bi bi-check-lg text-success me-2"></i>');
                });

                if (manualUpgradeStep == 0) {
                    step.find('i').remove();
                    step.prepend('<i class="bi bi-x-lg text-danger me-2"></i>');

                    $('#manualUpgradeButton').addClass('btn-danger').removeClass('btn-info').text(trans('tips.installFailure')); //FsLang
                    $('#manualUpgradeButton').data('upgrading', true);
                    $('#manualUpgradeTip').removeClass('d-none').text(manualUpgradeTip);

                    $('#upgradeStepModal').data('installFailure', 1);
                    clearInterval(upgradeTimer);
                    return;
                }

                if (!manualUpgradeStep || manualUpgradeStep == 7) {
                    step.find('i').remove();
                    step.prepend('<i class="bi bi-check-lg text-success me-2"></i>');

                    $('#manualUpgradeButton').addClass('btn-light').removeClass('btn-info').text(trans('tips.upgradeSuccess')); //FsLang
                    $('#manualUpgradeButton').data('upgrading', true);

                    $('#upgradeStepModal').data('upgradeSuccess', 1);
                    clearInterval(upgradeTimer);
                    return;
                }
            },
        });
    }

    // monitoring and upgrading process
    $('#autoUpgradeStepModal').on('show.bs.modal', function (e) {
        let button = $('#autoUpgradeButton');
        let action = button.data('action');

        if (!upgradeTimer) {
            checkAutoUpgradeStep(action);
            upgradeTimer = setInterval(checkAutoUpgradeStep, 5000, action);
        }
    });
    $('#manualUpgradeStepModal').on('show.bs.modal', function (e) {
        let button = $('#manualUpgradeButton');
        let action = button.data('action');

        if (!upgradeTimer) {
            checkManualUpgradeStep(action);
            upgradeTimer = setInterval(checkManualUpgradeStep, 5000, action);
        }
    });

    // select2
    $('.select2').select2({
        theme: 'bootstrap-5',
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
        let url = $(this).data('url');
        if (url) {
            $('#imageZoom').find('img').attr('src', url);
            $('#imageZoom').modal('show');
        }
    });

    // admin config
    $('#adminConfig .update-panel-url').change(function () {
        let systemUrl = $('#adminConfig').find('input[name=system_url]').val();
        let panelPath = $('#adminConfig').find('input[name=panel_path]').val();
        $('#adminConfig')
            .find('#panelUrl')
            .text(systemUrl + '/fresns/' + panelPath);
    });

    // update session key
    $('#updateKey').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            id = button.data('id'),
            name = button.data('name'),
            type = button.data('type'),
            isReadOnly = button.data('is_read_only'),
            isEnabled = button.data('is_enabled'),
            pluginFskey = button.data('plugin_fskey'),
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
            .find('input:radio[name=is_read_only][value="' + isReadOnly + '"]')
            .prop('checked', true);
        $(this)
            .find('input:radio[name=is_enabled][value="' + isEnabled + '"]')
            .prop('checked', true);
        $(this).find('#key_plugin').val(pluginFskey);
    });

    $('#updateKey,#createKey')
        .find('input[name=type]')
        .change(function () {
            if ($(this).val() == 3) {
                $(this).closest('form').find('select[name=plugin_fskey]').prop('required', true);
            } else {
                $(this).closest('form').find('select[name=plugin_fskey]').prop('required', false);
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

    $(document).on('click', 'input.order-number', function () {
        return false;
    });

    $(document).on('change', 'input.order-number', function () {
        $.ajax({
            method: 'post',
            url: $(this).data('action'),
            data: {
                order: $(this).val(),
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

        let isEnabled = language.isEnabled ? 1 : 0;

        $(this).find('form').attr('action', action);
        $(this).find('input[name=order]').val(language.order);
        $(this).find('input[name=lang_tag]').val(language.langTag);
        $(this).find('select[name=length_units]').val(language.lengthUnits);
        $(this).find('select[name=date_format]').val(language.dateFormat);
        $(this).find('input[name=time_format_minute]').val(language.timeFormatMinute);
        $(this).find('input[name=time_format_hour]').val(language.timeFormatHour);
        $(this).find('input[name=time_format_day]').val(language.timeFormatDay);
        $(this).find('input[name=time_format_month]').val(language.timeFormatMonth);
        $(this).find('input[name=time_format_year]').val(language.timeFormatYear);
        $(this)
            .find('input:radio[name=is_enabled][value="' + isEnabled + '"]')
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

                if ($('.btn-primary[data-url]').data('url')) {
                    window.location.href = $('.btn-primary[data-url]').data('url');
                    window.location.reload();
                }
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

    // user extcredits setting
    $('#extcreditsSetting').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            id = button.data('id'),
            state = button.data('state');

        $(this).find('input[name=extcreditsId]').val(id);
        $(this)
            .find('input:radio[name=extcredits_state][value="' + state + '"]')
            .prop('checked', true);
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

        //reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();

        if (!params) {
            return;
        }
        let configParams = button.data('config_params');

        if (params.icon_file_url) {
            $(this).find('input[name=icon_file_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_file_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        }
        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('select[name=plugin_fskey]').val(params.plugin_fskey);
        $(this).find('select[name=parameter]').val(params.parameter);
        $(this).find('input[name=app_id]').val(configParams.appId);
        $(this).find('input[name=app_key]').val(configParams.appKey);
        $(this)
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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

        //reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();

        if (!params) {
            return;
        }

        if (params.image_file_url) {
            $(this).find('input[name=image_file_url]').val(params.image_file_url);
            $(this).find('input[name=image_file_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        } else {
            $(this).find('input[name=image_file_url]').val('');
        }

        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('input[name=code]').val(params.code);
        $(this)
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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
                .find('input.sticker-rating')
                .attr('name', 'rating[' + sticker.id + ']')
                .val(sticker.rating);

            stickerTemplate.find('.sticker-img').attr('src', sticker.stickerUrl);
            stickerTemplate.find('.sticker-code').html(sticker.code);

            stickerTemplate.find('input.sticker-enable').attr('name', 'enable[' + sticker.id + ']');
            if (sticker.is_enabled) {
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

        //reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();

        if (!params) {
            return;
        }

        $(this).find('select[name=type]').val(params.type);
        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('input[name=name]').val(params.name);
        if (params.is_display_name) {
            $(this).find('input[name=is_display_name]').attr('checked', 'checked');
        }
        if (params.is_display_icon) {
            $(this).find('input[name=is_display_icon]').attr('checked', 'checked');
        }

        if (params.icon_file_url) {
            $(this).find('input[name=icon_file_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_file_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        } else {
            $(this).find('input[name=icon_file_url]').val('');
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
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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

    $(document).on('click', '.delete-custom-perm', function () {
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
                is_enabled: $(this).data('enable')
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
                progressDown && progressDown()
                var ansi_up = new AnsiUp;
                var html = ansi_up.ansi_to_html(response);

                console.log('uninstall response', html)
                $('#uninstall_artisan_output').html(html || trans('tips.uninstallSuccess')) //FsLang
            },
            error: function (response) {
                progressExit && progressExit();

                var errorMessage = response && response.responseJSON && response.responseJSON.message ? response.responseJSON.message : "Unknown error";
                $('#uninstall_artisan_output').html(errorMessage + "<br><br>" + trans('tips.uninstallFailure'));

                window.tips(errorMessage);
            },
        });
    });

    $('#installModal').on('show.bs.modal', function (e) {
        $('#inputFskeyOrInputFile').text('').hide()
        $('input[name=plugin_fskey]').removeClass('is-invalid')
        $('input[name=plugin_zipball]').removeClass('is-invalid')
    });

    $('.install_method').click(function (e) {
        let install_method = $(this).parent().data('name')
        $('input[name=install_method]').val(install_method)
    });

    $('#installSubmit').click(function (e) {
        e.preventDefault();

        let install_method = $('input[name=install_method]').val()
        let plugin_fskey = $('input[name=plugin_fskey]').val()
        let plugin_directory = $('input[name=plugin_directory]').val()
        let plugin_zipball = $('input[name=plugin_zipball]').val()

        if (plugin_fskey || plugin_zipball || plugin_directory) {
            $(this).submit()
            $('#installStepModal').modal('toggle')
            return;
        }

        if (install_method == 'inputFskey' && !plugin_fskey) {
            $('input[name=plugin_fskey]').addClass('is-invalid')
            $('#inputFskeyOrInputFile').text(trans('tips.install_not_entered_key')).show() // FsLang
            return;
        }

        if (install_method == 'inputDirectory' && !plugin_zipball) {
            $('input[name=plugin_directory]').addClass('is-invalid')
            $('#inputFskeyOrInputFile').text(trans('tips.install_not_entered_directory')).show() // FsLang
            return;
        }

        if (install_method == 'inputZipball' && !plugin_zipball) {
            $('input[name=plugin_zipball]').addClass('is-invalid')
            $('#inputFskeyOrInputFile').text(trans('tips.install_not_upload_zip')).show() // FsLang
            return;
        }
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
            action = button.data('action');
            isEnabled = button.data('is_enabled');
            noType = button.data('no_type');
            indexType = button.data('type');
            noQueryState = button.data('no_query_state');
            queryState = button.data('query_state');
            noQueryConfig = button.data('no_query_config');
            queryConfig = button.data('query_config');

        if (noType) {
            $(this).find('.index-type').hide();
        } else {
            $(this).find('.index-type').show();
        }

        if (noQueryState) {
            $(this).find('.query-state').hide();
        } else {
            $(this).find('.query-state').show();
        }

        if (noQueryConfig) {
            $(this).find('.query-config').hide();
        } else {
            $(this).find('.query-config').show();
        }

        $(this).find('form').attr('action', action);
        $(this)
            .find('input:radio[name=is_enabled][value="' + isEnabled + '"]')
            .prop('checked', true);
        $(this)
            .find('input:radio[name=index_type][value="' + indexType + '"]')
            .prop('checked', true);
        $(this)
            .find('input:radio[name=query_state][value="' + queryState + '"]')
            .prop('checked', true);
        $(this).find('textarea[name=query_config]').val(queryConfig);
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

    // extend services
    $('.extend-modal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');

        //reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();

        if (!params) {
            return;
        }

        if (params.icon_file_url) {
            $(this).find('input[name=icon_file_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_file_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        } else {
            $(this).find('input[name=icon_file_url]').val('');
        }
        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('select[name=plugin_fskey]').val(params.plugin_fskey);
        $(this).find('input[name=parameter]').val(params.parameter);
        if (params.name) {
            $(this).find('.name-button').text(params.name);
        }
        $(this)
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
            .prop('checked', true)
            .click();
    });

    // group edit
    $('.edit-group-category').click(function () {
        let action = $(this).data('action');
        let params = $(this).data('params');
        let coverUrl = $(this).data('cover-url');
        let bannerUrl = $(this).data('banner-url');
        let form = $('#createCategoryModal').parent('form');

        form.find('input[name=_method]').val(params ? 'put' : 'post');
        form.attr('action', action);
        form.trigger('reset');
        form.find('.name-button').text(trans('panel.table_name')); //FsLang
        form.find('.desc-button').text(trans('panel.table_description')); //FsLang

        //reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();
        $('#category_cover_file_view').hide();
        $('#category_banner_file_view').hide();

        if (params) {
            $('#createCategoryModal').find('input[name=rating]').val(params.rating);
            $('#createCategoryModal')
                .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
                .prop('checked', true);

            if (params.cover_file_id) {
                $('#category_cover_file_view').css('display', 'block');
                $('#category_cover_file_view').attr('href', coverUrl);
            }

            if (params.cover_file_url) {
                $('#createCategoryModal').find('input[name=cover_file_url]').parent().find('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
                $('#createCategoryModal').find('input[name=cover_file_url]').parent().find('.inputFile').css('display', 'none');

                $('#createCategoryModal').find('input[name=cover_file_url]').val(params.cover_file_url);
                $('#createCategoryModal').find('input[name=cover_file_url]').css('display', 'block');
            }

            if (params.banner_file_id) {
                $('#category_banner_file_view').css('display', 'block');
                $('#category_banner_file_view').attr('href', bannerUrl);
            }

            if (params.banner_file_url) {
                $('#createCategoryModal').find('input[name=banner_file_url]').parent().find('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
                $('#createCategoryModal').find('input[name=banner_file_url]').parent().find('.inputFile').css('display', 'none');

                $('#createCategoryModal').find('input[name=banner_file_url]').val(params.banner_file_url);
                $('#createCategoryModal').find('input[name=banner_file_url]').css('display', 'block');
            }

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
        let coverUrl = button.data('cover-url');
        let bannerUrl = button.data('banner-url');

        let form = $(this).parents('form');
        let selectAdmin = form.find('select[name="admin_ids[]"]');

        form.attr('action', action);
        form.find('input[name=_method]').val(params ? 'put' : 'post');

        form.trigger('reset');
        form.find('.name-button').text(trans('panel.table_name')); //FsLang
        form.find('.desc-button').text(trans('panel.table_description')); //FsLang
        selectAdmin.find('option').remove();

        //reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();
        $('#cover_file_view').hide();
        $('#banner_file_view').hide();

        if (!params) {
            return;
        }

        form.find('select[name=parent_id]').val(params.parent_id);
        form.find('input[name=rating]').val(params.rating);
        form.find('select[name=plugin_fskey]').val(params.plugin_fskey);

        let names = button.data('names');
        let descriptions = button.data('descriptions');
        let defaultName = $(this).data('default-name');
        let defaultDesc = $(this).data('default-desc');
        let adminUsers = button.data('admin_users');

        if (params.cover_file_id) {
            $('#cover_file_view').css('display', 'block');
            $('#cover_file_view').attr('href', coverUrl);
        }

        if (params.cover_file_url) {
            $('#groupModal').find('input[name=cover_file_url]').parent().find('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('#groupModal').find('input[name=cover_file_url]').parent().find('.inputFile').css('display', 'none');
            form.find('input[name=cover_file_url]').val(params.cover_file_url);
            form.find('input[name=cover_file_url]').css('display', 'block');
        }

        if (params.banner_file_id) {
            $('#banner_file_view').css('display', 'block');
            $('#banner_file_view').attr('href', bannerUrl);
        }

        if (params.banner_file_url) {
            $('#groupModal').find('input[name=banner_file_url]').parent().find('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('#groupModal').find('input[name=banner_file_url]').parent().find('.inputFile').css('display', 'none');
            form.find('input[name=banner_file_url]').val(params.banner_file_url);
            form.find('input[name=banner_file_url]').css('display', 'block');
        }

        form.find('input:radio[name=type_mode][value="' + params.type_mode + '"]')
            .prop('checked', true)
            .click();
        form.find('select[name="permissions[mode_whitelist_roles][]"]').val(params.permissions.mode_whitelist_roles).change();

        form.find('select[name=plugin_fskey]').val(params.plugin_fskey);

        let adminIds = [];
        if (adminUsers) {
            adminUsers.map((user) => {
                adminIds.push(user.id);
                let text = user.nickname + '(@' + user.username + ')';
                let newOption = new Option(text, user.id, true, true);
                form.find('select[name="admin_ids[]"]').append(newOption);
            });
        }
        form.find('select[name="admin_ids[]"]').val(adminIds).change();
        form.find('input:radio[name=type_find][value="' + params.type_find + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name=type_follow][value="' + params.type_follow + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name=is_recommend][value="' + params.is_recommend + '"]')
            .prop('checked', true)
            .click();

        let permissions = params.permissions;
        let publishPost = permissions.publish_post ? permissions.publish_post : 0;
        let publishPostReview = permissions.publish_post_review ? 1 : 0;
        let publishComment = permissions.publish_comment ? permissions.publish_comment : 0;
        let publishCommentReview = permissions.publish_comment_review ? 1 : 0;

        form.find('input:radio[name="permissions[publish_post]"][value="' + publishPost + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name="permissions[publish_post_review]"][value="' + publishPostReview + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name="permissions[publish_comment]"][value="' + publishComment + '"]')
            .prop('checked', true)
            .click();
        form.find('input:radio[name="permissions[publish_comment_review]"][value="' + publishCommentReview + '"]')
            .prop('checked', true)
            .click();

        form.find('select[name="permissions[publish_post_roles][]"]').val(params.permissions.publish_post_roles).change();
        form.find('select[name="permissions[publish_comment_roles][]"]')
            .val(params.permissions.publish_comment_roles)
            .change();
        form.find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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

    //expend-editor-modal
    $('.expend-editor-modal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');

        if (!params) {
            return;
        }
        $('#inlineCheckbox1').removeAttr('checked');
        $('#inlineCheckbox2').removeAttr('checked');
        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('select[name=plugin_fskey]').val(params.plugin_fskey);
        $(this).find('input[name=parameter]').val(params.parameter);
        $(this).find('input[name=editor_number]').val(params.editor_number);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_file_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_file_url]').removeAttr('style');
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
            .find('input:radio[name=editor_toolbar][value="' + params.editor_toolbar + '"]')
            .prop('checked', true)
            .click();
        $(this)
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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

        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('select[name=plugin_fskey]').val(params.plugin_fskey);
        $(this).find('input[name=parameter]').val(params.parameter);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_file_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_file_url]').removeAttr('style');
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
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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
        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('select[name=plugin_fskey]').val(params.plugin_fskey);
        $(this).find('input[name=parameter]').val(params.parameter);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_file_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_file_url]').removeAttr('style');
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
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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
        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('select[name=plugin_fskey]').val(params.plugin_fskey);
        $(this).find('input[name=parameter]').val(params.parameter);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_file_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_file_url]').removeAttr('style');
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
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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

        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('select[name=plugin_fskey]').val(params.plugin_fskey);
        $(this).find('input[name=parameter]').val(params.parameter);

        $('.inputUrl').css('display', 'none');
        $('.inputFile').removeAttr('style');
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        if (params.icon_file_url) {
            $(this).find('input[name=icon_file_url]').val(params.icon_file_url);
            $(this).find('input[name=icon_file_url]').removeAttr('style');
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
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
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

    // expend type edit
    $('#createTypeModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let params = button.data('params');
        if (!params) {
            return;
        }

        let dataSources = params.data_sources;
        let postByAll = dataSources.postByAll ? dataSources.postByAll.pluginFskey : null;
        let postByFollow = dataSources.postByFollow ? dataSources.postByFollow.pluginFskey : null;
        let postByNearby = dataSources.postByNearby ? dataSources.postByNearby.pluginFskey : null;
        let commentByAll = dataSources.commentByAll ? dataSources.commentByAll.pluginFskey : null;
        let commentByFollow = dataSources.commentByFollow ? dataSources.commentByFollow.pluginFskey : null;
        let commentByNearby = dataSources.commentByNearby ? dataSources.commentByNearby.pluginFskey : null;

        $('#inlineCheckbox1').removeAttr('checked');
        $('#inlineCheckbox2').removeAttr('checked');

        $(this).find('input[name=rating]').val(params.rating);
        $(this).find('select[name=plugin_fskey]').val(params.plugin_fskey);

        if (postByAll) {
            $(this).find('select[name=post_all]').val(postByAll);
        }
        if (postByFollow) {
            $(this).find('select[name=post_follow]').val(postByFollow);
        }
        if (postByNearby) {
            $(this).find('select[name=post_nearby]').val(postByNearby);
        }
        if (commentByAll) {
            $(this).find('select[name=comment_all]').val(commentByAll);
        }
        if (commentByFollow) {
            $(this).find('select[name=comment_follow]').val(commentByFollow);
        }
        if (commentByNearby) {
            $(this).find('select[name=comment_nearby]').val(commentByNearby);
        }

        if (params.scene) {
            scene = params.scene.split(',');
            for (var i = 0; i < scene.length; i++) {
                if (scene[i] == 1) {
                    $('#inlineCheckbox1').attr('checked', 'checked');
                }
                if (scene[i] == 2) {
                    $('#inlineCheckbox2').attr('checked', 'checked');
                }
            }
        }

        $(this)
            .find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]')
            .prop('checked', true);
    });

    // panel types edit
    $('#pluginRatingModal').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }
        let button = $(e.relatedTarget);
        let params = button.data('params');
        let action = button.data('action');
        let defaultLanguage = button.data('default_language');

        $(this).find('.rating-item').remove();
        $(this).find('form').attr('action', action);

        let template = $('#ratingTemplate').contents();
        params = JSON.parse(params);
        $(this).data('languages', null);

        params.map((param) => {
            let titles = new Object();
            let descriptions = new Object();

            let title, description;
            param.intro.map((item) => {
                titles[item.langTag] = item.title;
                descriptions[item.langTag] = item.description;

                if (item.langTag == defaultLanguage) {
                    title = item.title;
                    description = item.description;
                }
            });

            let ratingTemplate = template.clone();
            ratingTemplate.find('input[name="ids[]"]').val(param.id);
            ratingTemplate.find('.rating-title').data('languages', param.intro);
            ratingTemplate.find('input[name="titles[]"]').val(JSON.stringify(titles));
            ratingTemplate.find('.rating-description').data('languages', param.intro);
            ratingTemplate.find('input[name="descriptions[]"]').val(JSON.stringify(descriptions));

            if (title) {
                ratingTemplate.find('button.rating-title').text(title);
            }
            if (description) {
                ratingTemplate.find('button.rating-description').text(description);
            }

            ratingTemplate.insertBefore($(this).find('.add-rating-tr'));
        });

        $('#pluginRatingTitleLangModal');
    });

    // selectInputType
    $('.selectInputType li').click(function () {
        let inputName = $(this).data('name');

        $(this).parent().siblings('.showSelectTypeName').text($(this).text());
        $(this).parent().siblings('input').css('display', 'none');
        $(this)
            .parent()
            .siblings('.' + inputName)
            .removeAttr('style');
    });

    // content type data source
    $('#pluginRatingModal .add-rating').click(function () {
        let template = $('#ratingTemplate').clone();

        $(template.html()).insertBefore($('#pluginRatingModal').find('.add-rating-tr'));
    });

    $(document).on('click', '.delete-rating-number', function () {
        $(this).closest('tr').remove();
    });

    $('#pluginRatingTitleLangModal').on('show.bs.modal', function (e) {
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

    $('#pluginRatingTitleLangModal').on('hide.bs.modal', function (e) {
        let button = $($(this).data('button'));
        $('#pluginRatingModal').data('is_back', true);
        $('#pluginRatingModal').modal('show');

        let titles = $(this).find('form').serializeArray();
        let data = new Object();
        titles.map((title) => {
            data[title.name] = title.value;
        });
        button.siblings('input').val(JSON.stringify(data));
    });

    $('#pluginRatingDescLangModal').on('show.bs.modal', function (e) {
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

    $('#pluginRatingDescLangModal').on('hide.bs.modal', function (e) {
        let button = $($(this).data('button'));
        $('#pluginRatingModal').data('is_back', true);
        $('#pluginRatingModal').modal('show');

        let descriptions = $(this).find('form').serializeArray();
        let data = new Object();
        descriptions.map((description) => {
            data[description.name] = description.value;
        });
        button.siblings('input').val(JSON.stringify(data));
    });

    $('#pluginRatingModal').on('hide.bs.modal', function (e) {
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
        let isEnabled = button.data('enable');
        let action = button.data('action');
        let title = button.data('title');

        $(this).find('form').trigger('reset');
        $(this)
            .find('input:radio[name=is_enabled][value="' + isEnabled + '"]')
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
        let isEnabled = button.data('enable');
        let action = button.data('action');
        let title = button.data('title');

        $(this).find('form').trigger('reset');
        $(this)
            .find('input:radio[name=is_enabled][value="' + isEnabled + '"]')
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
        let status = $(this).find('input:radio[name=post_limit_status]:checked').val();
        let start = 0;
        let end = 0;

        if (status == 'false') {
            return;
        }

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
        let status = $(this).find('input:radio[name=comment_limit_status]:checked').val();
        let start = 0;
        let end = 0;

        if (status == 'false') {
            return;
        }

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

    // plugin install form
    $('#installModal form').submit(function (event) {
        event.preventDefault();

        $.ajax({
            method: $(this).attr('method'), // post form
            url: $(this).attr('action'),
            data: new FormData(document.querySelector('#installModal form')),
            contentType: false,
            processData: false,
            success: function (response) {
                progressDown && progressDown()
                var ansi_up = new AnsiUp;
                var html = ansi_up.ansi_to_html(response);

                console.log('install response', html)
                $('#install_artisan_output').html(html || trans('tips.installSuccess')) //FsLang
            },
            error: function (response) {
                progressExit && progressExit();

                var errorMessage = response && response.responseJSON && response.responseJSON.message ? response.responseJSON.message : "Unknown error";
                $('#install_artisan_output').html(errorMessage + "<br><br>" + trans('tips.installFailure'));

                window.tips(errorMessage);
            },
        });
    });

    // plugin upgrade
    $('.upgrade-plugin').on('click', function () {
        let fskey = $(this).data('fskey');
        let name = $(this).data('name');
        let version = $(this).data('version');
        let upgradeVersion = $(this).data('new-version');

        $('.plugin-name').text(name)
        $('.plugin-version').text(version)
        $('.plugin-new-version').text(upgradeVersion)
        $('input[name=fskey]').val(fskey)
    });

    // download apps
    $('.download-apps').on('click', function () {
        let fskey = $(this).data('fskey');
        let name = $(this).data('name');
        let upgradeVersion = $(this).data('new-version');

        $('.app-name').text(name)
        $('.app-new-version').text(upgradeVersion)
        $('input[name=app_fskey]').val(fskey)
    });

    // delete app
    $('.delete-app').on('click', function () {
        let fskey = $(this).data('fskey');
        let name = $(this).data('name');

        $('.app-name').text(name)
        $('input[name=app_fskey]').val(fskey)
    });

    // plugin upgrade form
    $('#upgradePlugin form').submit(function (event) {
        event.preventDefault();

        // set progress
        progress.init().setProgressElement($('.ajax-progress').removeClass('d-none')).work();

        $.ajax({
            method: $(this).attr('method'),
            url: $(this).attr('action'),
            dataType: 'json',
            data: new FormData(document.querySelector('#upgradePlugin form')),
            contentType: false,
            processData: false,
            success: function (response) {
                progressDown && progressDown()
                console.log('upgrade response', response)
                var ansi_up = new AnsiUp;
                var html = ansi_up.ansi_to_html(response.data.output);
                $('#upgrade_artisan_output').html(html || trans('tips.upgradeSuccess')) //FsLang
            },
            error: function (response) {
                progressExit && progressExit();

                var errorMessage = response && response.responseJSON && response.responseJSON.message ? response.responseJSON.message : "Unknown error";
                $('#upgrade_artisan_output').html(errorMessage + "<br><br>" + trans('tips.upgradeFailure'));

                window.tips(errorMessage);
            },
        });
    });

    $('#walletCurrencyName').on('hide.bs.modal', function(e) {
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

        if (defaultName) {
            $('#currencyNameButton').text(defaultName);
        }
    });

    $('#walletCurrencyUnit').on('hide.bs.modal', function(e) {
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

        if (defaultName) {
            $('#currencyUnitButton').text(defaultName);
        }
    });

    $('#editMessages').on('show.bs.modal', function(e) {
        let button = $(e.relatedTarget);
        let action = button.data('action');
        let messages = button.data('messages');
        let name = button.data('name');
        let code = button.data('code');
        let fskey= button.data('fskey');

        $(this).find('.code-message-plugin-name').text(name)
        $(this).find('.code-message-plugin-code').text(code)
        $(this).find('.code-message-plugin-fskey').text(fskey)

        $(this).find('form').attr('action', action);
        Object.entries(messages).forEach(([langTag, message]) => {
            console.log($(this).find("textarea[name='messages[" + langTag + "]'"))
            $(this).find("textarea[name='messages[" + langTag + "]'")
                    .val(message);
        })
    });

    $('#downloadModal form').submit(function () {
        $('#downloadModal').modal('hide');
        $('#downloadResultModal .modal-body').html(`<div class="my-3 ms-3">
            <div class="spinner-border spinner-border-sm me-1" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            ${trans('tips.request_in_progress')}
        </div>`);
        $('#downloadResultModal').modal('show');

        $.ajax({
            method: $(this).attr('method'),
            data: new FormData(document.querySelector('#downloadModal form')),
            contentType: false,
            processData: false,
            url: $(this).attr('action'),
            success: function (response) {
                console.log(response)
                if (response.code != 0) {
                    html = `<div class="alert alert-danger" role="alert">[${response.code}] ${response.message}</div>`;
                } else {
                    const data = response.data

                    html = `<ul class="list-group list-group-flush">
                        <li class="list-group-item d-none">${data.fskey}</li>
                        <li class="list-group-item">${data.name} <span class="badge bg-secondary ms-1 fs-9">${data.version}</span></li>
                        <li class="list-group-item">${data.description}</li>
                        <li class="list-group-item text-center"><a class="btn btn-primary" href="${data.zipBall}" target="_blank" role="button">${trans('panel.button_download')}</a></li>
                    </ul>`
                }

                $('#downloadResultModal .modal-body').html(html)
            },
            error: function (response) {
                console.error(response)
                window.tips(response.responseJSON.message);
            },
        });

        return false;
    });
});

// fresns extensions callback
window.onmessage = function (event) {
    let fresnsCallback;

    try {
        fresnsCallback = JSON.parse(event.data);
    } catch (error) {
        return;
    }

    console.log('fresnsCallback', fresnsCallback);

    if (!fresnsCallback) {
        return;
    }

    if (fresnsCallback.code != 0) {
        if (fresnsCallback.message) {
            window.tips(fresnsCallback.message, fresnsCallback.code);
        }
        return;
    }

    switch (fresnsCallback.action.postMessageKey) {
        case 'fresnsInstallExtension':
            // (new bootstrap.Modal('#installModal')).show();

            setTimeout(function () {
                console.log("Install Extension ", fresnsCallback.data.fskey)

                $('.showSelectTypeName').text($('#installModal .selectInputType li[data-name="inputDirectory"]').text())
                $('input[name="install_method"]').val('inputFskey')
                $('input[name="plugin_fskey"]').val(fresnsCallback.data.fskey)

                $('input[name="plugin_fskey"]').css('display', 'block').siblings('input').css('display', 'none')
                $('input[name="plugin_fskey"]').attr('required', true).siblings('input').attr('required', false)

                $('#installSubmit').click()
            }, 1000);
            break;
        case 'fresnsDownloadExtension':
            // (new bootstrap.Modal('#downloadModal')).show();

            setTimeout(function () {
                console.log("Download Extension ", fresnsCallback.data.fskey)

                $('input[name="app_fskey"]').val(fresnsCallback.data.fskey)

                $('#downloadSubmit').click()
            }, 1000);
            break;
    }

    if (fresnsCallback.action.windowClose) {
        $('#fresnsModal').modal('hide');
    }

    if (fresnsCallback.action.redirectUrl) {
        window.location.href = fresnsCallback.action.redirectUrl;
    }

    console.log('fresnsCallback end');
};
