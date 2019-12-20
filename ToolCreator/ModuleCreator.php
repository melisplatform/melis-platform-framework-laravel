<?php

namespace MelisPlatformFrameworkLaravel\ToolCreator;

use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Zend\Session\Container;

class ModuleCreator
{

    private $config;
    const MODULE_DIR = __DIR__ . '/../../../../thirdparty/Laravel/Modules/';
    const MODULE_TPL = __DIR__ . '/template/';

    public function __construct()
    {
        $container = new Container('melistoolcreator');
        $this->config = $container['melis-toolcreator'];

        echo '<style> body{background: #1f1f1f; color: #BCD42A; font-family: monospace;}</style>';
        echo '<pre>';
    }

    public function __destruct()
    {
        echo '</pre>';
    }

    public function run()
    {
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

        $this->moduleJson();

//        $this->setupJs();

        exit;

        Artisan::call('module:enable '.$this->moduleName());

//        exit;
//        Artisan::call('module:make-controller IndexController Testz');
//        Artisan::call('module:make-model Calendar Testz');
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

        echo $dirName.'<br>';
    }

    private function setupConfig($curDir)
    {
        // Module Config
        $fileName = 'config.php';
        $file = self::fgc('Config/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);

        // Module Form Config
        $this->setupFormConfig($curDir);

        // Module Table Config
        $this->setupTableConfig($curDir);
    }

    private function setupFormConfig($curDir)
    {
        $fileName = 'form.config.php';
        $file = self::fgc('Config/'.$fileName);

        $fileInputTpl = self::fgc('Codes/input');
        $fileInputAttrTpl = self::fgc('Codes/attributes');
        $fileInputOptsTpl = self::fgc('Codes/options');

        $fieldRow = [];

        foreach ($this->config['step5']['tcf-db-table-col-editable'] As $key => $col){

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

            switch ($inputType){
                case 'Switch':
                    $type = 'Checkbox';
                    $attributes[] = '\'value\' => 1';
                    break;
            }

            $fileInputAttrTemp = self::sp('#TCINPUTATTRS', implode(','.PHP_EOL."\t\t\t\t\t", $attributes), $fileInputAttrTemp);

            $fileInputTemp = self::sp('#TCINPUTTYPE', $type, $fileInputTemp);
            $fileInputTemp = self::sp('#TCINPUTATTRS', $fileInputAttrTemp, $fileInputTemp);
            $fileInputTemp = self::sp('#TCKEY', $col, $fileInputTemp);

            $options = '';
            switch ($inputType){
                case 'File':
                    $options = $this->fgc('Codes/file-input');
                    break;
                case 'Switch':
                    $options = $this->fgc('Codes/switch-input');
                    break;
            }

            $fileInputOptsTemp = self::sp('#TCKEY' , $col, $fileInputOptsTemp);
            $fileInputOptsTemp = self::sp('#TCINPUTOPTS' , $options, $fileInputOptsTemp);
            $fileInputTemp = self::sp('#TCINPUTOPTS' , $fileInputOptsTemp, $fileInputTemp);

            $fieldRow[] = $fileInputTemp;
        }

        $fieldRow = implode(','.PHP_EOL, $fieldRow);
        $file = $this->sp('#TCFIELDROW', $fieldRow, $file);

        $langFieldRow = '';
        if ($this->hasLanguage()) {
            // @TODO
        }
        $file = $this->sp('#TCLANGFIELDROW', $langFieldRow, $file);

        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupTableConfig($curDir)
    {
        $fileName = 'table.config.php';
        $file = self::fgc('Config/'.$fileName);

        $tblCols = self::fgc('Codes/tbl-cols');

        // Table columns
        $tblColumns = [];
        // Table searchable columns
        $searchableColumns = [];

        // Dividing length of table to several columns
        $colWidth = number_format(100/count($this->config['step4']['tcf-db-table-cols']), 0);
        foreach ($this->config['step4']['tcf-db-table-cols'] As $key => $col){

            // Primary column use to update and delete raw entry
            $priCol = ($col == self::getMainTablePk()) ? 'DT_RowId' : $col;

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
        $file = self::fgc('Entities/Model.php');

        $table = $this->config['step3']['tcf-db-table'];

        $entityName = self::makeEntityName($table);
        $primaryKey = $this->getTablePK($table);

        $fillable = [];

        foreach ($this->config['step5']['tcf-db-table-col-editable'] As $col)
            if ($col !== self::getMainTablePk())
                array_push($fillable, '\''.$col.'\'');

        $fillable = implode(', '.PHP_EOL."\t\t", $fillable);


        $fileInput = [];
        $fileUpload = $this->fgc('Codes/file-upload');
        foreach ($this->config['step5']['tcf-db-table-col-type'] As $key => $input){
            $temp = $fileUpload;
            if ($input == 'File')
                $fileInput[] = $this->sp('#TCKEY', $this->config['step5']['tcf-db-table-col-editable'][$key], $temp);
        }

        $fileInput = ($fileInput) ? implode(PHP_EOL, $fileInput): '';

        $storeFx = $this->fgc('Codes/store-file');

        $callStoreFx = ($fileInput) ? '$this->storeFile();' : '';
        $storeFx = ($fileInput) ? $this->sp('#TCFILEUPLOAD', $fileInput, $storeFx) : '';

        $file = self::sp(
            ['ModelName', '#TCTABLE', '#TCKEYNAME', '#TCFILLABLE', '#TCCALLSTOREFILE', '#TCSTOREFILEFUNCTION'],
            [$entityName, $table, $primaryKey, $fillable, $callStoreFx, $storeFx],
            $file
        );

        $modelRelation = '';
        $selectQry = '';
        $selectQryFx = '';

        $tblColDisplayFilters = [];
        foreach ($this->config['step4']['tcf-db-table-cols'] As $key => $col){
            if (is_bool(strpos($col, 'tclangtblcol_')) && $this->config['step4']['tcf-db-table-col-display'][$key] != 'raw_view'){
                $tblColDisplayFilters[] = $this->sp(
                    ['#TCKEY', '#TCCOLDISPLAY'],
                    [$col, $this->config['step4']['tcf-db-table-col-display'][$key]],
                    $this->fgc('/Codes/tbl-col-display-filter')
                );
            }
        }

        $displayColsTbl = $this->fgc('Codes/tbl-display-filter');
        $tableColsDisplay = ($tblColDisplayFilters) ? $this->sp('#TCTABLECOLS', implode(PHP_EOL, $tblColDisplayFilters), $displayColsTbl) : '';

        if (!$this->hasLanguage()) {
            $selectQry = $this->fgc('Codes/table-get-list');
        } else {
            // @TODO
        }

        $file = $this->sp(
            ['#TCLANGRELATION', '#TCSELECT', '#TCDISPLAYTABLECOLS', '#TCJOINMETHODS'],
            [$modelRelation, $selectQry, $tableColsDisplay, $selectQryFx],
            $file
        );

        $this->generateModuleFile($entityName.'.php', $curDir, $file);
    }

    public function setupEvents($curDir)
    {
        $fileName = 'SaveFormEvent.php';
        $file = self::fgc('Events/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);

        $fileName = 'DeleteItemEvent.php';
        $file = self::fgc('Events/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupControllers($curDir)
    {
        $fileName = 'IndexController.php';
        $file = self::fgc('Controllers/'.$fileName);

        $table = $this->config['step3']['tcf-db-table'];
        $entityName = self::makeEntityName($table);

        // #TCTOOLTYPEEDTION
        $toolForm = '';
        if ($this->isDbTool()) {

            if ($this->isModalTypeTool()) {

                $toolForm = $this->fgc('Codes/form-modal');

                $langQuery = ($this->hasLanguage()) ? $this->fgc('Codes/cms-lang-query') : '';

                $toolForm = $this->sp('#TCLANGCMS', $langQuery, $toolForm);
            } else {
                // @TODO for tabulation
            }
        }

        $file = $this->sp('#TCTOOLTYPEEDTION', $toolForm, $file);

        $file = self::sp('ModelName', $entityName, $file);

        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupRequests($curDir)
    {
        $file = self::fgc('Requests/Request.php');

        $table = $this->config['step3']['tcf-db-table'];
        $entityName = self::makeEntityName($table);

        $requiredCols = [];
        $requiredColsMsg = [];

        foreach ($this->config['step5']['tcf-db-table-col-required'] As $key => $col)
            if ($col !== self::getMainTablePk()){

                if ($this->config['step5']['tcf-db-table-col-type'][$key] !== 'File'){
                    array_push($requiredCols, '\''.$col.'\' => \'required\'');
                }

                array_push($requiredColsMsg, '\''.$col.'.required\' => __(\'moduletpl::messages.input_required\')');
            }

        $requiredFileInput = [];

        foreach ($this->config['step5']['tcf-db-table-col-editable'] As $key => $col)
            if ($this->config['step5']['tcf-db-table-col-type'][$key] == 'File'){

                if (in_array($col, $this->config['step5']['tcf-db-table-col-required'])){
                    $requiredFileInput[] = '$rules[\''. $col .'\'] = (request()->hasFile(\''. $col .'\')  || is_null($hasID)) ? $withRequired : $notRequired;';
                }else{
                    array_push($requiredCols, '\''.$col.'\' => \'file|image|max:3000\'');
                }

                array_push($requiredColsMsg, '\''.$col.'.max\' => __(\'moduletpl::messages.file_max_size\')');
                array_push($requiredColsMsg, '\''.$col.'.uploaded\' => __(\'moduletpl::messages.failed_upload\')');
            }

        $fileUploadRequired = $this->fgc('Codes/file-upload-rules');

        $fileUploadRequired = (!empty($requiredFileInput)) ? $fileUploadRequired.PHP_EOL."\t\t".implode("\t\t".PHP_EOL, $requiredFileInput) : '';

        $requiredCols = implode(', '.PHP_EOL."\t\t\t", $requiredCols);
        $requiredColsMsg = implode(', '.PHP_EOL."\t\t\t", $requiredColsMsg);

        $file = self::sp(
            ['ModelName', '#TCCOLSRULES', '#TCCOLSMGS', '#TCREQUIREDFILE'],
            [$entityName, $requiredCols, $requiredColsMsg, $fileUploadRequired],
            $file
        );

        $this->generateModuleFile($entityName.'Request.php', $curDir, $file);
    }

    private function setupListeners($curDir)
    {
        $table = $this->config['step3']['tcf-db-table'];
        $entityName = self::makeEntityName($table);

        $fileName = 'SaveFormRequest.php';
        $file = self::fgc('Listeners/'.$fileName);

        $langRequest = '';
        if ($this->hasLanguage()) {
            // @TODO
        }
        $file = self::sp('#TCLANGREQUEST', $langRequest, $file);
        $file = self::sp('ModelName', $entityName, $file);

        $this->generateModuleFile($fileName, $curDir, $file);


        $fileName = 'DeleteItemRequest.php';
        $file = self::fgc('Listeners/'.$fileName);

        $langRequest = '';
        if ($this->hasLanguage()) {
            // @TODO
        }
        $file = self::sp('#TCLANGREQUEST', $langRequest, $file);
        $file = self::sp('ModelName', $entityName, $file);

        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupProviders($curDir)
    {
        $fileName = 'ModuleServiceProvider.php';
        $file = self::fgc('Providers/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);

        $fileName = 'RouteServiceProvider.php';
        $file = self::fgc('Providers/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);

        $fileName = 'EventServiceProvider.php';
        $file = self::fgc('Providers/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupLang($curDir)
    {
        $coreLang = DB::table('melis_core_lang')->get();
        $commonTransTpl = require __DIR__.'/template/Resources/lang/messages.php';

        $currentLocale = app()->getLocale();

        // Common translation
        $commonTranslations = [];
        foreach ($coreLang As $lang){
            $tempLocale = explode('_', $lang->lang_locale)[0];
            app()->setLocale($tempLocale);

            foreach ($commonTransTpl As $cText)
                $commonTranslations[$lang->lang_locale][$cText] = Lang::get('melisLaravel::messages.'.$cText);

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
        $stepTexts = array_merge_recursive($this->config['step2'], $commonTranslations);

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

            $file = self::fgc('Resources/lang/language-tpl.php');
            $file = self::sp('#TCTRANSLATIONS', $strTranslations, $file);

            $locale = explode('_', $locale)[0];

            $langDir = $curDir.'/'.$locale;
            mkdir($langDir);

            $this->generateModuleFile('messages.php', $langDir, $file);
        }
    }

    private function setupViews($curDir)
    {
        if (!$this->hasLanguage())
            $fileName = 'form.blade.php';
        else
            $fileName = 'lang-form.blade.php';

        $file = self::fgc('Resources/views/'.$fileName);
        $this->generateModuleFile(str_replace('lang-', '', $fileName), $curDir, $file);

        $fileName = 'index.blade.php';
        $file = self::fgc('Resources/views/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupRoutes($curDir)
    {
        $fileName = 'api.php';
        $file = self::fgc('Routes/'.$fileName);
        $this->generateModuleFile($fileName, $curDir, $file);

        $fileName = 'web.php';
        $file = self::fgc('Routes/'.$fileName);

        if ($this->isModalTypeTool()) {
            $route = 'Route::get(\'/form/{id?}\', \'IndexController@form\');';
        } else {
            $route = 'Route::get(\'/tool-properties/{id?}\', \'IndexController@properties\');';
        }
        $file = $this->sp('#TCTOOLTYPE', $route, $file);
        $this->generateModuleFile($fileName, $curDir, $file);
    }

    private function setupJs()
    {
        $fileName = 'tool.js';
        $file = self::fgc('Resources/assets/js/'.$fileName);

        $zendModule = __DIR__ .'/../../../../module/';
        $moduleAssetsDir = $zendModule.DIRECTORY_SEPARATOR.$this->moduleName().DIRECTORY_SEPARATOR.'public/js';

        $langFromData = '';
        if ($this->hasLanguage()) {
            #TODO
        }
        $file = $this->sp('#TCLANGDATAFORM', $langFromData, $file);

        $this->generateModuleFile($fileName, $moduleAssetsDir, $file);
    }

    private function moduleJson()
    {
        $fileName = 'module.json';
        $file = self::fgc($fileName);
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

//        if ($this->hasLanguage())
//            $fileContent = $this->sp('tclangtblcol_', '', $fileContent);

        $targetFile = $targetDir.'/'.$fileName;
        if (!file_exists($targetFile)){
            $targetFile = fopen($targetFile, 'x+');
            fwrite($targetFile, $fileContent);
            fclose($targetFile);
        }
    }

    private function moduleName()
    {
        return self::makeModuleName($this->config['step1']['tcf-name']);
    }

    function getMainTablePk()
    {
        $table = $this->config['step3']['tcf-db-table'];
        return self::getTablePK($table);
    }

    function getTablePK($table)
    {
        $selectedTbl = $this->describeTable($table);

        foreach ($selectedTbl As $col)
            if ($col->Key == 'PRI' && $col->Extra == 'auto_increment')
                return $col->Field;

        return null;
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

    private function isModalTypeTool()
    {
        return $this->config['step1']['tcf-tool-edit-type'] == 'modal' ? true : false;
    }



    private function hasLanguage()
    {
        return ($this->config['step3']['tcf-db-table-has-language']) ? true : false;
    }

}