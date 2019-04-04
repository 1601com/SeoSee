document.addEventListener("DOMContentLoaded",function () {
    document.getElementsByClassName("seoseeJsPath")[0].addEventListener("DOMNodeInserted",function (event) {
        console.log("TEST");
        setTimeout(function () {
            document.getElementById("save").click();
        },500);
    });
});

/*(function(){
    document.addEventListener('DOMContentLoaded', function () {
        reset();
    });

    function reset() {
        const TableRowObjs = document.getElementById("ctrl_seoseeJsFiles").getElementsByTagName('tr');

        for (var i=0; i<TableRowObjs.length; i++) {
            const TableRowObj = TableRowObjs[i];
            const OpsObjs = TableRowObj.getElementsByClassName('operations');

            if (OpsObjs.length === 0) {
                continue;
            }

            var CopyObj = OpsObjs[0].querySelector('[data-operations="copy"]');

            if (CopyObj === null) {
                continue;
            }

            if (i !== TableRowObjs.length-1) {
                CopyObj.parentNode.removeChild(CopyObj);
            }
            else {
                setTimeout(function () {
                    TableRowObj.getElementById('ctrl_seoseeJsFiles_row' + (TableRowObjs.length-2) + '_js_files_path').readOnly = false;
                },100);
                CopyObj.onclick = function () {
                    setTimeout(function () {
                        reset();
                    },100);
                }
            }
        }
    }
})();*/


