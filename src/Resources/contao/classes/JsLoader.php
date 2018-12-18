<?php

namespace agentur1601com\seosee;
use agentur1601com\seosee\Helper;
use MatthiasMullie\Minify;
use Contao\Combiner;

class JsLoader
{
    /**
     * @param $foundedFiles
     * @param $savedFiles
     * @return array
     */
    public function returnMultiColumnWizardArray($foundedFiles, $savedFiles)
    {
        $savedFiles = array_reverse($savedFiles);

        $returnArray = [];

        foreach ($savedFiles as $savedFile)
        {
            foreach ($foundedFiles as $pathLoadedKey => $pathLoadedFile)
            {
                if(str_replace(TL_ROOT,"",$pathLoadedFile) === $savedFile['js_files_path'])
                {
                    $arrayValue = [
                        "select"            => $savedFile['select'],
                        "js_files_path"     => $savedFile['js_files_path'],
                        "js_param"          => $savedFile['js_param'],
                        "js_minimize"       => $savedFile['js_minimize'],
                        "js_files_path_min" => $savedFile['js_files_path_min'],
                    ];

                    if($savedFile['select'])
                    {
                        array_unshift($returnArray,$arrayValue);
                        unset($foundedFiles[$pathLoadedKey]);
                    }
                    break;
                }
            }
        }

        foreach ($foundedFiles as $pathLoadedFile)
        {
            $tmpPath = str_replace(TL_ROOT,"",$pathLoadedFile);
            $returnArray[] = [
                "select"            =>"",
                "js_files_path"     => $tmpPath,
                "js_param"          => "",
                "js_minimize"       => "",
                "js_files_path_min" => "",
            ];
        }

        return serialize($returnArray);
    }

    /**
     * @param $sourceFile
     * @param $destinationFile
     * @return mixed
     */
    public function minimizeFile($sourceFile,$destinationFile)
    {
        $Minify = new Minify\JS();
        $Minify->add($sourceFile);
        $Minify->minify($destinationFile);
        return $destinationFile;
    }

    /**
     * @param $objPage
     * @param $objLayout
     * @param $objPageRegular
     */
    public function loadJs($objPage, $objLayout, $objPageRegular)
    {
        if(isset($objLayout->seoseeJsFiles) && is_array(($jsFilesArray = unserialize($objLayout->seoseeJsFiles))))
        {
            foreach ($jsFilesArray as $jsFile)
            {
                $link = $jsFile["js_files_path"];

                if(!$jsFile["select"] || !file_exists(TL_ROOT . $link))
                {
                    continue;
                }

                if($jsFile['js_minimize'])
                {
                    $Combinder = new Combiner();
                    $Combinder->add($jsFile["js_files_path"]);
                    $link = $Combinder->getCombinedFile();
                    $this->minimizeFile(TL_ROOT . $jsFile["js_files_path"],TL_ROOT . "/" . $link);
                }

                switch ($jsFile["js_param"])
                {
                    case 'footer':
                        $GLOBALS['TL_BODY'][] = "<script src='" . $link . "'>";
                        break;
                    case 'preload':
                        $GLOBALS['TL_HEAD'][] = "<link rel='preload' href='" . $link . "' as='script'>";
                        $GLOBALS['TL_JAVASCRIPT'][] = $link;
                        break;
                    case 'preload_push':
                        header("Link: <" . $link . ">; rel=preload; as=script",false);
                        $GLOBALS['TL_JAVASCRIPT'][] = $link;
                        break;
                    case 'defer':
                        $GLOBALS['TL_HEAD'][] = "<script src='".$link."' defer></script>";
                        break;
                    default:
                        $GLOBALS['TL_JAVASCRIPT'][] = $link."|".$jsFile["js_param"];
                }
            }
        }
    }

}