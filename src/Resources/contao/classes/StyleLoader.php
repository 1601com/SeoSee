<?php

namespace agentur1601com\seosee;

use Contao\Combiner;

class StyleLoader
{
    /**
     * @param array $foundedFiles
     * @param string $savedFiles
     * @return string
     */
    public function returnMultiColumnWizardArray(array $foundedFiles, string $savedFiles)
    {
        $returnArray = [];

        $savedFiles = unserialize($savedFiles);

        foreach ($savedFiles as $savedFile)
        {
            if(($key = array_search($savedFile['style_files_path'], $foundedFiles)) !== false)
            {
                $returnArray[] = [
                    'style_files_path' => $savedFile['style_files_path'],
                    'style_param' => $savedFile['style_param']
                ];
                unset($foundedFiles[$key]);
            }
        }

        foreach (array_reverse($foundedFiles) as $foundedFile)
        {
            $returnArray[] = ['style_files_path' => $foundedFile, 'style_param'=>''];
        }

        return serialize($returnArray);
    }

    /**
     * @param $objPage
     * @param $objLayout
     * @param $objPageRegular
     */
    public function loadStyles($objPage, $objLayout, $objPageRegular)
    {
        if($objLayout->seoseeStyleFilesLoad && is_array(($styleFiles = unserialize($objLayout->seoseeStyleFilesLoad))))
        {
            foreach ($styleFiles as $styleFile)
            {
                $combinerObj = new Combiner();

                if(!file_exists($styleFile['style_files_path']))
                {
                    continue;
                }

                $combinerObj->add($styleFile['style_files_path']);

                switch ($styleFile['style_param'])
                {
                    case "head":
                        $GLOBALS['TL_HEAD'][] = "<link rel='stylesheet' href='/{$combinerObj->getFileUrls()[0]}'>";
                        break;
                    case "footer":
                        $GLOBALS['TL_BODY'][] = "<link rel='stylesheet' href='/{$combinerObj->getFileUrls()[0]}'>";
                        break;
                    case "preload":
                        $GLOBALS['TL_HEAD'][] = "<link rel='preload' href='/{$combinerObj->getFileUrls()[0]}' as='style'>";
                        $GLOBALS['TL_HEAD'][] = "<link rel='stylesheet' href='/{$combinerObj->getFileUrls()[0]}'>";
                        break;
                    case "preload_push":
                        header("Link: </" . $combinerObj->getFileUrls()[0] . ">; rel=preload; as=style",false);
                        $GLOBALS['TL_HEAD'][] = "<link rel='stylesheet' href='/{$combinerObj->getFileUrls()[0]}'>";
                        break;
                    case "delay":
                        $GLOBALS['TL_HEAD'][] = "
<noscript id='deferred-styles'>
    <link rel='stylesheet' type='text/css' href='{$combinerObj->getFileUrls()[0]}'/>
</noscript>
<script>
    var loadDeferredStyle = function() {
      var addStylesNode = document.getElementById('deferred-styles');
      var replacement = document.createElement('div');
        replacement.innerHTML = addStylesNode.textContent;
        document.body.appendChild(replacement);
        addStylesNode.parentElement.removeChild(addStylesNode);
    }
    var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
          window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
      if (raf) raf(function() { window.setTimeout(loadDeferredStyle, 0); });
      else window.addEventListener('load', loadDeferredStyle);    
</script>";
                }
            }
        }
    }

    /**
     * @param $file
     * @ToDo implement method
     */
    public function checkModified($file)
    {

    }
}
