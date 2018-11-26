<?php


namespace agentur1601com\seosee;
use FilesModel;
/**
 * Class Helper
 * @package agentur1601com\seosee
 * @author LBeckX <lb@1601.com>
 */
class Helper
{
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
            return [];
        }

        foreach ($fileTree as &$path)
        {
            $path = FilesModel::findByPk($path)->path;
        }

        return $fileTree;
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
     * @param $path
     * @return string
     */
    public static function safePath($path)
    {
        return rtrim(ltrim(trim(preg_replace("/(\/+)/","/",$path)),"/"),"/");
    }

    /**
     * @param $dir
     * @param string $nameContain
     * @return bool
     */
    public function cleanDir($dir, $nameContain = "seosee")
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
    public function createDir($dir)
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
    public function generateMinFileName(string $path)
    {
        $dataFile = pathinfo($path);
        return trim("seosee_" . md5($dataFile["dirname"]) . "_" . $dataFile["filename"] . ".min." . $dataFile["extension"]);
    }
}