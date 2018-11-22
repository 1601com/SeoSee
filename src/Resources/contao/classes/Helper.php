<?php


namespace agentur1601com\seosee;
use MatthiasMullie\Minify;
use FilesModel;

/**
 * Class Helper
 * @package agentur1601com\seosee
 * @author LBeckX <lb@1601.com>
 */
class Helper
{
    /**
     * @var string $assetsPath
     */
    protected $assetsPath       =   TL_ROOT . "/web/assets";

    /**
     * @var string $assetsPathScript
     */
    protected $assetsPathScript =   TL_ROOT . "/web/assets/js";

    /**
     * @var string $assetsPathStyles
     */
    protected $assetsPathStyles =   TL_ROOT . "/web/assets/css";

    /**
     * @var string $subDirPrefix
     */
    protected $subDirPrefix     =   "_seosee_";

    /**
     * Helper constructor.
     */
    public function __construct()
    {
        if(!is_dir($this->assetsPath))
        {
            throw new Exception("Assets folder do not exists.");
        }

        $this->createDir($this->assetsPathScript);

        $this->createDir($this->assetsPathStyles);
    }

    /**
     * @param string $path
     * @param array $searchFor
     * @param string $type
     * @return array
     */
    public function searchDir($path = __DIR__, $searchFor = array("js"), $type = "-r")
    {
        $files = array();
        if ($handle = opendir($path))
        {
            while (false !== ($file = readdir($handle)))
            {
                if($file === '.' || $file === '..')
                {
                    continue;
                }

                if(is_dir($path."/".$file) && $type==="-r")
                {
                    $files = array_merge($files,$this->searchDir($path."/".$file,$searchFor,$type));
                }
                else
                {
                    $dataFile = pathinfo($path."/".$file);

                    if((is_array($searchFor) && in_array($dataFile["extension"],$searchFor)) ||
                       (is_string($searchFor) && $searchFor === $dataFile["extension"]))
                    {
                        $files[] = $path."/".$file;
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * Add Js-Files from Layout to fe_page
     * @param $objPage
     * @param $objLayout
     * @param $objPageRegular
     */
    public function loadJsToLayout($objPage, $objLayout, $objPageRegular)
    {
        if($objLayout->seoseeJsFiles && $jsFilesArray = unserialize($objLayout->seoseeJsFiles))
        {
            foreach ($jsFilesArray as $jsFile)
            {
                if(!$jsFile["select"])
                {
                    continue;
                }

                if($jsFile["js_minimize"])
                {
                    $link = $jsFile["js_files_path_min"];
                }
                else
                {
                    $link = $jsFile["js_files_path"];
                }

                switch ($jsFile["js_param"])
                {
                    case 'preload':
                        $GLOBALS['TL_HEAD'][] = "<link rel='preload' href='" . $link . "' as='script'>";
                        $GLOBALS['TL_JAVASCRIPT'][] = $link;
                        break;
                    case 'preload push':
                        header("Link: <" . $link . ">; rel=preload; as=script",false);
                        $GLOBALS['TL_JAVASCRIPT'][] = $link;
                        break;
                    default:
                        $GLOBALS['TL_JAVASCRIPT'][] = $link."|".$jsFile["js_param"];
                }
            }
        }
    }

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
        return $returnArray;
    }

    /**
     * @param array $filesArray
     * @param string $subDir
     * @param string $pathKey
     * @return array
     */
    public function generateMinFiles(array $filesArray, $subDir = "sub", $pathKey = "js_files_path")
    {
        if(!empty($subDir) && $subDir !== null)
        {
            $this->assetsPathScript = $this->assetsPathScript . "/" . self::safePath($this->subDirPrefix . $subDir);

            if(!$this->createDir($this->assetsPathScript))
            {
                throw new Exception("Can't create dir with name: " . $this->assetsPathScript);
            }
        }

        $this->cleanDir($this->assetsPathScript);

        foreach ($filesArray as &$file)
        {
            $filePath = TL_ROOT . $file[$pathKey];

            if(file_exists($filePath) && $file["select"])
            {
                $minFileName = $this->generateMinFileName($file[$pathKey]);

                $file["js_files_path_min"] = $this->assetsPathScript . "/" . $minFileName;

                $this->safeMiniJs($filePath, $file["js_files_path_min"]);

                $file["js_files_path_min"] = str_replace(TL_ROOT."/web/","/", $file["js_files_path_min"]);
            }
        }
        return $filesArray;
    }

    /**
     * @param $path
     * @return string
     */
    public static function safePath($path)
    {
        return rtrim(ltrim(trim(preg_replace("/(\/+)/","/",$path)),"/"),"/");
    }

    /**
     * @param $fileTree
     * @return array
     * @throws \Exception
     */
    public function getPathsByUUIDs($fileTree):array
    {
        if(!$fileTree)
        {
            return [];
        }

        if(!is_array($fileTree))
        {
            $fileTree = unserialize($fileTree);
        }

        if(!is_array($fileTree))
        {
            throw new \Exception("File Tree must be an Array - is type " . gettype ( $fileTree ));
        }

        foreach ($fileTree as &$path)
        {
            $path = FilesModel::findByPk($path)->path;
        }

        return $fileTree;
    }

    /**
     * @param $sourcePath
     * @param string|null $safePath
     * @return Minify\JS
     * @throws Exception
     */
    protected function safeMiniJs($sourcePath,string $safePath = null)
    {
        if(!is_string($safePath) || empty($safePath))
        {
            throw new Exception("Safepath must be a string: ".addslashes($safePath));
        }

        $minifier = new Minify\JS();

        if(is_string($sourcePath) && !file_exists($sourcePath))
        {
            throw new Exception("Source file does not exists: " . addslashes($sourcePath));
        }
        elseif(is_string($sourcePath) && file_exists($sourcePath))
        {
            $minifier->add($sourcePath);
        }
        elseif(is_array($sourcePath))
        {
            foreach ($sourcePath as $path)
            {
                if(is_string($path) && !file_exists($path))
                {
                    throw new Exception("Source file does not exists: " . addslashes($path));
                }
                elseif (is_string($path) && !file_exists($path))
                {
                    $minifier->add($path);
                }
                else
                {
                    throw new Exception("Source file does not exists. Must be string or string-array");
                }
            }
        }
        else
        {
            throw new Exception("Source file does not exists. Must be string or string-array");
        }

        $minifier->minify($safePath);

        return $minifier;
    }

    /**
     * @param $dir
     * @param string $nameContain
     * @return bool
     */
    private function cleanDir($dir, $nameContain = "seosee")
    {
        if(is_dir($dir))
        {
            $files = glob($dir . '/*');
            foreach($files as $file)
            {
                $dataFile = pathinfo($file);
                if(is_file($file) && strpos($dataFile["filename"], $nameContain) !== false)
                {
                    unlink($file);
                }
                elseif (is_file($file) && !$nameContain)
                {
                    unlink($file);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param $dir
     * @return bool
     */
    private function createDir($dir)
    {
        if(is_dir($dir))
        {
            return true;
        }
        return mkdir($dir);
    }

    /**
     * @param string $path
     * @return mixed
     */
    protected function generateMinFileName(string $path)
    {
        $dataFile = pathinfo($path);
        return trim("seosee_" . md5($dataFile["dirname"]) . "_" . $dataFile["filename"] . ".min." . $dataFile["extension"]);
    }
}