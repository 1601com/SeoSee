<?php

namespace agentur1601com\seosee;
use agentur1601com\seosee\Helper;
use Contao\LayoutModel;
use MatthiasMullie\Minify;
use Contao\Combiner;
use function PHPSTORM_META\elementType;

class JsLoader
{
    /**
     * @param $foundedFiles
     * @param $savedFiles
     * @param bool $removeExternFiles
     * @return array
     */
    public function returnMultiColumnWizardArray($foundedFiles, $savedFiles, bool $removeExternFiles = false)
    {
        $savedFiles = array_reverse($savedFiles);

        $returnArray = [];

        foreach ($savedFiles as $savedFile)
        {
            if ($savedFile['js_files_extFile'] === "1" && !$removeExternFiles)
            {
                array_unshift($returnArray, $savedFile);
                continue;
            }

            foreach ($foundedFiles as $pathLoadedKey => $pathLoadedFile)
            {
                if(str_replace(TL_ROOT,"", $pathLoadedFile) === $savedFile['js_files_path'])
                {
                    $arrayValue = $this->setWizardArray(
                        $savedFile['select'],
                        $savedFile['js_files_path'],
                        $savedFile['js_files_path_min'],
                        $savedFile['js_param'],
                        $savedFile['js_minimize']
                    );

                    if($savedFile['select'])
                    {
                        array_unshift($returnArray, $arrayValue);
                        unset($foundedFiles[$pathLoadedKey]);
                    }

                    break;
                }
            }
        }

        foreach ($foundedFiles as $pathLoadedFile)
        {
            $tmpPath = str_replace(TL_ROOT,"", $pathLoadedFile);
            $returnArray[] = $this->setWizardArray(
                "",
                $tmpPath
            );
        }

        return serialize($returnArray);
    }

    /**
     * Remove extern JS Files
     * @param $filesArray
     * @return mixed
     */
    public function removeExtFiles($filesArray)
    {
        foreach ($filesArray as &$file)
        {
            if ($file['js_files_extFile'] === '1')
            {
                unset($file);
            }
        }

        return $filesArray;
    }

    /**
     * @param $sourceFile
     * @param $destinationFile
     * @return mixed
     */
    public function minimizeFile($sourceFile, $destinationFile)
    {
        $Minify = new Minify\JS();
        $Minify->add($sourceFile);
        $Minify->minify($destinationFile);
        return $destinationFile;
    }


    /**
     * @param $objPage
     * @param LayoutModel $objLayout
     * @param $objPageRegular
     */
    public function loadJs($objPage, $objLayout, $objPageRegular)
    {
        if(isset($objLayout->seoseeJsFiles) && is_array(($jsFilesArray = unserialize($objLayout->seoseeJsFiles))))
        {
            if ($objLayout->seoseeModifyExtJs === '1')
            {
                require(TL_ROOT . '/system/initialize.php');

                $jsFilesArray = $this->mergeWizardArrByGlobalScriptArr(
                    $jsFilesArray,
                    $this->parseGlobalJsPaths($GLOBALS['TL_JAVASCRIPT'])
                );

                $jsFilesArraySerialized = ['seoseeJsFiles' => serialize($jsFilesArray)];

                $objResult = \Contao\Database::getInstance()
                    ->prepare("UPDATE tl_layout %s WHERE id=?")
                    ->set($jsFilesArraySerialized)
                    ->execute($objLayout->id);

                $GLOBALS['TL_JAVASCRIPT'] = [];
            }

            foreach ($jsFilesArray as $jsFile)
            {
                $link = $this->removeTrailingSlash($jsFile["js_files_path"]);

                if (!$jsFile["select"] || !file_exists(TL_ROOT . $link))
                {
                    continue;
                }

                if ($jsFile['js_minimize'] && $jsFile['js_files_extFile'] !== '1')
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
                        $jsFile["js_param"] = empty($jsFile["js_param"]) ? 'static' : $jsFile["js_param"];
                        $GLOBALS['TL_JAVASCRIPT'][] = $link."|".$jsFile["js_param"];
                }
            }
        }
    }

    /**
     * @param array $globalJsPaths
     * @return array
     */
    private function parseGlobalJsPaths(array $globalJsPaths = [])
    {
        if (!is_array($globalJsPaths))
        {
            return [];
        }

        $returnArr = [];

        foreach ($globalJsPaths as $key => $pathValue)
        {
            if (is_string($key))
            {
                $returnArr[$key] = explode('|', $pathValue);
            }
            else
            {
                $returnArr[] = explode('|', $pathValue);
            }
        }

        return $returnArr;
    }

    /**
     * @param string $select
     * @param string $js_files_path
     * @param string $js_files_path_min
     * @param string $js_param
     * @param string $js_minimize
     * @param string $js_files_extFile
     * @return array
     */
    private function setWizardArray(
        string $select = "",
        string $js_files_path = "",
        string $js_files_path_min = "",
        string $js_param = "",
        string $js_minimize = "",
        string $js_files_extFile = ""
    )
    {
        return [
            "select"            => $select,
            "js_files_path"     => $js_files_path,
            "js_files_path_min" => $js_files_path_min,
            "js_param"          => $js_param,
            "js_minimize"       => $js_minimize,
            "js_files_extFile"  => $js_files_extFile,
        ];
    }

    /**
     * @param array $wizardArray
     * @param array $mergeArray
     * @return array
     */
    private function mergeWizardArrByGlobalScriptArr(array $wizardArray, array $mergeArray)
    {
        foreach ($wizardArray as &$wizardElem)
        {
            foreach ($mergeArray as $key => &$mergeElem)
            {
                if ($wizardElem['js_files_path'] === $mergeElem[0])
                {
                    $wizardElem['verified'] = 1;
                    unset($mergeArray[$key]);
                }
            }

            if ($wizardElem['js_files_extFile'] === '1' && $wizardElem['verified'] !== '1')
            {
                unset($wizardElem);
            }
            else {
                unset($wizardElem['verified']);
            }
        }

        foreach ($mergeArray as $key => $mergeElem)
        {
            $wizardArrayElem = $this->setWizardArray(
                '1',
                $mergeElem[0],
                '',
                $mergeElem[1],
                '',
                '1'
            );

            if (is_string($key) && !empty($key))
            {
                $wizardArray[$key] = $wizardArrayElem;
            }
            else
            {
                array_unshift($wizardArray, $wizardArrayElem);
            }
        }

        return $wizardArray;
    }

    /**
     * @param string $path
     * @return string
     */
    private function removeTrailingSlash(string $path)
    {
        return '/' . ltrim(rtrim($path,'/'), '/');
    }

}
