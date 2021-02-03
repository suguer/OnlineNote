/**
 * 获取get请求的参数值
 * @param name
 * @returns {string|null}
 */
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    // if (r != null) return unescape(r[2]);
    if (r != null) return decodeURI(r[2]);
    return null;
}


/**
 * 判断是否微信浏览器打开的页面
 * @returns {boolean}
 */
function isWeiXin(){
    //window.navigator.userAgent属性包含了浏览器类型、版本、操作系统类型、浏览器引擎类型等信息，这个属性可以用来判断浏览器类型
    var ua = window.navigator.userAgent.toLowerCase();
    //通过正则表达式匹配ua中是否含有MicroMessenger字符串
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}

// 判断安卓
function isAndroid() {
    var u = navigator.userAgent;
    if (u.indexOf("Android") > -1 || u.indexOf("Linux") > -1) {
        if (window.ShowFitness !== undefined) return true;
    }
    return false;
}
// 判断设备为 ios
function isIos() {
    var u = navigator.userAgent;
    if (u.indexOf("iPhone") > -1 || u.indexOf("iOS") > -1) {
        return true;
    }
    return false;
}

/**
 * 超链接a 下载图片的一种方式
 * @param src
 * @param name
 */
downloadImage = (src, name) => {
    const canvas = document.createElement('canvas');
    const img = document.createElement('img');
    img.onload = () => {
        canvas.width = img.width;
        canvas.height = img.height;
        const context = canvas.getContext('2d');
        context.drawImage(img, 0, 0, img.width, img.height);
        // window.navigator.msSaveBlob(canvas.msToBlob(),'image.jpg');
        // saveAs(imageDataUrl, '附件');
        canvas.getContext('2d').drawImage(img, 0, 0, img.width, img.height);
        canvas.toBlob(blob => {
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = name;
            link.click();
        }, 'image/jpeg');
    };
    img.setAttribute('crossOrigin', 'Anonymous');
    img.src = `/api?src=${encodeURI(src)}`;
};