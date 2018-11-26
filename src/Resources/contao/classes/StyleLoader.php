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
            if(($key = array_search($savedFile['style_files_path'],$foundedFiles)) !== false)
            {
                $returnArray[] = ['style_files_path' => $savedFile['style_files_path'] , 'style_param'=>$savedFile['style_param']];
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
        //var_dump($objLayout->seoseeStyleFilesLoad);
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
                        $GLOBALS['TL_HEAD'][] = "<link rel='stylesheet' href='{$combinerObj->getCombinedFile()}'>";
                        break;
                    case "footer":
                        $GLOBALS['TL_BODY'][] = "<link rel='stylesheet' href='{$combinerObj->getCombinedFile()}'>";
                        break;
                    case "preload":
                        $GLOBALS['TL_HEAD'][] = "<link rel='preload' href='{$combinerObj->getCombinedFile()}' as='style'>";
                        $GLOBALS['TL_HEAD'][] = "<link rel='stylesheet' href='{$combinerObj->getCombinedFile()}'>";
                        break;
                    case "preload_push":
                        header("Link: <" . $combinerObj->getCombinedFile() . ">; rel=preload; as=style",false);
                        $GLOBALS['TL_HEAD'][] = "<link rel='stylesheet' href='{$combinerObj->getCombinedFile()}'>";
                        break;
                }
            }
        }
    }
}