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
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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

$(document).on('submit', 'form', function () {
    var btn = $(this).find('button[type="submit"]');

    btn.find('i').remove();

    btn.prop('disabled', true);
    if (btn.children('.spinner-border').length == 0) {
        btn.prepend(
            '<span class="spinner-border spinner-border-sm mg-r-5 d-none" role="status" aria-hidden="true"></span> '
        );
    }
    btn.children('.spinner-border').removeClass('d-none');
});

// settings
$(document).ready(function () {
    // btn spinner
    $('.fs-btn-spinner').click(function () {
        var btn = $(this);

        btn.find('i').remove();

        btn.prop('disabled', true);
        btn.addClass('disabled');
        if (btn.children('.spinner-border').length == 0) {
            btn.prepend(
                '<span class="spinner-border spinner-border-sm mg-r-5 d-none" role="status" aria-hidden="true"></span> '
            );
        }
        btn.children('.spinner-border').removeClass('d-none');
    });

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

    // delete confirmation
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

    // generic processing (name multilingual)
    $('.name-lang-modal').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }

        let button = $(e.relatedTarget);
        var parent = button.data('parent');
        if (!parent) {
            return;
        }

        $(this).on('hide.bs.modal', function (e) {
            if (parent) {
                let defaultName = $(this).find('.text-primary').closest('tr').find('.name-input').val();
                if (!defaultName) {
                    defaultName = $(this).find('.name-input').filter(function () {
                        return $(this).val() != '';
                    }).first().val();
                }

                $(parent).find('.name-button').text(defaultName);

                $(parent).data('is_back', true);
                $(parent).modal('show');
            }
        });
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
                    defaultDesc = $(this).find('.desc-input').filter(function () {
                        return $(this).val() != '';
                    }).first().val();
                }

                $(parent).find('.desc-button').text(defaultDesc);
                $(parent).data('is_back', true);
                $this.parent('form').find('input[name=update_description]').val(1);
                $(parent).modal('show');
            }
        });
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

    // panel config
    $('#panelConfig .update-panel-url').change(function () {
        let systemUrl = $('#panelConfig').find('input[name=system_url]').val();
        let panelPath = $('#panelConfig').find('input[name=panel_path]').val();

        $('#panelConfig').find('#panelUrl').text(systemUrl + '/fresns/' + panelPath);
    });

    // languages
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

    // update-order
    $(document).on('click', 'input.update-order', function () {
        return false;
    });

    $(document).on('change', 'input.update-order', function () {
        $.ajax({
            method: 'post',
            url: $(this).data('action'),
            data: {
                order: $(this).val(),
                _method: 'patch',
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
        $(this).find('input:radio[name=is_enabled][value="' + isEnabled + '"]').prop('checked', true).click();
    });

    // update policy
    $('#updatePolicy').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            title = button.data('title'),
            langLabel = button.data('lang_label'),
            langContent = button.data('lang_content'),
            action = button.data('action');

        $(this).find('form').attr('action', action);
        $(this).find('.lang-label').text(langLabel);
        $(this).find('.modal-title').text(title);
        $(this).find('textarea[name=langContent]').val(langContent);
    });

    $('#updatePolicyForm').submit(function () {
        $('#updatePolicy').modal('hide');
        let langContent = $(this).find('textarea[name=langContent]').val();

        $.ajax({
            method: 'post',
            url: $(this).attr('action'),
            data: {
                langContent: langContent,
                _method: 'patch',
            },
            success: function (response) {
                window.tips(response.message);

                if ($('.btn-primary[data-url]').data('url')) {
                    window.location.href = $('.btn-primary[data-url]').data('url');
                    window.location.reload();
                }
            },
        });
        return false;
    });

    $('#emailModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let languages = button.data('languages');
        let isEnabled = button.data('enable');
        let action = button.data('action');
        let title = button.data('title');

        $(this).find('form').trigger('reset');
        $(this).find('input:radio[name=is_enabled][value="' + isEnabled + '"]').prop('checked', true);
        $(this).find('form').attr('action', action);
        $(this).find('.modal-title').text(title);

        if (languages) {
            languages.map((language) => {
                $(this).find("input[name='titles[" + language.langTag + "]'").val(language.title);
                $(this).find("textarea[name='contents[" + language.langTag + "]'").val(language.content);
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
        $(this).find('input:radio[name=is_enabled][value="' + isEnabled + '"]').prop('checked', true);
        $(this).find('form').attr('action', action);
        $(this).find('.modal-title').text(title);

        if (languages) {
            languages.map((language) => {
                $(this).find("input[name='sign_names[" + language.langTag + "]'").val(language.signName);
                $(this).find("input[name='template_codes[" + language.langTag + "]'").val(language.templateCode);
                $(this).find("input[name='code_params[" + language.langTag + "]'").val(language.codeParam);
            });
        }
    });

    // account connect
    $('#addConnect').click(function () {
        let template = $('#connectTemplate');
        $('.connect-box').append(template.html());
    });

    // account config (accountConfigForm)
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
    $('#accountConfigForm').submit(function () {
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

    // delete connect
    $(document).on('click', '.delete-connect', function () {
        $(this).parent().remove();
    });

    // configLangModal
    $('#configLangModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            title = button.data('title'),
            description = button.data('description'),
            action = button.data('action'),
            languages = button.data('languages');

        $('.lang-modal-title').text(trans('panel.button_setting')); //FsLang
        $('.lang-modal-description').addClass('d-none');
        let form = $(this).find('form');
            form.attr('action', action);
            form.trigger('reset');

        if (title) {
            $('.lang-modal-title').text(title);
        }

        if (description) {
            $('.lang-modal-description').removeClass('d-none');
            $('.lang-modal-description-text').text(description);
        }

        if (languages) {
            Object.entries(languages).forEach(([langTag, langContent]) => {
                form.find("input[name='languages[" + langTag + "]']").val(langContent);
            });
        }
    });

    // configStatusModal
    $('#configStatusModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            title = button.data('title'),
            action = button.data('action'),
            status = button.data('status') || '0';

        $('.lang-modal-title').text(trans('panel.button_setting')); //FsLang
        let form = $(this).find('form');
            form.attr('action', action);
            form.trigger('reset');

        if (title) {
            $('.lang-modal-title').text(title);
        }

        $(this).find('input:radio[name=itemValue][value="' + status + '"]').prop('checked', true).click();
    });

    // configStateModal
    $('#configStateModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            title = button.data('title'),
            action = button.data('action'),
            state = button.data('state');

        $('.lang-modal-title').text(trans('panel.button_setting')); //FsLang
        let form = $(this).find('form');
            form.attr('action', action);
            form.trigger('reset');

        if (title) {
            $('.lang-modal-title').text(title);
        }

        if (state) {
            $(this).find('input:radio[name=itemValue][value="' + state + '"]').prop('checked', true);
        }
    });

    // configPublicStatusModal
    $('#configPublicStatusModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            title = button.data('title'),
            action = button.data('action'),
            status = button.data('status') || '0';

        $('.lang-modal-title').text(trans('panel.button_setting')); //FsLang
        let form = $(this).find('form');
            form.attr('action', action);
            form.trigger('reset');

        if (title) {
            $('.lang-modal-title').text(title);
        }

        $(this).find('input:radio[name=itemValue][value="' + status + '"]').prop('checked', true).click();
    });

    // configSeoModal
    $('#configSeoModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            title = button.data('title'),
            action = button.data('action'),
            languages = button.data('languages');

        $('.lang-modal-title').text(trans('panel.button_setting')); //FsLang
        let form = $(this).find('form');
            form.attr('action', action);
            form.trigger('reset');

        if (title) {
            $('.lang-modal-title').text(title);
        }

        if (languages) {
            Object.entries(languages).forEach(([langTag, langContent]) => {
                form.find("input[name='languages[" + langTag + "][title]']").val(langContent.title);
                form.find("textarea[name='languages[" + langTag + "][description]']").val(langContent.description);
                form.find("textarea[name='languages[" + langTag + "][keywords]']").val(langContent.keywords);
            });
        }
    });

    // configListModal
    $('#configListModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            title = button.data('title'),
            action = button.data('action'),
            indexType = button.data('indexType'),
            queryState = button.data('queryState'),
            queryConfig = button.data('queryConfig');

        $('.lang-modal-title').text(trans('panel.button_setting')); //FsLang
        $('.lang-modal-description').addClass('d-none');
        $('.index-type').hide();
        let form = $(this).find('form');
            form.attr('action', action);
            form.trigger('reset');

        if (title) {
            $('.lang-modal-title').text(title);
        }

        if (indexType) {
            $('.index-type').show();
            form.find('input:radio[name=index_type][value="' + indexType + '"]').prop('checked', true);
        }

        if (queryState) {
            form.find('input:radio[name=query_state][value="' + queryState + '"]').prop('checked', true);
        }

        if (queryConfig) {
            form.find("textarea[name='query_config']").val(queryConfig);
        }
    });

    // configDefaultListModal
    $('#configDefaultListModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            action = button.data('action'),
            itemValue = button.data('itemValue');

        let form = $(this).find('form');
            form.attr('action', action);
            form.trigger('reset');

        if (itemValue) {
            form.find('input:radio[name=itemValue][value="' + itemValue + '"]').prop('checked', true);
        }
    });

    // user roles
    $('#emptyColor').change(function () {
        if ($(this).is(':checked')) {
            $('.choose-color').hide();
        } else {
            $('.choose-color').show();
        }
    });

    $('#editRoleModal').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }

        let button = $(e.relatedTarget);
        let action = button.data('action');
        let params = button.data('params');
        let defaultName = button.data('defaultName');

        $('.choose-color').hide();

        //reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();

        $(this).parent('form').attr('action', action);
        $(this).parent('form').find('input[name=_method]').val(params ? 'put' : 'post');
        $(this).parent('form').trigger('reset');

        if (!params) {
            $(this).find('.name-button').text(trans('panel.table_name')); //FsLang

            return;
        }

        $(this).find('input[name=sort_order]').val(params.sort_order);
        $(this).find('.name-button').text(defaultName);
        Object.entries(params.name).forEach(([langTag, langContent]) => {
            $(this).parent('form').find("input[name='names[" + langTag + "]']").val(langContent);
        });
        if (params.is_display_name) {
            $(this).find('input[name=is_display_name]').prop('checked', true);
        }
        if (params.is_display_icon) {
            $(this).find('input[name=is_display_icon]').prop('checked', true);
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
        $(this).find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]').prop('checked', true).click();
    });

    $('#editRoleModal').on('hide.bs.modal', function (e) {
        $(this).data('is_back', false);
    });

    $('#deleteRoleModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let action = button.data('action');
        let defaultName = button.data('defaultName');
        let params = button.data('params');

        $(this).find('form').attr('action', action);

        $(this).find('input[name=name]').val(defaultName);
        $(this).find('#chooseRole').children('.role-option').prop('disabled', false);
        $(this).find('#chooseRole').find('option[value=' + params.id + ']').prop('disabled', true);
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

    // sticker
    $('#editStickerGroupModal').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }

        let button = $(e.relatedTarget);
        let action = button.data('action');
        let defaultName = button.data('defaultName');
        let params = button.data('params');

        let form = $('#editStickerGroupForm');

        form.attr('action', action);
        form.find('input[name=_method]').val(params ? 'put' : 'post');
        form.trigger('reset');

        //reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();

        if (!params) {
            form.find('.name-button').text(trans('panel.sticker_table_group_name')); //FsLang

            return;
        }

        if (params.image_file_url) {
            form.find('input[name=image_file_url]').val(params.image_file_url);
            form.find('input[name=image_file_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        } else {
            form.find('input[name=image_file_url]').val('');
        }

        form.find('input[name=sort_order]').val(params.sort_order);
        form.find('.name-button').text(defaultName);
        Object.entries(params.name).forEach(([langTag, langContent]) => {
            form.find("input[name='names[" + langTag + "]']").val(langContent);
        });
        form.find('input[name=code]').val(params.code);
        form.find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]').prop('checked', true).click();
    });

    $('#editStickerGroupModal').on('hide.bs.modal', function (e) {
        $(this).data('is_back', false);
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

            stickerTemplate.find('input.sticker-order').attr('name', 'sort_order[' + sticker.id + ']').val(sticker.sort_order);

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

    // group edit
    $('#groupModal').on('shown.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }
        let button = $(e.relatedTarget);
        let action = button.data('action');
        let params = button.data('params');
        let parentGroupName = button.data('parentGroupName');
        let defaultName = button.data('defaultName');
        let defaultDescription = button.data('defaultDescription');
        let coverUrl = button.data('cover-url');
        let bannerUrl = button.data('banner-url');
        let adminUsers = button.data('adminUsers');

        let form = $(this).parents('form');
        let selectAdmin = form.find('select[name="admin_ids[]"]');

        form.attr('action', action);
        form.find('input[name=_method]').val(params ? 'put' : 'post');

        // reset default
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();
        $('#cover_file_view').hide();
        $('#banner_file_view').hide();
        $('.publish_setting').show();

        form.find('.parent-group-button').text(trans('panel.option_unselect')); //FsLang
        form.find('.name-button').text(trans('panel.table_name')); //FsLang
        form.find('.desc-button').text(trans('panel.table_description')); //FsLang

        selectAdmin.find('option').remove();
        form.find('input[name=parent_id]').val(0);
        form.trigger('reset');

        if (!params) {
            return;
        }

        let permissions = params.permissions;
        let privateWhitelistRoles = permissions.private_whitelist_roles;
        let canPublish = permissions.can_publish ? '1' : '0';
        let publishPost = permissions.publish_post ? permissions.publish_post : 0;
        let publishPostRoles = permissions.publish_post_roles;
        let publishPostReview = permissions.publish_post_review ? 1 : 0;
        let publishComment = permissions.publish_comment ? permissions.publish_comment : 0;
        let publishCommentRoles = permissions.publish_comment_roles;
        let publishCommentReview = permissions.publish_comment_review ? 1 : 0;

        form.find('input[name=parent_id]').val(params.parent_id);
        if (parentGroupName) {
            form.find('.parent-group-button').text(parentGroupName);
        }
        form.find('input[name=sort_order]').val(params.sort_order);

        form.find('.name-button').text(defaultName);
        Object.entries(params.name).forEach(([langTag, langContent]) => {
            form.find("input[name='names[" + langTag + "]']").val(langContent);
        });

        form.find('.desc-button').text(defaultDescription);
        Object.entries(params.description).forEach(([langTag, langContent]) => {
            form.find("textarea[name='descriptions[" + langTag + "]']").val(langContent);
        });

        if (coverUrl) {
            $('#cover_file_view').show().attr('href', coverUrl);
        }

        if (bannerUrl) {
            $('#banner_file_view').show().attr('href', bannerUrl);
        }

        if (params.cover_file_url) {
            form.find('input[name=cover_file_url]').val(params.cover_file_url);
            form.find('input[name=cover_file_url]').removeAttr('style');
            $('#showIcon').text(trans('panel.button_image_input')); //FsLang
            form.find('input[name=cover_file]').css('display', 'none');
        } else {
            form.find('input[name=cover_file_url]').val('');
        }

        if (params.banner_file_url) {
            form.find('input[name=banner_file_url]').val(params.banner_file_url);
            form.find('input[name=banner_file_url]').removeAttr('style');
            $('#showBanner').text(trans('panel.button_image_input')); //FsLang
            form.find('input[name=banner_file]').css('display', 'none');
        } else {
            form.find('input[name=banner_file_url]').val('');
        }

        form.find('input:radio[name=privacy][value="' + params.privacy + '"]').prop('checked', true).click();
        form.find('input:radio[name=visibility][value="' + params.visibility + '"]').prop('checked', true).click();
        form.find('select[name="permissions[private_whitelist_roles][]"]').val(privateWhitelistRoles).change();
        form.find('input:radio[name=follow_type][value="' + params.follow_type + '"]').prop('checked', true).click();
        form.find('select[name=follow_app_fskey]').val(params.follow_app_fskey);
        form.find('input:radio[name=is_recommend][value="' + params.is_recommend + '"]').prop('checked', true).click();

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

        form.find('input:radio[name="permissions[can_publish]"][value="' + canPublish + '"]').prop('checked', true).click();

        form.find('input:radio[name="permissions[publish_post]"][value="' + publishPost + '"]').prop('checked', true).click();
        form.find('select[name="permissions[publish_post_roles][]"]').val(publishPostRoles).change();
        form.find('input:radio[name="permissions[publish_post_review]"][value="' + publishPostReview + '"]').prop('checked', true).click();

        form.find('input:radio[name="permissions[publish_comment]"][value="' + publishComment + '"]').prop('checked', true).click();
        form.find('select[name="permissions[publish_comment_roles][]"]').val(publishCommentRoles).change();
        form.find('input:radio[name="permissions[publish_comment_review]"][value="' + publishCommentReview + '"]').prop('checked', true).click();
    });

    $('#groupModal').on('hide.bs.modal', function (e) {
        $(this).data('is_back', false);
    });

    $('#parentGroupModal').on('show.bs.modal', function (e) {
        $('#parentGroups').empty();
        $('#firstGroups').val('');
        $('input[name="choose_group_id"]').val('');

        let button = $(e.relatedTarget);
        var parent = button.data('parent');

        if (!parent) {
            return;
        }

        $(this).on('hide.bs.modal', function (e) {
            if (parent) {
                $(parent).data('is_back', true);
                $(parent).modal('show');
            }
        });
    });

    $('#parentGroupModal .choose-group').change(function() {
        var subgroupDiv = $('#parentGroups');
        var unselect = trans('panel.option_unselect'); // FsLang
        subgroupDiv.empty();
        fetchAndAppendSubgroups($(this), subgroupDiv, 0, unselect);
    });

    // choose group confirm
    $('#chooseGroupConfirm').click(function () {
        var chooseGroupId = $('input[name="choose_group_id"]').val();
        var chooseGroupName = $('input[name="choose_group_name"]').val();

        $('input[name="parent_id"]').val(chooseGroupId);
        $('.parent-group-button').text(chooseGroupName);

        $('input[name="group_id"]').val(chooseGroupId);
        $('.group-button').text(chooseGroupName);
    });

    // search users
    $('.group-user-select2').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '/fresns/search/users',
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
    $('.select2').closest('form').on('reset', function (ev) {
        var targetJQForm = $(ev.target);
        setTimeout(
            function () {
                this.find('.select2').trigger('change');
            }.bind(targetJQForm),
            0
        );
    });

    // group move
    $('#moveModal').on('shown.bs.modal', function (e) {
        let button = $(e.relatedTarget);
        let action = button.data('action');
        let defaultName = button.data('defaultName');
        let groupId = button.data('groupId');

        $(this).data('group_id', groupId);

        $(this).find('form').attr('action', action);
        $(this).find('form').trigger('reset');

        $(this).find('input[name=current_group]').val(defaultName);
    });

    $('#moveModal').on('show.bs.modal', function () {
        $('#subgroup').empty();
        $('input[name="group_id"]').val('');
    });

    $('#moveModal .choose-group').change(function() {
        var subgroupDiv = $('#subgroup');
        var unselect = trans('panel.option_unselect'); // FsLang
        subgroupDiv.empty();
        fetchAndAppendSubgroups($(this), subgroupDiv, 0, unselect);
    });

    // parent group select
    function fetchAndAppendSubgroups(selectElement, targetDiv, parentGroupId, parentGroupName) {
        var groupId = selectElement.val();
        var groupName = selectElement.find('option:selected').text();

        if (!groupId) {
            $('#subgroup-' + parentGroupId + ' > div').remove();

            $('input[name="choose_group_id"]').val(parentGroupId); // parentGroup and extendGroup
            $('input[name="choose_group_name"]').val(parentGroupName); // parentGroup and extendGroup
            $('input[name="group_id"]').val(parentGroupId); // moveGroup
            $('input[name="groupId"]').val(parentGroupId); // filterGroup

            return;
        }

        $('input[name="choose_group_id"]').val(groupId);
        $('input[name="choose_group_name"]').val(groupName);
        $('input[name="group_id"]').val(groupId);
        $('input[name="groupId"]').val(groupId);

        $.ajax({
            method: 'get',
            url: '/fresns/search/groups',
            data: {
                groupId: groupId,
            },
            success: function (response) {
                if (response.length == 0) {
                    return;
                }

                var subgroupDivId = 'subgroup-' + groupId;
                var newSubgroupDiv = $('<div id="' + subgroupDivId + '"></div>');
                targetDiv.append(newSubgroupDiv);

                var newSelect = $('<select class="form-select choose-group mb-3"></select>');
                newSelect.append('<option selected disabled value="">' + trans('tips.select_box_tip_group') + '</option>'); // FsLang
                newSelect.append('<option value="">' + trans('panel.option_unselect') + '</option>'); // FsLang
                response.forEach(function (group) {
                    newSelect.append('<option value="' + group.id + '">' + group.name + '</option>');
                });
                newSubgroupDiv.append(newSelect);

                newSelect.change(function() {
                    fetchAndAppendSubgroups($(this), newSubgroupDiv, groupId, groupName);
                    $(this).nextAll().remove();
                });
            },
        });
    }

    // app-usages
    $('.app-usage-modal').on('show.bs.modal', function (e) {
        if ($(this).data('is_back')) {
            return;
        }

        let button = $(e.relatedTarget);
        let params = button.data('params');
        let groupName = button.data('group-name');
        let defaultName = button.data('default-name');
        let action = button.data('action');

        let form = $(this).parents('form');

        //reset default
        form.find('.group-button').text(trans('tips.select_box_tip_group')); //FsLang
        form.find('.name-button').text(trans('panel.table_name')); //FsLang
        $('.showSelectTypeName').text(trans('panel.button_image_upload')); //FsLang
        $('.inputUrl').hide();
        $('.inputFile').show();

        form.attr('action', action);
        form.find('input[name=_method]').val(params ? 'put' : 'post');
        form.find('input[name=group_id]').val(0);
        form.trigger('reset');

        if (!params) {
            return;
        }

        if (params.icon_file_url) {
            form.find('input[name=icon_file_url]').val(params.icon_file_url);
            form.find('input[name=icon_file_url]').removeAttr('style');
            $('.showSelectTypeName').text(trans('panel.button_image_input')); //FsLang
            $('.inputFile').css('display', 'none');
        } else {
            form.find('input[name=icon_file_url]').val('');
        }

        if (defaultName) {
            form.find('.name-button').text(defaultName);
        }

        form.find('.group-button').text(groupName);
        form.find('input[name=group_id]').val(params.group_id);
        form.find('input[name=sort_order]').val(params.sort_order);
        form.find('select[name=app_fskey]').val(params.app_fskey);
        form.find('input[name=parameter]').val(params.parameter);
        form.find('input[name=editor_number]').val(params.editor_number);
        form.find('input:radio[name=editor_toolbar][value="' + params.editor_toolbar + '"]').prop('checked', true).click();
        form.find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]').prop('checked', true).click();

        Object.entries(params.name).forEach(([langTag, langContent]) => {
            form.find("input[name='names[" + langTag + "]']").val(langContent);
        });

        $('#scenePost').removeAttr('checked');
        $('#sceneComment').removeAttr('checked');
        $('#sceneUser').removeAttr('checked');
        if (params.scene) {
            scene = params.scene.split(',');
            for (var i = 0; i < scene.length; i++) {
                if (scene[i] == 1) {
                    $('#scenePost').attr('checked', 'checked');
                }
                if (scene[i] == 2) {
                    $('#sceneComment').attr('checked', 'checked');
                }
                if (scene[i] == 3) {
                    $('#sceneUser').attr('checked', 'checked');
                }
            }
        }

        if (params.roles) {
            form.find('select[name="roles[]"]').val(params.roles.split(',')).change();
        }

        form.find('input:radio[name=is_group_admin][value="' + params.is_group_admin + '"]').prop('checked', true).click();
        if (params.is_group_admin) {
            $('#usage_setting').removeClass('show');
        } else {
            $('#usage_setting').addClass('show');
        }
    });

    $('.app-usage-modal').on('hide.bs.modal', function (e) {
        $(this).data('is_back', false);
    });

    $('#extendGroupModal').on('show.bs.modal', function (e) {
        $('#groups').empty();
        $('#firstGroups').val('');
        $('input[name="choose_group_id"]').val('');

        let button = $(e.relatedTarget);
        var parent = button.data('parent');

        if (!parent) {
            return;
        }

        $(this).on('hide.bs.modal', function (e) {
            if (parent) {
                $(parent).data('is_back', true);
                $(parent).modal('show');
            }
        });
    });

    $('#extendGroupModal .choose-group').change(function() {
        var subgroupDiv = $('#groups');
        var unselect = trans('panel.option_unselect'); // FsLang
        subgroupDiv.empty();
        fetchAndAppendSubgroups($(this), subgroupDiv, 0, unselect);
    });

    $('#filterModal .choose-group').change(function() {
        var subgroupDiv = $('#subgroup');
        var unselect = trans('panel.option_unselect'); // FsLang
        subgroupDiv.empty();
        fetchAndAppendSubgroups($(this), subgroupDiv, 0, unselect);
    });

    // language pack
    $('#editLanguagePacks').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            action = button.data('action'),
            params = button.data('params');

        let form = $(this).find('form');
            form.find('.modal-title').text(trans('panel.button_add')); //FsLang
            form.attr('action', action);
            form.find('input[name=_method]').val(params ? 'put' : 'post');
            form.find('input[name=langKey]').val('').prop('disabled', false).prop('readonly', false);
            form.trigger('reset');

        if (!params) {
            return;
        }

        form.find('.modal-title').text(trans('panel.button_edit')); //FsLang
        form.find('input[name=langKey]').val(params.lang_key).prop('disabled', true).prop('readonly', true);

        if (params.lang_values) {
            Object.entries(params.lang_values).forEach(([langTag, langContent]) => {
                form.find("textarea[name='langValues[" + langTag + "]']").val(langContent);
            });
        }
    });

    // language pack - delete
    $('#deleteLangKey').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            action = button.data('action'),
            key = button.data('key'),
            value = button.data('value');

        let form = $(this).find('form');
            form.attr('action', action);
            form.find('.modal-title>span').text(key);
            form.find('.modal-body').text(value);
    });

    // code messages
    $('#editCodeMessages').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            appName = button.data('appName'),
            action = button.data('action'),
            params = button.data('params');

        let form = $(this).find('form');
            form.attr('action', action);
            form.trigger('reset');

        $('.message-app-name').text(appName);
        $('.message-app-fskey').text(params.app_fskey);
        $('.message-code').text(params.code);

        if (params.messages) {
            Object.entries(params.messages).forEach(([langTag, langContent]) => {
                form.find("textarea[name='messages[" + langTag + "]']").val(langContent);
            });
        }
    });

    // code messages - delete
    $('#deleteCodeMessages').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            appName = button.data('appName'),
            codeMessage = button.data('codeMessage'),
            action = button.data('action'),
            params = button.data('params');

        let form = $(this).find('form');
            form.attr('action', action);

        form.find('.message-app-name').text(appName);
        form.find('.message-app-fskey').text(params.app_fskey);
        form.find('.message-code').text(params.code);
        form.find('.modal-body').text(codeMessage);
    });


    // app key
    $('#appKeyModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            action = button.data('action'),
            params = button.data('params');

        let form = $(this).find('form');

        form.attr('action', action);
        form.find('input[name=_method]').val(params ? 'put' : 'post');

        // reset default
        form.trigger('reset');

        if (!params) {
            return;
        }

        $('.key-modal-title').text(trans('panel.button_setting')); //FsLang

        form.find('select[name=platform_id]').val(params.platform_id);
        form.find('input[name=name]').val(params.name);
        form.find('input:radio[name=type][value="' + params.type + '"]').prop('checked', true).click();
        form.find('input:radio[name=is_read_only][value="' + params.is_read_only + '"]').prop('checked', true).click();
        form.find('input:radio[name=is_enabled][value="' + params.is_enabled + '"]').prop('checked', true).click();

        if (params.type == 3) {
            form.find('#app_key_plugin').prop('required', true);
            form.find('select[name=app_fskey]').val(params.app_fskey);
        } else {
            form.find('#app_key_plugin').prop('required', false);
        }
    });

    // app key - reset and delete
    $('#resetKey,#deleteKey').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            name = button.data('name'),
            appId = button.data('appId'),
            action = button.data('action');

        $(this).find('form').attr('action', action);
        $(this).find('.modal-title').text(name);
        $(this).find('.app-id').text(appId);
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
            window.url = button.data('action');

        $(this).find('.modal-title').text( window.pluginName );
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
        $('input[name=app_fskey]').removeClass('is-invalid')
        $('input[name=app_zipball]').removeClass('is-invalid')
    });

    $('.install_method').click(function (e) {
        let install_method = $(this).parent().data('name')
        $('input[name=install_method]').val(install_method)
    });

    $('#installSubmit').click(function (e) {
        e.preventDefault();

        let install_method = $('input[name=install_method]').val()
        let app_fskey = $('input[name=app_fskey]').val()
        let app_directory = $('input[name=app_directory]').val()
        let app_zipball = $('input[name=app_zipball]').val()

        if (app_fskey || app_zipball || app_directory) {
            $(this).submit()
            $('#installStepModal').modal('toggle')
            return;
        }

        if (install_method == 'inputFskey' && !app_fskey) {
            $('input[name=app_fskey]').addClass('is-invalid')
            $('#inputFskeyOrInputFile').text(trans('tips.install_not_entered_key')).show() // FsLang
            return;
        }

        if (install_method == 'inputDirectory' && !app_zipball) {
            $('input[name=app_directory]').addClass('is-invalid')
            $('#inputFskeyOrInputFile').text(trans('tips.install_not_entered_directory')).show() // FsLang
            return;
        }

        if (install_method == 'inputZipball' && !app_zipball) {
            $('input[name=app_zipball]').addClass('is-invalid')
            $('#inputFskeyOrInputFile').text(trans('tips.install_not_upload_zip')).show() // FsLang
            return;
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
        $('input[name=fskey]').val(fskey)
    });

    $('#deleteApp,#deleteTheme').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget),
            action = button.data('action'),
            name = button.data('name'),
            fskey = button.data('fskey');

        let form = $(this).find('form');

        form.attr('action', action);

        // reset default
        form.trigger('reset');

        $('.app-name').text(name);

        form.find('input[name=fskey]').val(fskey);
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
                $('input[name="app_fskey"]').val(fresnsCallback.data.fskey)

                $('input[name="app_fskey"]').css('display', 'block').siblings('input').css('display', 'none')
                $('input[name="app_fskey"]').attr('required', true).siblings('input').attr('required', false)

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
