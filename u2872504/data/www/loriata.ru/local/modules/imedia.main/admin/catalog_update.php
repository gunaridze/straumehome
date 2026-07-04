<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Catalog\Service\ImportProperties;

$module_id = "imedia.main";

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "modules/main/options.php");
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/local/modules/imedia.main/options.php");
Loc::loadMessages(__FILE__);

$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage("IMEDIA_MAIN_GLOBAL_MENU_CATALOG_UPDATE_TITLE"));

if ($APPLICATION->GetGroupRight($module_id) < "S") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loader::includeModule($module_id);

Extension::load('ui.vue');
Extension::load('ui.progressbar');
Extension::load('ui.alerts');
Extension::load('ui.buttons.icons');

$aTabs = [
    [
        "DIV" => "edit1",
        "TAB" => 'Импорт',
        "ICON" => "iblock",
        "TITLE" => 'Импорт',
        "OPTIONS" => [
            [
                'catalog_update_import_file',
                '',
                '',
                ['text', 30]
            ],
            [
                'catalog_update_import_delimiter',
                '',
                '',
                ['text', 30]
            ],
            [
                'catalog_update_properties',
                '',
                '',
                ['text', 30]
            ],
            [
                'catalog_update_limit_time',
                '',
                '',
                ['text', 4]
            ],
            [
                'catalog_update_limit_items',
                '',
                '',
                ['text', 4]
            ]
        ]
    ]
];

$request = Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

$currentPage = $APPLICATION->GetCurPage()
    . '?mid='
    . htmlspecialcharsbx($request["mid"])
    . '&amp;lang='
    . $request["lang"]
;

if (
    $request->isPost()
    && check_bitrix_sessid()
){
    if(
        $request->getPost('update')
        || $request->getPost('import')
    ){
        foreach ($aTabs as $aTab){
            foreach ($aTab["OPTIONS"] as $arOption){
                if (!is_array($arOption)){
                    continue;
                }

                if ($arOption["note"]){
                    continue;
                }

                $optionName = $arOption[0];
                $optionValue = $request->getPost($optionName);

                Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }
        }

        if($request->getPost('import')){

            $filepath = $_SERVER['DOCUMENT_ROOT'] . Option::get($module_id, 'catalog_update_import_file');
            $delimiter = Option::get($module_id, 'catalog_update_import_delimiter');

            $result = ImportProperties::process($filepath, $delimiter);
            $data = $result->getData();

            \CAdminMessage::ShowMessage(
                [
                    'MESSAGE' => Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_IMPORT_TOTAL', [
                        '#TOTAL#' => $data['TOTAL']
                    ]),
                    'TYPE' => 'OK'
                ]
            );

            if(!($result->isSuccess())){
                \CAdminMessage::ShowMessage(
                    [
                        'MESSAGE' => implode(', ', $result->getErrorMessages()),
                        'TYPE' => 'ERROR'
                    ]
                );
            }

        }

    }
}

$propertiesConfig = Option::get($module_id, 'catalog_update_properties');
$currentConfig = [];
if($propertiesConfig){
    $propertiesConfig = Json::decode($propertiesConfig);
    foreach($propertiesConfig as $property){
        if($property['VALUE']){
            $currentConfig[$property['ID']] = $property['VALUE'];
        }
    }
}

$catalogProperties = [];

$fields = [
    'DETAIL_PICTURE',
    'DETAIL_TEXT'
];

foreach($fields as $field){

    $id = 'FIELD_' . $field;

    $catalogProperties[] = [
        'id' => $id,
        'name' => Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_' . $id),
        'value' => $currentConfig[$id]
    ];

}

$query = PropertyTable::getList(
    [
        'select' => ['CODE', 'NAME'],
        'filter' => [
            '=IBLOCK_ID' => IblockHelper::getId('CATALOG'),
            [
                'LOGIC' => 'OR',
                [
                    '=PROPERTY_TYPE' => [
                        PropertyTable::TYPE_STRING,
                        PropertyTable::TYPE_NUMBER
                    ],
                    '=MULTIPLE' => false
                ],
                [
                    '=PROPERTY_TYPE' => PropertyTable::TYPE_FILE
                ]
            ]

        ],
        'order' => [
            'NAME' => 'ASC'
        ]
    ]
);
while($row = $query->fetch()){
    $catalogProperties[] = [
        'id' => $row['CODE'],
        'name' => $row['NAME'] . ' ['.$row['CODE'].']',
        'value' => $currentConfig[$row['CODE']]
    ];
}
?>
<form action="<?= $currentPage ?>" method="post" name="imedia_main_catalog_update">
    <div id="app">
        <?php
        $tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_FILE')?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                        type="text"
                        name="catalog_update_import_file"
                        value="<?= htmlspecialcharsbx(Option::get($module_id, "catalog_update_import_file")); ?>"
                        size="30"
                >
                <input type="button" value="<?=Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_SELECT')?>" OnClick="BtnClick()">
                <?php CAdminFileDialog::ShowScript(
                    [
                        "event" => "BtnClick",
                        "arResultDest" => [
                            "FORM_NAME" => "imedia_main_catalog_update",
                            "FORM_ELEMENT_NAME" => "catalog_update_import_file"
                        ],
                        "arPath" => [
                            "SITE" => SITE_ID,
                            "PATH" => "/".Option::get("main", "upload_dir", "upload")
                        ] ,
                        "select" => 'F',
                        "operation" => 'O',
                        "showUploadTab" => true,
                        "showAddToMenuTab" => false,
                        "fileFilter" => 'csv',
                        "allowAllFiles" => true,
                        "SaveConfig" => true
                    ]
                );
                ?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label><?=Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_DELIMITER')?>:</label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                        type="text"
                        name="catalog_update_import_delimiter"
                        size="30"
                        value="<?=Option::get($module_id, "catalog_update_import_delimiter")?>"
                >
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label><?=Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_LIMIT_TIME')?>:</label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                        type="text"
                        name="catalog_update_limit_time"
                        size="4"
                        value="<?=Option::get($module_id, "catalog_update_limit_time")?>"
                >
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label><?=Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_LIMIT_ITEMS')?>:</label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                        type="text"
                        name="catalog_update_limit_items"
                        size="4"
                        value="<?=Option::get($module_id, "catalog_update_limit_items")?>"
                >
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?=Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_PROPERTIES')?></td>
        </tr>
        <tr>
            <td colspan="2">
                <properties :properties='<?=Json::encode($catalogProperties)?>'></properties>
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?=Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_PROCESS')?></td>
        </tr>
        <tr>
            <td colspan="2">
                <update-properties></update-properties>
            </td>
        </tr>
        <?php $tabControl->EndTab();?>
        <?php $tabControl->Buttons();?>
        <input type="submit" name="import" value="<?=Loc::getMessage('IMEDIA_MAIN_CATALOG_UPDATE_IMPORT')?>">
        <input type="submit" name="update" value="<?=Loc::getMessage("MAIN_SAVE")?>">
        <input type="reset" name="reset" value="<?=Loc::getMessage("MAIN_RESET")?>">
        <?= bitrix_sessid_post() ?>
        <?php $tabControl->End(); ?>
        <script>
            BX.Vue.component('update-properties', {
                data(){
                    return {
                        loading: false,
                        inProgress: false,
                        isDone: false,
                        error: null,
                        total: 0
                    }
                },
                computed: {
                    displayProcessStatus(){

                        if(this.inProgress){
                            return `Идет процесс обновления свойств. Обработано записей: ${this.total}`;
                        }

                        if(this.isDone){
                            return `Обновление свойств завершено. Обработано записей: ${this.total}`;
                        }

                        return '-';

                    }
                },
                methods: {

                    async run(){

                        if(this.loading){
                            return false;
                        }

                        this.loading = true;
                        this.inProgress = true;
                        this.isDone = false;
                        this.total = 0;

                        try{
                            this.inProgress = true;

                            while(this.inProgress){
                                const response = await BX.ajax.runAction('imedia:main.api.updateproperties.process');
                                this.inProgress = +response.data.total > 0;
                                this.total += +response.data.total;
                            }

                            this.isDone = true;

                        } catch (e){
                            console.error(e);

                            if(e.hasOwnProperty('errors')){
                                const errorsData = [];

                                e.errors.forEach(error => {
                                    errorsData.push(error.message);
                                });

                                this.error = errorsData.join(', ');
                            } else{
                                this.error = 'Процесс завершен с ошибкой';
                            }
                        }

                        this.inProgress = false;
                        this.loading = false;

                    }

                },
                template: `
                    <div>
                        <br>
                        <div
                            class="ui-progressbar ui-progressbar-column"
                            :class="[{'ui-progressbar-danger': error},{'ui-progressbar-success': isDone}]"
                        >
                            <div class="ui-progressbar-text-before">Обновление свойств</div>
                            <div class="ui-progressbar-text-after">{{displayProcessStatus}}</div>
                        </div>
                        <div class="ui-alert ui-alert-danger" v-if="error">
                            <span class="ui-alert-message" v-html="error"></span>
                        </div>
                        <br>
                        <div class="ui-btn-container ui-btn-container-center">
                            <button
                                class="ui-btn ui-btn-icon-business ui-btn-primary"
                                :class="[{'ui-btn-disabled': inProgress}, {'ui-btn-wait': inProgress}]"
                                @click.prevent="run"
                                :disabled="inProgress"
                                aria-label="Запустить"
                            >Запустить</button>
                        </div>
                    </div>
                              `
            });

            BX.Vue.component('property', {
                props: {
                    item: {
                        type: Object,
                        required: true
                    }
                },
                template: `
                <tr>
                    <td width="40%" class="adm-detail-content-cell-l">
                        <input type="text" v-model="item.value" :placeholder="item.id">
                    </td>
                    <td width="60%" class="adm-detail-content-cell-r">{{ item.name }}</td>
                </tr>
               `
            });

            BX.Vue.component('properties', {
                props: {
                    properties: {
                        type: Array,
                        default: []
                    }
                },
                data(){
                    return {
                        items: null
                    }
                },
                computed: {
                    json(){

                        const data = [];
                        this.items.forEach(item => {
                            data.push({
                                ID: item.id,
                                VALUE: item.value
                            })
                        });

                        return JSON.stringify(data);
                    }
                },
                created(){
                    this.items = this.properties.slice();
                },
                template: `
                    <div>
                        <table style="width: 100%;">
                            <tbody>
                                <property
                                    v-for="item in items"
                                    :key="item.id"
                                    :item="item"
                                ></property>
                            </tbody>
                        </table>
                        <input type="hidden" :value="json" name="catalog_update_properties">
                    </div>
                `
            });

            BX.Vue.create({
                el: '#app'
            });
        </script>
    </div>
</form>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');