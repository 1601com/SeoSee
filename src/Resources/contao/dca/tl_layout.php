<?php
$GLOBALS['TL_DCA']['tl_layout']['palettes']['default'] = str_replace("{expert_legend:hide}",
    "{seosee_seo_files_legend:hide},seoseeJsPath,seoseeJsFiles;{expert_legend:hide}",
    $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_layout']['fields']['seoseeJsPath'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['seoseeJsPath'],
    'inputType'               => 'text',
    'exclude'                 => true,
    'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50 m12'),
    'save_callback'           => array(array('seoseeJsFiles', 'safePath')),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_layout']['fields']['seoseeJsFiles'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['seoseeJsFiles'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array(
        'multiple'=>true,
        'tl_class'=>'clr m12',
        'dragAndDrop'  => true,
        'columnFields' => array(
            'select' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_select'],
                'exclude'   => true,
                'inputType' => 'checkbox',
                'eval'      => array('style'=>'width:20px')
            ),
            'js_files_path' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_files_path'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => array("readonly"=>true)
            ),
            'js_files_path_min' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_files_path_min'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => array('hideBody'=>true,"hideHead"=>true,"style"=>"display:none!important; margin:0!important; padding:0!important; border:0!important; opacity:0;")
            ),
            'js_param' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_param'],
                'exclude'   => true,
                'inputType' => 'select',
                'options'   => array
                (
                    'async' => 'async',
                    'defer' => 'defer',
                    'preload' => 'preload',
                    'preload push' => 'preload push'
                ),
                'eval' 		=> array('style' => 'width:150px', 'includeBlankOption'=>true, 'chosen'=>true)
            ),
            'js_minimize' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_minimize'],
                'exclude'   => true,
                'inputType' => 'checkbox',
                'eval'      => array('style'=>'width:20px')
            ),
        ),
        'buttons' => array(
            'copy' => false,
            'new' => false,
            'delete' => false,
        )
    ),
    'load_callback'           => array(array('seoseeJsFiles', 'loadJsFiles')),
    'sql'                     => "blob NULL"
);

use agentur1601com\seosee\Helper;

class seoseeJsFiles extends Backend
{
    /**
     * @param $savedFiles
     * @param DataContainer $dc
     * @return string
     */
    public function loadJsFiles($savedFiles,DataContainer $dc)
    {
        $helper = new Helper();

        $pathLoadedFiles = [];

        if(!empty($dc->activeRecord->seoseeJsPath))
        {
            $indiPath = explode(",", $dc->activeRecord->seoseeJsPath);
            foreach ($indiPath as $path)
            {
                $pathLoadedFiles = array_merge($pathLoadedFiles, $helper->searchDir(TL_ROOT . "/" . Helper::safePath($path)));
            }
        }
        else
        {
            $pathLoadedFiles = $helper->searchDir(TL_ROOT . "/files");
        }

        $returnArray = $helper->returnMultiColumnWizardArray($pathLoadedFiles, unserialize($savedFiles));

        $returnArray = $helper->generateMinFiles($returnArray, $dc->activeRecord->id);

        return serialize($returnArray);
    }

    /**
     * Generate relative path to js files
     * @param $path
     * @return string
     */
    public function safePath($path)
    {
        return Helper::safePath($path);
    }
}