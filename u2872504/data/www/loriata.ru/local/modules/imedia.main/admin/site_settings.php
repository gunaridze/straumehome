<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\UI\Extension;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

$module_id = "imedia.main";

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "modules/main/options.php");
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/local/modules/imedia.main/options.php");
Loc::loadMessages(__FILE__);

$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage("IMEDIA_MAIN_GLOBAL_MENU_SITE_SETTINGS_TITLE"));

if ($APPLICATION->GetGroupRight($module_id) < "S") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loader::includeModule($module_id);

Extension::load('ui.vue');
Extension::load('ui.progressbar');
Extension::load('ui.alerts');
Extension::load('ui.buttons.icons');

$catalogProperties = [];
$query = PropertyTable::getList(
    [
        'select' => ['CODE', 'NAME'],
        'filter' => [
            '=IBLOCK_ID' => IblockHelper::getId('CATALOG')
        ],
        'order' => [
            'NAME' => 'ASC'
        ]
    ]
);
while($row = $query->fetch()){
    $catalogProperties[ $row['CODE'] ] = $row['NAME'] . ' ['.$row['CODE'].']';
}

$request = Bitrix\Main\HttpApplication::getInstance()
    ->getContext()
    ->getRequest();

$aTabs = [
    [
        "DIV" => "edit0",
        "TAB" => Loc::getMessage("IMEDIA_MAIN_TAB_SITE_SETTINGS"),
        "OPTIONS" => [
            'Отложенные товары',
            [
                'deffered_products_keep_days',
                'Время хранения (дней)',
                '',
                ['text', 4]
            ],
            [
                'product_main_properties',
                'Основные свойства товара',
                '',
                ['multiselectbox', $catalogProperties]
            ],
            'Региональность',
            [
                'default_location',
                'Местоположение по умолчанию',
                '',
                ['location', 30],
            ]
        ],
    ],
    [
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("IMEDIA_MAIN_TAB_SETTINGS"),
        "OPTIONS" => [
            Loc::getMessage("IMEDIA_MAIN_DEBUG_OPTIONS"),
            [
                "debug_view_all",
                Loc::getMessage("IMEDIA_MAIN_DEBUG_VIEW_ALL_TITLE"),
                'N',
                ['checkbox', "Y"],
            ]
        ],
    ],
    [
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"),
    ],
];

if ($request->isPost() && $request["update"] && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        foreach ($aTab["OPTIONS"] as $arOption) {
            if (!is_array($arOption)) {
                continue;
            }

            if ($arOption["note"]) {
                continue;
            }

            $optionName = $arOption[0];
            $optionValue = $request->getPost($optionName);

            switch ($optionName) {
                default:
                    break;
            }

            Option::set(
                $module_id,
                $optionName,
                is_array($optionValue) ? implode(",", $optionValue) : $optionValue
            );
        }
    }
}

$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>
  <form action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($request["mid"]) ?>&amp;lang=<?= $request["lang"] ?>" method="post" name="imedia_main_settings">
      <?php
      $tabControl->Begin();
      foreach ($aTabs as $aTab) {
          if ($aTab["OPTIONS"]) {
              $tabControl->BeginNextTab();
              CImedia::__AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
          }

          if($aTab['DIV'] === 'edit0'){
              ?>
              <tr class="heading">
                  <td colspan="2">Каталог</td>
              </tr>
              <tr>
                  <td colspan="2">
                      <div id="app">
                          <update-prices></update-prices>
                      </div>
                      <script>
                          BX.Vue.component('update-prices', {
                              data(){
                                  return {
                                      loading: false,
                                      inProgress: false,
                                      isDone: false,
                                      types: [],
                                      currentType: null,
                                      error: null
                                  }
                              },
                              computed: {
                                  displayProcessStatus(){

                                      if(this.inProgress){
                                          return 'Идет процесс обновления цен';
                                      }

                                      if(this.isDone){
                                          return 'Обновление цен завершено';
                                      }

                                      return '-';

                                  },
                                  styleProgressBar(){

                                      let width = 0;

                                      if(
                                          this.types.length
                                          && (+this.currentType > 0)
                                      ){
                                          const percent = this.types.length * .01;
                                          width = Math.round((this.types.indexOf(+this.currentType) + 1) / percent)
                                      }

                                      return {
                                          width: `${width}%`
                                      }
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
                                      this.currentType = null;

                                      try{

                                          let response = await BX.ajax.runAction('imedia:main.api.updateprices.run');

                                          this.types = response.data.types.slice();
                                          this.currentType = response.data.currentType;
                                          this.inProgress = response.data.inProgress;

                                          while(this.inProgress){
                                              response = await BX.ajax.runAction('imedia:main.api.updateprices.process');

                                              this.currentType = response.data.currentType;
                                              this.inProgress = response.data.inProgress;
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
                                        <div class="ui-progressbar-text-before">Обновление цен</div>
                                        <div class="ui-progressbar-track">
                                            <div class="ui-progressbar-bar" :style="styleProgressBar"></div>
                                        </div>
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

                          BX.Vue.create({
                              el: '#app'
                          });
                      </script>
                  </td>
              </tr>
              <?php
          }

      }

      $tabControl->BeginNextTab();
      require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");
      $tabControl->Buttons();
      ?>
    <input type="submit" name="update" value="<?= Loc::getMessage("MAIN_SAVE") ?>">
    <input type="reset" name="reset" value="<?= Loc::getMessage("MAIN_RESET") ?>">
    <?= bitrix_sessid_post() ?>
    <?php $tabControl->End(); ?>
  </form>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');