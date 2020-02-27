function login(url, result, success, failure) {
    $.ajax({
        url: url,
        data: {
            code: result.code
        },
        success: function(data){
            var urlArr = window.location.href.split("?");
            if(data.code == 0) {
                location.href=data.data.redirect;
                if(urlArr.length > 1) {
                    location.href=data.data.redirect + "?" + urlArr[1];
                }
                success();
            } else {
                failure(data.message);
            }
        },
        error: function(xhr, error) {
            failure(error);
        }
    });
}
function ddLogin(url, corpId, success, error){
    dd.device.notification.showPreloader({
        text: "自动登录中...", //loading显示的字符，空表示不显示文字
        showIcon: true, //是否显示icon，默认true
    });
    dd.runtime.permission.requestAuthCode({
        corpId: corpId,
        onSuccess: function(result) {
            login(url, result, function(){
                dd.device.notification.hidePreloader({});
            }, function(message){
                dd.device.notification.hidePreloader({});
                alert(message);
            });
        },
        onFail : function(err) {
            dd.device.notification.hidePreloader({});
        }
    });
}
function ddPcLogin(corpId, url, success, error){
    DingTalkPC.device.notification.toast({
        type: "information", //toast的类型 alert, success, error, warning, information, confirm
        text: "登录中...", //提示信息
        duration: 3, //显示持续时间，单位秒，最短2秒，最长5秒
        delay: 0, //延迟显示，单位秒，默认0, 最大限制为10
    });
    DingTalkPC.runtime.permission.requestAuthCode({
        corpId: corpId,
        onSuccess: function(result) {
            login(url, result, function(){
            }, function(message){
                alert(message);
            });
        },
        onFail : function(err) {
        }
    });
}

