<?php

namespace LeKoala\Crm;

use SilverStripe\Security\Member;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;

/**
 * A simple CRM admin
 *
 */
class CrmAdmin extends ModelAdmin
{
    private static $managed_models = [
        Company::class,
        Member::class
    ];
    private static $url_segment = 'crm';
    private static $menu_title = 'CRM';
    private static $menu_icon_class = 'font-icon-torsos-all';
    private static $page_length = 18;

    public static function isCurrentController(): bool
    {
        return Controller::has_curr() && Controller::curr() instanceof CrmAdmin;
    }

    public function getList()
    {
        $list = parent::getList();

        switch ($this->modelClass) {
            case Company::class:
                break;
            case Member::class:
                // Required for summaryFields
                $list = $list->innerJoin('Company_Persons', 'Company_Persons.MemberID = Member.ID')->eagerLoad('Companies');
                break;
        }

        return $list;
    }

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();

        switch ($this->modelClass) {
            case Company::class:
                break;
            case Member::class:
                // see https://github.com/silverstripe/silverstripe-framework/issues/11589
                $config = $config->removeComponentsByType(GridFieldDeleteAction::class);
                break;
        }

        return $config;
    }
}
