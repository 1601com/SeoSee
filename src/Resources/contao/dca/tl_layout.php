<?php
$GLOBALS['TL_DCA']['tl_layout']['palettes']['default'] = str_replace("{expert_legend:hide}",
    "{seosee_seo_files_legend:hide},seoseeJsPath,seoseeJsFiles;{expert_legend:hide}",
    $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_layout']['fields']['seoseeJsPath'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['seoseeJsPath'],
    'inputType'               => 'fileTree',
    'eval'                    => [
        'multiple'=>true,
        'fieldType'=>'checkbox',
        'mandatory'=>false,
        'files'=>false,
        'tl_class'=>'w50 m12'
    ],
    'sql'                     => "BLOB NULL"
];

$GLOBALS['TL_DCA']['tl_layout']['fields']['seoseeJsFiles'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['seoseeJsFiles'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => [
        'multiple'=>true,
        'tl_class'=>'clr m12',
        'dragAndDrop'  => true,
        'columnFields' => [
            'select' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_select'],
                'exclude'   => true,
                'inputType' => 'checkbox',
                'eval'      => [
                    'style'=>'width:20px'
                ],
            ],
            'js_files_path' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_files_path'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [
                    "readonly"=>true
                ],
            ],
            'js_files_path_min' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_files_path_min'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [
                    'hideBody'=>true,
                    "hideHead"=>true,
                    "style"=>"display:none!important; margin:0!important; padding:0!important; border:0!important; opacity:0;"
                ]
            ],
            'js_param' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_param'],
                'exclude'   => true,
                'inputType' => 'select',
                'options'   => [
                    'async' => 'async',
                    'defer' => 'defer',
                    'preload' => 'preload',
                    'preload push' => 'preload push'
                ],
                'eval' 		=> [
                    'style'=>'width:150px',
                    'includeBlankOption'=>true,
                    'chosen'=>true
                ],
            ],
            'js_minimize' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_minimize'],
                'exclude'   => true,
                'inputType' => 'checkbox',
                'eval'      => [
                    'style'=>'width:20px',
                ],
            ],
        ],
        'buttons' => [
            'copy' => false,
            'new' => false,
            'delete' => false,
        ],
    ],
    'load_callback'           => [
        ['seoseeJsFiles', 'loadJsFiles']
    ],
    'sql'                     => "blob NULL"
];

use agentur1601com\seosee\Helper;
class seoseeJsFiles extends Backend
{
    /**
     * @param $savedFiles
     * @param DataContainer $dc
     * @return string
     * @throws Exception
     */
    public function loadJsFiles($savedFiles,DataContainer $dc)
    {
        $Helper = new Helper();

        $pathLoadedFiles = [];

        $paths = $Helper->getPathsByUUIDs($dc->activeRecord->seoseeJsPath);

        if(!empty($paths))
        {
            foreach ($paths as $path)
            {
                $pathLoadedFiles = array_merge($pathLoadedFiles, $Helper->searchDir(TL_ROOT . "/" . Helper::safePath($path)));
            }
        }
        else
        {
            $pathLoadedFiles = $Helper->searchDir(TL_ROOT . "/files");
        }

        $returnArray = $Helper->returnMultiColumnWizardArray($pathLoadedFiles, unserialize($savedFiles));

        $returnArray = $Helper->generateMinFiles($returnArray, $dc->activeRecord->id);

        return serialize($returnArray);
    }
}