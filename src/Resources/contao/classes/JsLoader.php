<?php

namespace agentur1601com\seosee;
use MatthiasMullie\Minify;
use agentur1601com\seosee\Helper;

class JsLoader
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
     * @var string $subDirPrefix
     */
    protected $subDirPrefix     =   "_seosee_";

    /**
     * @var \agentur1601com\seosee\Helper|null
     */
    public $Helper = null;

    public function __construct()
    {
        $this->Helper = new Helper();
        if(!is_dir($this->assetsPath))
        {
            throw new Exception("Assets folder do not exists.");
        }
        $this->Helper->createDir($this->assetsPathScript);
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
            $this->assetsPathScript = $this->assetsPathScript . "/" . $this->Helper->safePath($this->subDirPrefix . $subDir);

            if(!$this->Helper->createDir($this->assetsPathScript))
            {
                throw new Exception("Can't create dir with name: " . $this->assetsPathScript);
            }
        }

        $this->Helper->cleanDir($this->assetsPathScript);

        foreach ($filesArray as &$file)
        {
            $filePath = TL_ROOT . $file[$pathKey];

            if(file_exists($filePath) && $file["select"])
            {
                $minFileName = $this->Helper->generateMinFileName($file[$pathKey]);

                $file["js_files_path_min"] = $this->assetsPathScript . "/" . $minFileName;

                $this->safeMiniJs($filePath, $file["js_files_path_min"]);

                $file["js_files_path_min"] = str_replace(TL_ROOT."/web/","/", $file["js_files_path_min"]);
            }
        }
        return $filesArray;
    }

    /**
     * Add Js-Files from Layout to fe_page
     * @param $objPage
     * @param $objLayout
     * @param $objPageRegular
     */
    public function loadJsToLayout($objPage, $objLayout, $objPageRegular)
    {
        if($objLayout->seoseeJsFiles && is_array(($jsFilesArray = unserialize($objLayout->seoseeJsFiles))))
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
                    default:
                        $GLOBALS['TL_JAVASCRIPT'][] = $link."|".$jsFile["js_param"];
                }
            }
        }
    }

    /**
     * @param $sourcePath
     * @param string|null $safePath
     * @return Minify\JS
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
}