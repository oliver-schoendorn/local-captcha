
if ( ! window.LocalCaptcha) {
    window.LocalCaptcha = (function() {
        var _documentReady = document.readyState === 'complete'
        var _onLoadCallback = function() {};

        if ( ! _documentReady) {
            if (document.addEventListener) {
                document.addEventListener("DOMContentLoaded", function() {
                    document.removeEventListener('DOMContentLoaded', arguments.callee, false);
                    _documentReady = true
                    _onLoadCallback()
                })
            }
            else if(document.attachEvent) {
                document.attachEvent("onreadystatechange", function(){
                    if (document.readyState === "complete") {
                        document.detachEvent("onreadystatechange", arguments.callee);
                        _documentReady = true
                        _onLoadCallback();
                    }
                });
            }
        }

        return {
            onLoad: function (callback) {
                if (_documentReady) {
                    callback()
                    return
                }

                var _prevOnLoadCallback = _onLoadCallback
                _onLoadCallback = function () {
                    callback()
                    _prevOnLoadCallback()
                }
            }
        }
    })();
}
