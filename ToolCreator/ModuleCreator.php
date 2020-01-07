<?php

namespace MelisPlatformFrameworkLaravel\ToolCreator;

use function GuzzleHttp\Psr7\str;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Zend\Session\Container;

class ModuleCreator extends Controller
{

    private $config;
    const MODULE_DIR = __DIR__ . '/../../../../thirdparty/Laravel/Modules/';
    const MODULE_TPL = __DIR__ . '/template/';

    public function __construct()
    {
        $container = new Container('melistoolcreator');
        $this->config = $container['melis-toolcreator'];

//        echo '<style> body{background: #1f1f1f; color: #BCD42A; font-family: monospace;}</style>';
//        echo '<pre>';
    }

    public function __destruct()
    {
//        echo '</pre>';
    }

    public function run()
    {

        if ((!$this->isDbTool() && !$this->isBlankTool()) ||
            (!$this->isFrameworkTool() || !$this->isLaravelFrameworkTool()))
            return;

        $moduleStructure = [
            $this->moduleName() => [
                'Config',
                'Entities',
                'Events',
                'Http' => [
                    'Controllers',
                    'Requests'
                ],
                'Listeners',
                'Providers',
                'Resources' => [
                    'lang',
                    'views'
                ],
                'Routes',
            ]
        ];

        $modGen = function($moduleDir, $curDir, $modGen){

            foreach ($moduleDir As $dirName => $subDir){

                if (is_array($subDir)){
                    $this->moduleDirFile($dirName, $curDir);
                    $modGen($subDir, $curDir.DIRECTORY_SEPARATOR.$dirName, $modGen);
                }
                else
                    $this->moduleDirFile($subDir, $curDir);
            }
        };

        $modGen($moduleStructure, self::MODULE_DIR, $modGen);

        // Module Json
        $this->moduleJson();

        // Adding Js to zend module
        $this->setupJs();

        // Activating new generated module in laravel using Artisan
        Artisan::call('module:enable '.$this->moduleName());
        return;
    }

    private function moduleDirFile($dirName, $directory)
    {
        $curDir = $directory.DIRECTORY_SEPARATOR.$dirName;
        if (is_dir($curDir))
            return;

        mkdir($curDir);

        $setupFx = 'setup'.ucfirst($dirName);
        if (method_exists($this, $setupFx))
            $this->$setupFx($curDir);

//        echo $dirName.'<br>';
    }

    private function setupConfig($curDir)
    {
        // Module Config
        $fileName = 'config.php';
        $file = $this->fgc('Config/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);

        // Module Form Config
        $this->setupFormConfig($curDir);

        // Module Table Config
        $this->setupTableConfig($curDir);
    }

    private function setupFormConfig($curDir)
    {
        $fileName = 'form.config.php';
        $file = $this->fgc('Config/'.$fileName);

        if ($this->isBlankTool()) {
            $file = $this->sp('#TCFIELDROW', '', $file);
            $file = $this->sp('#TCLANGFIELDROW', '', $file);
            $this->generateModuleFile($fileName, $curDir, $file);
            return;
        }

        $fileInputTpl = $this->fgc('Codes/input');
        $fileInputAttrTpl = $this->fgc('Codes/attributes');
        $fileInputOptsTpl = $this->fgc('Codes/options');

        $fieldRow = [];
        $langFieldRow = [];

        foreach ($this->config['step5']['tcf-db-table-col-editable'] As $key => $col) {

            $fileInputTemp = $fileInputTpl;
            $fileInputAttrTemp = $fileInputAttrTpl;
            $fileInputOptsTemp = $fileInputOptsTpl;

            $attributes = [];
            if ($col == $this->getMainTablePk())
                $attributes[] = '\'disabled\' => \'disabled\'';
            else
                if (in_array($col, $this->config['step5']['tcf-db-table-col-required']))
                    $attributes[] = '\'required\' => \'required\'';

            $inputType = $this->config['step5']['tcf-db-table-col-type'][$key];
            $type = $inputType;

            switch ($inputType) {
                case 'Switch':
                    $type = 'Checkbox';
                    $attributes[] = '\'value\' => 1';
                    break;
            }

            // Language form
            // FKs set to hidden input
            if ($this->hasLanguage()) {
                if (!is_bool(strpos($col, 'tclangtblcol_' )))
                    if (in_array(str_replace('tclangtblcol_', '', $col), [$this->getLangTablePk(), $this->getLangMainTableFk(), $this->getLangTableFk()])) {
                        $type = 'Hidden';
                        $attributes = [];
                        $fileInputAttrTemp = '';
                    }
            }

            if (!empty($fileInputAttrTemp))
                $fileInputAttrTemp = $this->sp('#TCINPUTATTRS', implode(','.PHP_EOL."\t\t\t\t\t", $attributes), $fileInputAttrTemp);

            $fileInputTemp = $this->sp('#TCINPUTTYPE', $type, $fileInputTemp);
            $fileInputTemp = $this->sp('#TCINPUTATTRS', $fileInputAttrTemp, $fileInputTemp);
            $fileInputTemp = $this->sp('#TCKEY', $col, $fileInputTemp);

            $options = '';
            switch ($inputType) {
                case 'File':
                    $options = $this->fgc('Codes/file-input');
                    break;
                case 'Switch':
                    $options = $this->fgc('Codes/switch-input');
                    break;
            }

            // Language form
            // FKs set to hidden input
            if ($this->hasLanguage()) {
                if (!is_bool(strpos($col, 'tclangtblcol_' )))
                    if (in_array(str_replace('tclangtblcol_', '', $col), [$this->getLangTablePk(), $this->getLangMainTableFk(), $this->getLangTableFk()])) {
                        $fileInputOptsTemp = '';
                    }
            }

            $fileInputOptsTemp = $this->sp('#TCKEY' , $col, $fileInputOptsTemp);
            $fileInputOptsTemp = $this->sp('#TCINPUTOPTS' , $options, $fileInputOptsTemp);
            $fileInputTemp = $this->sp('#TCINPUTOPTS' , $fileInputOptsTemp, $fileInputTemp);

            // Checking for language type of tool
            // "tclangtblcol_" this means language inputs included
            if (is_bool(strpos($col, 'tclangtblcol_')))
                $fieldRow[] = $fileInputTemp;
            else
                $langFieldRow[] = $fileInputTemp;
        }

        $fieldRow = implode(','.PHP_EOL, $fieldRow);
        $file = $this->sp('#TCFIELDROW', $fieldRow, $file);

        // Language form
        if (!empty($langFieldRow)) {
            $langFieldRow = implode(','.PHP_EOL, $langFieldRow);
            $langFileFieldRow = $this->fgc('Codes/lang-form-config');
            $langFieldRow = PHP_EOL.$this->sp('#TCFIELDROW', $langFieldRow, $langFileFieldRow);
        } else
            $langFieldRow = '';

        $file = $this->sp('#TCLANGFIELDROW', $langFieldRow, $file);

        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupTableConfig($curDir)
    {
        $fileName = 'table.config.php';
        $file = $this->fgc('Config/'.$fileName);

        if ($this->isBlankTool()) {
            $file = $this->sp('#TABLECOLUMNS', '', $file);
            $file = $this->sp('#TABLESEARCHBLECOLUMNS', '', $file);
            $this->generateModuleFile($fileName, $curDir, $file);
            return;
        }


        $tblCols = $this->fgc('Codes/tbl-cols');

        // Table columns
        $tblColumns = [];
        // Table searchable columns
        $searchableColumns = [];

        // Dividing length of table to several columns
        $colWidth = number_format(100/count($this->config['step4']['tcf-db-table-cols']), 0);
        foreach ($this->config['step4']['tcf-db-table-cols'] As $key => $col){

            // Primary column use to update and delete raw entry
            $priCol = ($col == $this->getMainTablePk()) ? 'DT_RowId' : $col;

            $strColTmp = $this->sp('#TCKEYCOL', $priCol, $tblCols);
            $strColTmp = $this->sp('#TCKEY', $col, $strColTmp);
            $tblColumns[] = $this->sp('#TCTBLKEY', $colWidth, $strColTmp);

            if (!isset($searchableColumns[$col]))
                $searchableColumns[$col] = $col;
            else
                $searchableColumns[$col] = $this->config['step3']['tcf-db-table'].'.'.$col;
        }

        // Format array to string
        foreach ($searchableColumns As $key => $col)
            $searchableColumns[$key] = "\t\t\t".'\''.$col.'\'';

        $file = $this->sp('#TABLECOLUMNS', implode(','."\n", $tblColumns), $file);
        $file = $this->sp('#TABLESEARCHBLECOLUMNS', implode(','."\n", $searchableColumns), $file);

        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupEntities($curDir)
    {
        if ($this->isBlankTool()) {
            $this->generateGitKeep($curDir);
            return;
        }

        $file = $this->fgc('Entities/Model.php');

        $table = $this->config['step3']['tcf-db-table'];

        $entityName = $this->makeEntityName($table);
        $primaryKey = $this->getTablePK($table);

        $fillable = [];
        $langFillable = [];

        $skipCols[] = $this->getMainTablePk();
        if ($this->hasLanguage()) {
            $skipCols = array_merge($skipCols, [
                $this->getLangTablePk(),
                $this->getLangMainTableFk(),
                $this->getLangTableFk()
            ]);
        }

        foreach ($this->config['step5']['tcf-db-table-col-editable'] As $col)
            if (!in_array($this->sp('tclangtblcol_', '', $col), $skipCols)) {
                if (is_bool(strpos($col, 'tclangtblcol_')))
                    array_push($fillable, '\''.$col.'\'');
                else
                    array_push($langFillable, '\''.$col.'\'');
            }

        $fillable = implode(', '.PHP_EOL."\t\t", $fillable);

        if (!empty($langFillable))
            $langFillable = implode(', '.PHP_EOL."\t\t", $langFillable);

        $fileInput = [];
        $langFileInput = [];
        $fileUpload = $this->fgc('Codes/file-upload');
        $langFileUpload = $this->fgc('Codes/file-lang-upload');
        foreach ($this->config['step5']['tcf-db-table-col-type'] As $key => $input) {
            $colInput = $this->config['step5']['tcf-db-table-col-editable'][$key];
            if ($input == 'File')
                if (is_bool(strpos($colInput, 'tclangtblcol_')))
                    $fileInput[] = $this->sp('#TCKEY', $colInput, $fileUpload);
                else
                    $langFileInput[] = $this->sp('#TCKEY', $colInput, $langFileUpload);
        }

        $fileInput = ($fileInput) ? implode(PHP_EOL, $fileInput): '';
        $langFileInput = ($langFileInput) ? implode(PHP_EOL, $langFileInput): '';

        $storeFx = $this->fgc('Codes/store-file');

        $callStoreFx = ($fileInput) ? '$this->storeFile();' : '';
        $storeFx = ($fileInput) ? $this->sp('#TCFILEUPLOAD', $fileInput, $storeFx) : '';

        $file = $this->sp(
            ['ModelName', '#TCTABLE', '#TCKEYNAME', '#TCFILLABLE', '#TCCALLSTOREFILE', '#TCSTOREFILEFUNCTION'],
            [$entityName, $table, $primaryKey, $fillable, $callStoreFx, $storeFx],
            $file
        );

        $modelRelation = '';
        $selectQryFx = '';

        $tblColDisplayFilters = [];
        foreach ($this->config['step4']['tcf-db-table-cols'] As $key => $col)
            if ($this->config['step4']['tcf-db-table-col-display'][$key] != 'raw_view') {
                $tblColDisplayFilters[] = $this->sp(
                    ['#TCKEY', '#TCCOLDISPLAY'],
                    [$col, $this->config['step4']['tcf-db-table-col-display'][$key]],
                    $this->fgc('/Codes/tbl-col-display-filter')
                );
            }

        $displayColsTbl = $this->fgc('Codes/tbl-display-filter');
        $tableColsDisplay = ($tblColDisplayFilters) ? $this->sp('#TCTABLECOLS', implode(PHP_EOL, $tblColDisplayFilters), $displayColsTbl) : '';

        if (!$this->hasLanguage()) {
            $selectQry = $this->fgc('Codes/table-get-list');
        } else {
            $modelRelation = $this->fgc('Codes/lang-relation');
            $modelRelation = $this->sp('ModelLangName', $this->makeEntityName($this->getLangTable()), $modelRelation);

            $selectQry = $this->fgc('Codes/lang-table-get-list');
            $selectQry =  $this->sp('ModelLangName', $this->makeEntityName($this->getLangTable()), $selectQry);

            $selectQryFx = $this->fgc('Codes/table-get-list-join');
            $selectQryFx = $this->sp('ModelLangName', $this->makeEntityName($this->getLangTable()), $selectQryFx);
        }

        $file = $this->sp(
            [
                '#TCLANGRELATION',
                '#TCSELECT',
                '#TCDISPLAYTABLECOLS',
                '#TCJOINMETHODS'
            ],
            [
                $modelRelation,
                $selectQry,
                $tableColsDisplay,
                $selectQryFx
            ],
            $file
        );

        $this->generateModuleFile($entityName.'.php', $curDir, $file);

        if ($this->hasLanguage()) {
            $file = $this->fgc('Entities/LangModel.php');

            $callStoreFx = ($langFileInput) ? '$this->storeFile($form);' : '';
            $storeFx = $this->fgc('Codes/store-lang-file');
            $storeFx = ($langFileInput) ? $this->sp('#TCFILEUPLOAD', $langFileInput, $storeFx) : '';

            $file = $this->sp(
                [
                    '#TCTABLE',
                    '#TCKEYNAME',
                    '#TCFK1',
                    '#TCFK2',
                    '#TCFILLABLE',
                    '#TCCALLSTOREFILE',
                    '#TCSTOREFILEFUNCTION'
                ],
                [
                    $this->getLangTable(),
                    $this->getLangTablePk(),
                    $this->getLangMainTableFk(),
                    $this->getLangTableFk(),
                    $langFillable,
                    $callStoreFx,
                    $storeFx
                ],
                $file
            );

            $entityName = $this->makeEntityName($this->getLangTable());
            $file = $this->sp('ModelLangName', $entityName, $file);

            $this->generateModuleFile($entityName.'.php', $curDir, $file);
        }
    }

    public function setupEvents($curDir)
    {
        if ($this->isBlankTool()) {
            $this->generateGitKeep($curDir);
            return;
        }

        $fileName = 'SaveFormEvent.php';
        $file = $this->fgc('Events/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);

        $fileName = 'DeleteItemEvent.php';
        $file = $this->fgc('Events/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupControllers($curDir)
    {
        if ($this->isBlankTool()) {

            $fileName = 'IndexController.php';
            $file = $this->fgc('Controllers/'.str_replace('Index', 'Blank', $fileName));
            $this->generateModuleFile($fileName, $curDir, $file);

            return;
        }

        $fileName = 'IndexController.php';
        $file = $this->fgc('Controllers/'.$fileName);

        $table = $this->config['step3']['tcf-db-table'];
        $entityName = $this->makeEntityName($table);

        $toolForm = '';
        if ($this->isDbTool()) {

            $toolForm = $this->fgc('Codes/form-function');
            $langQuery = ($this->hasLanguage()) ? $this->fgc('Codes/cms-lang-query') : '';
            $toolForm = $this->sp('#TCLANGCMS', $langQuery, $toolForm);
        }

        $file = $this->sp('#TCTOOLTYPEEDTION', $toolForm, $file);

        $file = $this->sp('ModelName', $entityName, $file);

        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupRequests($curDir)
    {
        if ($this->isBlankTool()) {
            $this->generateGitKeep($curDir);
            return;
        }

        $file = $this->fgc('Requests/Request.php');

        $table = $this->config['step3']['tcf-db-table'];
        $entityName = $this->makeEntityName($table);

        $requiredCols = [];
        $requiredColsMsg = [];

        $requiredLangCols = [];
        $requiredLangColsMsg = [];

        $skipCols[] = $this->getMainTablePk();
        if ($this->hasLanguage()) {
            $skipCols = array_merge($skipCols, [
                $this->getLangTablePk(),
                $this->getLangMainTableFk(),
                $this->getLangTableFk()
            ]);
        }

        foreach ($this->config['step5']['tcf-db-table-col-required'] As $key => $col)
            if (!in_array($this->sp('tclangtblcol_', '', $col), $skipCols)) {
                if ($this->config['step5']['tcf-db-table-col-type'][array_search($col, $this->config['step5']['tcf-db-table-col-editable'])] !== 'File') {

                    if (is_bool(strpos($col, 'tclangtblcol_')))
                        array_push($requiredCols, '\''.$col.'\' => \'required\'');
                    else
                        array_push($requiredLangCols, '\''.$col.'\' => \'required\'');

                }

                if (is_bool(strpos($col, 'tclangtblcol_')))
                    array_push($requiredColsMsg, '\''.$col.'.required\' => __(\'moduletpl::messages.input_required\')');
                else
                    array_push($requiredLangColsMsg, '\''.$col.'.required\' => __(\'moduletpl::messages.input_required\')');
            }

        $fileInput = [];
        $langFileInput = [];

        foreach ($this->config['step5']['tcf-db-table-col-editable'] As $key => $col)
            if (!in_array($this->sp('tclangtblcol_', '', $col), $skipCols)) {
                if ($this->config['step5']['tcf-db-table-col-type'][$key] == 'File') {

                    if (in_array($col, $this->config['step5']['tcf-db-table-col-required'])) {
                        if (is_bool(strpos($col, 'tclangtblcol_')))
                            $fileInput[] = '$rules = $this->fileRules($rules, \'' . $col . '\');';
                        else
                            $langFileInput[] = '$rules = $this->fileRules($rules, $form, \'' . $col . '\');';
                    } else {
                        if (is_bool(strpos($col, 'tclangtblcol_')))
                            array_push($fileInput, '$rules = $this->fileRules($rules, \'' . $col . '\', false);');
                        else
                            array_push($langFileInput, '$rules = $this->fileRules($rules, $form, \'' . $col . '\', false);');
                    }

                    if (is_bool(strpos($col, 'tclangtblcol_'))) {
                        array_push($requiredColsMsg, '\'' . $col . '.max\' => __(\'moduletpl::messages.max_upload\')');
                    } else {
                        array_push($requiredLangColsMsg, '\'' . $col . '.max\' => __(\'moduletpl::messages.max_upload\')');
                    }
                }
            }

        $fileUpload = (!empty($fileInput)) ? implode(PHP_EOL."\t\t", $fileInput) : '';
        $langFileUpload = (!empty($langFileInput)) ? implode(PHP_EOL."\t\t", $langFileInput) : '';

        $requiredCols = implode(', '.PHP_EOL."\t\t\t", $requiredCols);
        $requiredColsMsg = implode(', '.PHP_EOL."\t\t\t", $requiredColsMsg);

        $requiredLangCols = (!empty($requiredLangCols)) ? implode(', '.PHP_EOL."\t\t\t", $requiredLangCols) : '';
        $requiredLangColsMsg = (!empty($requiredLangCols)) ? implode(', '.PHP_EOL."\t\t\t", $requiredLangColsMsg) : '';

        $fileInputRules = (!empty($fileUpload)) ? $this->fgc('Codes/file-upload-rules') : '';

        $file = $this->sp(
            [
                'ModelName',
                '#TCCOLSRULES',
                '#TCCOLSMGS',
                '#TCREQUIREDFILE',
                '#TCFILERULESFX'
            ],
            [
                $entityName,
                $requiredCols,
                $requiredColsMsg,
                $fileUpload,
                $fileInputRules
            ],
            $file
        );

        $this->generateModuleFile($entityName.'Request.php', $curDir, $file);

        if ($this->hasLanguage()) {
            $file = $this->fgc('Requests/LangRequest.php');

            $entityName = $this->makeEntityName($this->getLangTable());

            $fileInputRules = (!empty($fileUpload)) ? $this->fgc('Codes/file-lang-upload-rules') : '';

            $file = $this->sp(
                [
                    'ModelName',
                    '#TCCOLSRULES',
                    '#TCCOLSMGS',
                    '#TCREQUIREDFILE',
                    '#TCFILERULESFX'
                ],
                [
                    $entityName,
                    $requiredLangCols,
                    $requiredLangColsMsg,
                    $langFileUpload,
                    $fileInputRules
                ],
                $file
            );

            $this->generateModuleFile($entityName.'Request.php', $curDir, $file);
        }
    }

    private function setupListeners($curDir)
    {
        if ($this->isBlankTool()) {
            $this->generateGitKeep($curDir);
            return;
        }

        $table = $this->config['step3']['tcf-db-table'];
        $entityName = $this->makeEntityName($table);

        $fileName = 'SaveFormRequest.php';
        $file = $this->fgc('Listeners/'.$fileName);

        $langRequest = '';
        if ($this->hasLanguage()) {
            $langRequestName = $this->makeEntityName($this->getLangTable());
            $langRequest = PHP_EOL."\t\t".'\Modules\ModuleTpl\Http\Requests\\'.$langRequestName. 'Request::class,';
        }

        $file = $this->sp('#TCLANGREQUEST', $langRequest, $file);
        $file = $this->sp('ModelName', $entityName, $file);

        $this->generateModuleFile($fileName, $curDir, $file);

        $fileName = 'DeleteItemRequest.php';
        $file = $this->fgc('Listeners/'.$fileName);

        $langRequest = '';
        if ($this->hasLanguage()) {
            $langRequestName = $this->makeEntityName($this->getLangTable());
            $langRequest = PHP_EOL."\t\t".'\Modules\ModuleTpl\Http\Requests\\'.$langRequestName. 'Request::class,';
        }
        $file = $this->sp('#TCLANGREQUEST', $langRequest, $file);
        $file = $this->sp('ModelName', $entityName, $file);

        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupProviders($curDir)
    {
        $fileName = 'ModuleServiceProvider.php';
        $file = $this->fgc('Providers/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);

        $fileName = 'RouteServiceProvider.php';
        $file = $this->fgc('Providers/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);


        $fileName = 'EventServiceProvider.php';
        $file = $this->fgc('Providers/'.$fileName);

        $events = '';
        if (!$this->isBlankTool()) {
            $events = $this->fgc('Codes/event-listeners');
        }
        $file = $this->sp('#TCEVENTLISTENERS', $events, $file);

        $this->generateModuleFile($fileName, $curDir, $file);

    }

    private function setupLang($curDir)
    {
        $coreLang = DB::table('melis_core_lang')->get();
        $commonTransTpl = require __DIR__.'/template/Resources/lang/messages.php';


        // Merging texts from steps forms
        $stepTexts = $this->config['step2'];

        if (!$this->isBlankTool()) {

            // Common translation
            $commonTranslations = [];

            $currentLocale = app()->getLocale();

            foreach ($coreLang As $lang) {
                $tempLocale = explode('_', $lang->lang_locale)[0];
                app()->setLocale($tempLocale);

                foreach ($commonTransTpl As $cText)
                    $commonTranslations[$lang->lang_locale][$cText] = __('melisLaravel::messages.'.$cText);

                if (!empty($this->config['step6'][$lang->lang_locale])){
                    foreach ($this->config['step6'][$lang->lang_locale]['pri_tbl'] As $col => $val){
                        if (!strpos($col, 'tcinputdesc'))
                            $col .= '_text';
                        $commonTranslations[$lang->lang_locale][$col] = $val;
                    }

                    if (!empty($this->config['step6'][$lang->lang_locale]['lang_tbl'])){
                        foreach ($this->config['step6'][$lang->lang_locale]['lang_tbl'] As $col => $val){
                            if (!strpos($col, 'tcinputdesc'))
                                $col .= '_text';
                            $commonTranslations[$lang->lang_locale][$col] = $val;
                        }
                    }
                }
            }

            app()->setLocale($currentLocale);

            // Merging texts from steps forms
            $stepTexts = array_merge_recursive($stepTexts, $commonTranslations);
        }

        $translations = [];
        $textFields = [];

        // Default value setter
        foreach ($coreLang As $lang){
            $translations[$lang->lang_locale] = [];
            if (!empty($stepTexts[$lang->lang_locale])){
                foreach($stepTexts[$lang->lang_locale]  As $key => $text){

                    if (!in_array($key, ['tcf-lang-local', 'tcf-tbl-type'])){
                        // Input description
                        if (strpos($key, 'tcinputdesc')){
                            if (empty($text))
                                $text = $stepTexts[$lang->lang_locale][$key];

                            $key = $this->sp('tcinputdesc', 'tooltip', $key);
                            $key = $this->sp('tclangtblcol_', '', $key);
                        }

                        $translations[$lang->lang_locale][$key] = $text;
                    }else
                        $text = '';

                    // Getting fields that has a value
                    // this will be use as default value if a field doesn't have value
                    if (!empty($text))
                        $textFields[$key] = $text;
                }
            }
        }

        // Assigning values to the fields that doesn't have value(s)
        foreach ($translations As $local => $texts)
            foreach ($textFields As $key => $text)
                if (empty($texts[$key]))
                    $translations[$local][$key] = $text;

        foreach ($translations As $locale => $texts){
            $strTranslations = '';
            foreach ($texts As $key => $text){

                if (in_array($key, ['tcf-lang-local_text', 'tcf-tbl-type_text']))
                    continue;

                $text = $this->sp("'", "\'", $text);
                $key = $this->sp('-', '_', $key);
                $key = $this->sp('tcf_', '', $key);

                $strTranslations .= "\t\t".'\''.$key.'\' => \''.$text.'\','."\n";
            }

            $file = $this->fgc('Resources/lang/language-tpl.php');
            $file = $this->sp('#TCTRANSLATIONS', $strTranslations, $file);

            $locale = explode('_', $locale)[0];

            $langDir = $curDir.'/'.$locale;
            mkdir($langDir);

            $this->generateModuleFile('messages.php', $langDir, $file);
        }
    }

    private function setupViews($curDir)
    {
        if ($this->isBlankTool()) {
            $file = $this->fgc('Resources/views/blank-index.blade.php');
            $this->generateModuleFile('index.blade.php', $curDir, $file);
            return;
        }

        $fileName = 'form.blade.php';
        if (!$this->hasLanguage() && $this->isModalTypeTool())
                $fileName = 'tab-'. $fileName;
        else {
            if ($this->isModalTypeTool())
                $fileName = 'lang-'.$fileName;
            else
                $fileName = 'tab-lang-'.$fileName;
        }

        $file = $this->fgc('Resources/views/'.$fileName);
        $this->generateModuleFile('form.blade.php', $curDir, $file);

        $fileName = 'index.blade.php';
        if (!$this->isModalTypeTool())
            $fileName = 'tab-'. $fileName;

        $file = $this->fgc('Resources/views/'.$fileName);
        $this->generateModuleFile('index.blade.php', $curDir, $file);
    }

    private function setupRoutes($curDir)
    {
        $fileName = 'api.php';
        $file = $this->fgc('Routes/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);


        $fileName = 'web.php';
        if ($this->isBlankTool()) {

            $file = $this->fgc('Routes/blank-'.$fileName);
            $this->generateModuleFile($fileName, $curDir, $file);

        } else {

            $file = $this->fgc('Routes/'.$fileName);

            $route = '';
            if ($this->isDbTool()) {
                $route = 'Route::get(\'/form/{id?}\', \'IndexController@form\');';
            }
            $file = $this->sp('#TCTOOLTYPE', $route, $file);
            $this->generateModuleFile($fileName, $curDir, $file);
        }
    }

    private function setupJs()
    {
        if ($this->isBlankTool())
            return;

        $fileName = 'tool.js';
        $temp = $fileName;
        if (!$this->isModalTypeTool())
            $temp = 'tab-'.$fileName;

        $file = $this->fgc('Resources/assets/js/'.$temp);

        $zendModule = __DIR__ .'/../../../../module/';
        $moduleAssetsDir = $zendModule.DIRECTORY_SEPARATOR.$this->moduleName().DIRECTORY_SEPARATOR.'public/js';

        $langFromData = '';
        if ($this->hasLanguage()) {
            $langFromData ='Codes/lang-formdata-js';
            if (!$this->isModalTypeTool())
                $langFromData = 'Codes/tab-lang-formdata-js';

            $langFromData = $this->fgc($langFromData);
        }
        $file = $this->sp('#TCLANGDATAFORM', $langFromData, $file);

        $this->generateModuleFile($fileName, $moduleAssetsDir, $file);
    }

    private function moduleJson()
    {
        $fileName = 'module.json';
        $file = $this->fgc($fileName);
        $this->generateModuleFile($fileName, self::MODULE_DIR.$this->moduleName(), $file);
    }

    /**
     * This method generate files to the directory
     *
     * @param string $fileName - file name
     * @param string $targetDir - the target directory where the file will created
     * @param string $fileContent - will be the content of the file created
     */
    private function generateModuleFile($fileName, $targetDir, $fileContent)
    {
        // Tool creator session container
        $moduleName = $this->moduleName();

        $fileContent = str_replace('ModuleTpl', $moduleName, $fileContent);
        $fileContent = str_replace('moduleTpl', lcfirst($moduleName), $fileContent);
        $fileContent = str_replace('moduletpl', strtolower($moduleName), $fileContent);

        // Only for Language tool that file contain "tclangtblcol_"
        $fileContent = $this->sp('tclangtblcol_', '', $fileContent);

        $targetFile = $targetDir.'/'.$fileName;
        if (!file_exists($targetFile)){
            $targetFile = fopen($targetFile, 'x+');
            fwrite($targetFile, $fileContent);
            fclose($targetFile);
        }
    }

    private function generateGitKeep($targetDir)
    {
        $targetFile = $targetDir.'/.gitKeep';

        if (!file_exists($targetFile)){
            $targetFile = fopen($targetFile, 'x+');
            fwrite($targetFile, '');
            fclose($targetFile);
        }
    }

    private function moduleName()
    {
        return $this->makeModuleName($this->config['step1']['tcf-name']);
    }

    function getMainTablePk()
    {
        $table = $this->config['step3']['tcf-db-table'];
        return $this->getTablePK($table);
    }

    function getTablePK($table)
    {
        $selectedTbl = $this->describeTable($table);

        foreach ($selectedTbl As $col)
            if ($col->Key == 'PRI' && $col->Extra == 'auto_increment')
                return $col->Field;

        return null;
    }

    function getLangTable()
    {
        return $this->config['step3']['tcf-db-table-language-tbl'];
    }

    function getLangTablePk()
    {
        $selectedTbl = $this->describeTable($this->config['step3']['tcf-db-table-language-tbl']);

        foreach ($selectedTbl As $col)
            if ($col->Key == 'PRI' && $col->Extra == 'auto_increment')
                return $col->Field;

        return null;
    }

    function getLangMainTableFk()
    {
        return $this->config['step3']['tcf-db-table-language-pri-fk'];
    }

    function getLangTableFk()
    {
        return $this->config['step3']['tcf-db-table-language-lang-fk'];
    }

    function describeTable($table)
    {
        return DB::select('DESCRIBE '.$table);
    }

    function fgc($dir)
    {
        return file_get_contents(self::MODULE_TPL.$dir);
    }

    function sp($search, $replace, $subject)
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     * This will modified a string to valid zf2 module name
     * @param string $str
     * @return string
     */
    function makeModuleName($str) {
        $str = preg_replace('/([a-z])([A-Z])/', "$1$2", $str);
        $str = str_replace(['-', '_'], '', ucwords(strtolower($str)));
        $str = ucfirst($str);
        $str = $this->cleanString($str);
        return $str;
    }

    function makeEntityName($str)
    {
        $str = preg_replace('/([a-z])([A-Z])/', "$1$2", $str);
        $str = str_replace(['-', '_'], ' ', $str);
        return  str_replace(' ', '', ucwords(strtolower($str)));
    }

    /**
     * Clean strings from special characters
     *
     * @param string $str
     * @return string
     */
    function cleanString($str)
    {
        $str = preg_replace("/[áàâãªä]/u", "a", $str);
        $str = preg_replace("/[ÁÀÂÃÄ]/u", "A", $str);
        $str = preg_replace("/[ÍÌÎÏ]/u", "I", $str);
        $str = preg_replace("/[íìîï]/u", "i", $str);
        $str = preg_replace("/[éèêë]/u", "e", $str);
        $str = preg_replace("/[ÉÈÊË]/u", "E", $str);
        $str = preg_replace("/[óòôõºö]/u", "o", $str);
        $str = preg_replace("/[ÓÒÔÕÖ]/u", "O", $str);
        $str = preg_replace("/[úùûü]/u", "u", $str);
        $str = preg_replace("/[ÚÙÛÜ]/u", "U", $str);
        $str = preg_replace("/[’‘‹›‚]/u", "'", $str);
        $str = preg_replace("/[“”«»„]/u", '"', $str);
        $str = str_replace("–", "-", $str);
        $str = str_replace(" ", " ", $str);
        $str = str_replace("ç", "c", $str);
        $str = str_replace("Ç", "C", $str);
        $str = str_replace("ñ", "n", $str);
        $str = str_replace("Ñ", "N", $str);

        return ($str);
    }

    private function isDbTool()
    {
        return $this->config['step1']['tcf-tool-type'] == 'db' ? true : false;
    }

    private function isBlankTool()
    {
        return $this->config['step1']['tcf-tool-type'] == 'blank' ? true : false;
    }

    private function isModalTypeTool()
    {
        return $this->config['step1']['tcf-tool-edit-type'] == 'modal' ? true : false;
    }

    private function isFrameworkTool()
    {
        return $this->config['step1']['tcf-create-framework-tool']  ? true : false;
    }

    private function isLaravelFrameworkTool()
    {
        return $this->config['step1']['tcf-tool-framework'] == 'laravel'  ? true : false;
    }

    private function hasLanguage()
    {
        return ($this->config['step3']['tcf-db-table-has-language']) ? true : false;
    }

}