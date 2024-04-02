/*!
 * Fresns (https://fresns.org)
 * Copyright 2021-Present Jevan Tang
 * Licensed under the Apache-2.0 license
 */

var FresnsCallback = {
    encode: function(action, data = null, code = 0, message = 'ok') {
        const messageArr = {
            code: code,
            message: message,
            data: data,
            action: action,
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
        if (!stringify) {
            return;
        }

        let callbackData;

        try {
            callbackData = JSON.parse(stringify);
        } catch (error) {
            console.log('fresns callback data error', error);
            return;
        }

        if (!callbackData) {
            return;
        }

        const fresnsCallbackData = {
            code: callbackData.code || 24000,
            message: callbackData.message || 'Callback data format error',
            data: callbackData.data || null,
            action: {
                postMessageKey: callbackData.action?.postMessageKey || '',
                windowClose: callbackData.action?.windowClose || true,
                redirectUrl: callbackData.action?.redirectUrl || '',
                dataHandler: callbackData.action?.dataHandler || '',
            },
        }

        return fresnsCallbackData;
    },

    send: function(action, data = null, code = 0, message = 'ok') {
        setTimeout(function () {
            const messageString = FresnsCallback.encode(action, data, code, message);

            const userAgent = navigator.userAgent.toLowerCase();

            switch (true) {
                case (window.Android !== undefined):
                    // Android (addJavascriptInterface)
                    window.Android.receiveMessage(messageString);
                    break;

                case (window.webkit && window.webkit.messageHandlers.iOSHandler !== undefined):
                    // iOS (WKScriptMessageHandler)
                    window.webkit.messageHandlers.iOSHandler.postMessage(messageString);
                    break;

                case (window.FresnsJavascriptChannel !== undefined):
                    // Flutter
                    window.FresnsJavascriptChannel.postMessage(messageString);
                    break;

                case (window.ReactNativeWebView !== undefined):
                    // React Native WebView
                    window.ReactNativeWebView.postMessage(messageString);
                    break;

                case (userAgent.indexOf('miniprogram') > -1):
                    // WeChat Mini Program
                    wx.miniProgram.postMessage({ data: messageString });
                    wx.miniProgram.navigateBack();
                    break;

                // Web
                default:
                    parent.postMessage(messageString, '*');
            }
        }, 2000);
    },
};
