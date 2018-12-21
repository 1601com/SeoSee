document.addEventListener("DOMContentLoaded",function () {
    document.getElementsByClassName("seoseeStyleFiles")[0].addEventListener("DOMNodeInserted",function (event) {
        setTimeout(function () {
            document.getElementById("save").click();
        },500);
    });
});