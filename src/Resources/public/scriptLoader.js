document.addEventListener("DOMContentLoaded",function () {
    document.getElementsByClassName("seoseeJsPath")[0].addEventListener("DOMNodeInserted",function (event) {
        setTimeout(function () {
            document.getElementById("save").click();
        },500);
    });
});