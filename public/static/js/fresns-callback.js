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

        return successResponse;
    },

    send: function(callbackAction, apiData = null, apiCode = 0, apiMessage = 'ok') {
        setTimeout(function () {
            const messageString = FresnsCallback.encode(callbackAction, apiData, apiCode, apiMessage);

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
