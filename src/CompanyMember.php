<?php

namespace LeKoala\Crm;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;

/**
 * Extension point for members that belongs to companies
 *
 * @property \SilverStripe\Security\Member|\LeKoala\Crm\CompanyMember $owner
 * @method \SilverStripe\ORM\ManyManyList<\LeKoala\Crm\Company> Companies()
 */
class CompanyMember extends Extension
{
    private static $db = [];
    private static $belongs_many_many = [
        'Companies' => Company::class
    ];

    public function Fullname()
    {
        return $this->owner->FirstName . ' ' . $this->owner->Surname;
    }

    public function updateCMSFields(FieldList $fields)
    {
        if (CrmAdmin::isCurrentController()) {
            $fields->removeByName('LastVisitedDate');
            $fields->removeByName('Locale');
            $fields->removeByName('FailedLoginCount');
            $fields->removeByName('DirectGroups');
            $fields->removeByName('RequiresPasswordChangeOnNextLogin');
            $fields->removeByName('Password');
            $fields->removeByName('Permissions');

            /** @var GridField $Companies */
            $Companies = $fields->dataFieldByName('Companies');
            if ($Companies) {
                $Companies->getConfig()->removeComponentsByType(GridFieldAddNewButton::class);
                $Companies->getConfig()->removeComponentsByType(GridFieldAddExistingSearchButton::class);
                $Companies->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
                $Companies->getConfig()->removeComponentsByType(GridFieldFilterHeader::class);
            }
        }
    }

    public function CompaniesList()
    {
        return implode(', ', $this->owner->Companies()->column('Title'));
    }

    public function updateSummaryFields(&$fields)
    {
        if (Controller::has_curr() && Controller::curr() instanceof CrmAdmin) {
            $fields['CompaniesList'] = 'CompaniesList';
        }
    }
}
