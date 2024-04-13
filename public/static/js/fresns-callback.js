/*!
 * Fresns (https://fresns.org)
 * Copyright 2021-Present Jevan Tang
 * Licensed under the Apache-2.0 license
 */

var FresnsCallback = {
    encode: function(callbackAction, apiData = null, apiCode = 0, apiMessage = 'ok') {
        const messageArr = {
            code: apiCode,
            message: apiMessage,
            data: apiData,
            action: callbackAction,
            // action: {
            //     postMessageKey: '',
            //     windowClose: true,
            //     redirectUrl: '',
            //     dataHandler: '',
            // },
        }

        const messageString = JSON.stringify(messageArr);

        return messageString;
    },

    decode: function(stringify = '') {
        const errorResponse = {
            code: 40000,
            message: 'Callback data format error',
            data: null,
            action: {
                postMessageKey: '',
                windowClose: true,
                redirectUrl: '',
                dataHandler: '',
            },
        }

        if (!stringify) {
            return errorResponse;
        }

        let callbackData;

        try {
            callbackData = JSON.parse(stringify);
        } catch (error) {
            return errorResponse;
        }

        if (!callbackData) {
            return errorResponse;
        }

        const successResponse = {
            code: callbackData.code !== undefined && callbackData.code !== null ? callbackData.code : errorResponse.code,
            message: callbackData.message || errorResponse.message,
            data: callbackData.data || errorResponse.data,
            action: {
                postMessageKey: callbackData.action?.postMessageKey || errorResponse.action.postMessageKey,
                windowClose: callbackData.action?.windowClose || errorResponse.action.windowClose,
                redirectUrl: callbackData.action?.redirectUrl || errorResponse.action.redirectUrl,
                dataHandler: callbackData.action?.dataHandler || errorResponse.action.dataHandler,
            },
        }

        console.log('FresnsCallback', 'Receive', successResponse);

        return successResponse;
    },

    send: function(callbackAction, apiData = null, apiCode = 0, apiMessage = 'ok', timeout = 0) {
        setTimeout(function () {
            const messageString = FresnsCallback.encode(callbackAction, apiData, apiCode, apiMessage);

            console.log('FresnsCallback', 'Send', {
                code: apiCode,
                message: apiMessage,
                action: callbackAction,
                data: apiData,
            });

            const userAgent = navigator.userAgent.toLowerCase();

            switch (true) {
                // iOS (WKScriptMessageHandler)
                case (window.webkit && window.webkit.messageHandlers.iOSHandler !== undefined):
                    window.webkit.messageHandlers.iOSHandler.postMessage(messageString);
                    break;

                // Android (addJavascriptInterface)
                case (window.Android !== undefined):
                    window.Android.receiveMessage(messageString);
                    break;

                // Flutter
                case (window.FresnsJavascriptChannel !== undefined):
                    window.FresnsJavascriptChannel.postMessage(messageString);
                    break;

                // React Native WebView
                case (window.ReactNativeWebView !== undefined):
                    window.ReactNativeWebView.postMessage(messageString);
                    break;

                // WeChat Mini Program
                case (userAgent.indexOf('miniprogram') > -1):
                    loadScript('https://res.wx.qq.com/open/js/jweixin-1.6.0.js', function() {
                        wx.miniProgram.postMessage({ data: messageString });
                        wx.miniProgram.navigateBack();
                    });
                    break;

                // Web Browser
                default:
                    parent.postMessage(messageString, '*');
            }
        }, timeout);
    },
};

function loadScript(url, callback) {
    var script = document.createElement('script');
    script.type = 'text/javascript';

    if (script.readyState) { // IE
        script.onreadystatechange = function() {
            if (script.readyState == 'loaded' || script.readyState == 'complete') {
                script.onreadystatechange = null;
                callback();
            }
        };
    } else { // Other Browsers
        script.onload = function() {
            callback();
        };
    }

    script.src = url;
    document.getElementsByTagName('head')[0].appendChild(script);
}
