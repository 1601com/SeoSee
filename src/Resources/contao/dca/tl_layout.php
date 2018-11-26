<?php
$GLOBALS['TL_DCA']['tl_layout']['palettes']['default'] = str_replace("{expert_legend:hide}",
    "{seosee_seo_files_js_legend:hide},seoseeJsPath,seoseeJsFiles;
             {seosee_seo_files_style_legend:hide},seoseeStyleFiles,seoseeStyleFilesLoad;{expert_legend:hide}",
    $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_layout']['fields']['seoseeJsPath'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['seoseeJsPath'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => [
        'multiple'=>true,'fieldType'=>'checkbox','mandatory'=>false,'files'=>false,'tl_class'=>'w50 m12','submitOnChange'=>true
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
                    'async' => 'Async',
                    'defer' => 'Defer',
                    'footer' => 'Footer',
                    'preload' => 'Preload',
                    'preload_push' => 'Preload push'
                ],
                'eval' 		=> [
                    'style'=>'width:150px',
                    'includeBlankOption'=>true,
                    'chosen'=>true
                ],
            ],
            'js_minimize' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['js_minimize'],
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
        ['seoSeeFiles', 'loadJsFiles']
    ],
    'sql'                     => "blob NULL"
];


$GLOBALS['TL_DCA']['tl_layout']['fields']['seoseeStyleFiles'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['seoseeStyleFiles'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => ['multiple'=>true, 'fieldType'=>'checkbox', 'filesOnly'=>true, 'extensions'=>'css,scss,less','submitOnChange'=>true],
    'sql'                     => "BLOB NULL"
];

$GLOBALS['TL_DCA']['tl_layout']['fields']['seoseeStyleFilesLoad'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['seoseeStyleFilesLoad'],
    'inputType'               => 'multiColumnWizard',
    'eval'                    => [
        'multiple'=>true,
        'tl_class'=>'clr m12',
        'dragAndDrop'  => true,
        'columnFields' => [
            'style_files_path' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['style_files_path'],
                'inputType' => 'text',
                'eval'      => [
                    "readonly"=>true
                ],
            ],
            'style_param' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_layout']['style_param'],
                'inputType' => 'select',
                'options'   => [
                    'head' => 'Head',
                    'footer' => 'Footer',
                    'preload' => 'Preload',
                    'preload_push' => 'Preload push'
                ],
                'eval' 		=> [
                    'style'=>'width:150px',
                    'chosen'=>true
                ],
            ],
            'style_version' => [
                'label'     => [],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [
                    'hideBody'=>true,
                    "hideHead"=>true,
                    "style"=>"display:none!important; margin:0!important; padding:0!important; border:0!important; opacity:0;"
                ]
            ],
        ],
        'buttons' => [
            'copy' => false,
            'new' => false,
            'delete' => false,
        ],
    ],
    'load_callback'           => [
        ['seoSeeFiles', 'loadStyleFiles']
    ],
    'save_callback'           => [
        ['seoSeeFiles', 'saveStyleFiles']
    ],
    'sql'                     => "blob NULL"
];

use agentur1601com\seosee\Helper;
use agentur1601com\seosee\JsLoader;
use agentur1601com\seosee\StyleLoader;
class seoSeeFiles extends Backend
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
        $JsLoader = new JsLoader();

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

        $returnArray = $JsLoader->returnMultiColumnWizardArray($pathLoadedFiles, unserialize($savedFiles));

        $returnArray = $JsLoader->generateMinFiles($returnArray, $dc->activeRecord->id);

        return serialize($returnArray);
    }

    /**
     * @param $savedFiles
     * @param DataContainer $dc
     * @return string
     * @throws Exception
     */
    public function loadStyleFiles($savedFiles,DataContainer $dc)
    {
        $StyleLoader = new StyleLoader();
        $Helper = new Helper();

        $files = $Helper->getPathsByUUIDs($dc->activeRecord->seoseeStyleFiles);

        if(empty($files))
        {
            return serialize([]);
        }

        return $StyleLoader->returnMultiColumnWizardArray($files,$savedFiles);
    }

    public function saveStyleFiles($savedFiles,DataContainer $dc)
    {
        return $savedFiles;
    }
}