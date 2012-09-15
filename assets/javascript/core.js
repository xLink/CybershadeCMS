
//add the CMS header to prototype's ajax requests, we can tell if its coming from this lib
/*Ajax.Base.prototype.initialize = Ajax.Base.prototype.initialize.wrap(
    function (callOriginal, options) {
        var headers = options.requestHeaders || {};
        headers["X-CMS-IS"] = 'CybershadeCMS';
        options.requestHeaders = headers;
        return callOriginal(options);
    }
);
*/