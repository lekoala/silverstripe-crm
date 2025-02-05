<?php

namespace LeKoala\Crm;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\EmailField;
use LeKoala\PhoneNumber\PhoneField;
use SilverStripe\Forms\HeaderField;
use LeKoala\CmsActions\CustomAction;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Security\Member;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use DragonBe\Vies\Vies;
use SilverStripe\Core\Environment;
use DragonBe\Vies\ViesException;
use DragonBe\Vies\ViesServiceException;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\LiteralField;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

/**
 * Describe a moral person
 *
 * @property ?string $Title
 * @property ?string $StreetAddress
 * @property ?string $StreetAddress2
 * @property ?string $PostalCode
 * @property ?string $Locality
 * @property ?string $CountryCode
 * @property ?string $PhoneNumber
 * @property ?string $MobilePhoneNumber
 * @property ?string $Email
 * @property ?string $Website
 * @property ?string $ContactPerson
 * @property ?string $VatNumber
 * @property ?string $BankAccount
 * @property ?string $Bic
 * @property ?string $Notes
 * @property ?string $ViesData
 * @method \SilverStripe\ORM\DataList<\LeKoala\Invoice\Invoice> Invoices()
 * @method \SilverStripe\ORM\DataList<\LeKoala\Invoice\Offer> Offers()
 * @method \SilverStripe\ORM\DataList<\LeKoala\Invoice\Payment> Payments()
 * @method \SilverStripe\ORM\ManyManyList<\SilverStripe\Security\Member> Persons()
 * @mixin \LeKoala\Invoice\InvoiceCompany
 * @mixin \SilverStripe\Assets\Shortcodes\FileLinkTracking
 * @mixin \SilverStripe\Assets\AssetControlExtension
 * @mixin \SilverStripe\CMS\Model\SiteTreeLinkTracking
 * @mixin \SilverStripe\Versioned\RecursivePublishable
 * @mixin \SilverStripe\Versioned\VersionedStateExtension
 */
class Company extends DataObject
{
    use CrmDefaultPermissions;

    private static $table_name = 'Company';

    private static $db = [
        'Title' => 'Varchar(255)',
        // Address
        'StreetAddress' => 'Varchar(255)',
        'StreetAddress2' => 'Varchar(255)',
        'PostalCode' => 'Varchar(32)',
        'Locality' => 'Varchar(255)',
        'CountryCode' => 'Varchar(2)',
        // Contact
        'PhoneNumber' => 'Varchar(255)',
        'MobilePhoneNumber' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'Website' => 'Varchar(255)',
        'ContactPerson' => 'Varchar(255)',
        // Bank & Taxes
        'VatNumber' => 'Varchar(255)',
        'BankAccount' => 'Varchar(255)',
        'Bic' => 'Varchar(255)',
        // Misc
        'Notes' => 'Text',
        'ViesData' => 'Text',
    ];
    private static $many_many = [
        'Persons' => Member::class,
    ];
    private static $many_many_extraFields = [
        'Persons' => [
            'IsDefault' => 'Boolean',
            'JobTitle' => 'Varchar(255)'
        ]
    ];
    private static $summary_fields = [
        'Title',
        'ContactPerson',
        'CountryCode'
    ];
    private static $searchable_fields = [
        'Title',
        'CountryCode',
        'PostalCode',
        'Email',
        'VatNumber',
        'BankAccount'
    ];

    /**
     * @return array{countryCode:string,vatNumber:string,requestDate:string,valid:bool,name:string,address:string,identifier:string}
     */
    public function queryViesData()
    {
        $vies = new Vies();

        $requesterCountryCode = Environment::getEnv('CRM_REQUESTER_CODE') ?: 'BE';
        $requesterVatId = Environment::getEnv('CRM_REQUESTER_VAT') ?: '0811231190';

        $vatResult = $vies->validateVat(
            $this->CountryCode,
            VatHelper::filterNumber($this->VatNumber, true),
            $requesterCountryCode,
            VatHelper::filterNumber($requesterVatId, true)
        );

        return $vatResult->toArray();
    }

    public function doVies()
    {
        try {
            $data = $this->queryViesData();
            $this->ViesData = json_encode($data);

            // Update any missing data
            if (!$this->Title) {
                $this->Title = $data['name'];
            }
            if (!$this->StreetAddress) {
                $addressParts = explode("\n", $data['address']);
                $this->StreetAddress = $addressParts[0];

                $locality = explode(' ', $addressParts[1], 2);
                $this->PostalCode = $locality[0];
                $this->Locality = $locality[1];
            }

            if ($this->isChanged()) {
                $this->write();
                return 'Info updated from vies';
            }
            return 'Data already up to date';
        } catch (ViesException $viesException) {
            return 'Cannot process VAT validation: ' . $viesException->getMessage();
        } catch (ViesServiceException $viesServiceException) {
            return 'Cannot process VAT validation: ' . $viesServiceException->getMessage();
        }
    }

    public function getCMSActions()
    {
        $fields = parent::getCMSActions();
        if ($this->VatNumber && $this->CountryCode) {
            $fields->push(new CustomAction('doVies', 'Ask Vies'));
        }
        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->VatNumber) {
            $this->VatNumber = VatHelper::filterNumber($this->VatNumber);
        }
        if ($this->BankAccount) {
            $this->BankAccount = VatHelper::filterNumber($this->BankAccount);
        }

        // Import data from first contact
        $firstContact = $this->Persons()->first();
        if (!$this->ContactPerson && $firstContact) {
            $this->ContactPerson = $firstContact->Fullname();
        }
        if (!$this->Email && $firstContact) {
            $this->Email = $firstContact->Email;
        }
        if (!$this->PhoneNumber && $firstContact) {
            $this->PhoneNumber = $firstContact->PhoneNumber;
        }
        if (!$this->MobilePhoneNumber && $firstContact) {
            $this->MobilePhoneNumber = $firstContact->MobilePhoneNumber;
        }
    }

    public function Country()
    {
        return IntlHelper::getCountryNameFromCode($this->CountryCode);
    }

    /**
     * This expects a format like My street, 111, something. , are optionals
     * @return array{street:string,num:string}
     */
    public function SplitStreetAddress()
    {
        return AddressHelper::splitAddress($this->StreetAddress);
    }

    public function FullAddress()
    {
        $html = $this->StreetAddress . '<br/>';
        if ($this->StreetAddress2) {
            $html .= $this->StreetAddress2 . '<br/>';
        }
        $html .= $this->PostalCode . ' - ' . $this->Locality . '<br/>';
        $html .= $this->Country();
        return $html;
    }

    public function validate()
    {
        $result = parent::validate();

        if ($this->CountryCode && $this->VatNumber) {
            if (!VatHelper::isValid($this->VatNumber, $this->CountryCode)) {
                $result->addError('VAT Number is not valid in country ' . $this->CountryCode);
            }
        }

        return $result;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Headers
        $fields->insertBefore('StreetAddress', new HeaderField('HeaderAddress', 'Address'));
        $fields->insertBefore('PhoneNumber', new HeaderField('HeaderContact', 'Contact'));
        $fields->insertBefore('VatNumber', new HeaderField('HeaderBank', 'Bank & Taxes'));
        $fields->insertBefore('Notes', new HeaderField('HeaderNotes', 'Miscellaneous'));

        // Fields replacements
        $fields->replaceField('CountryCode', $CountryCode = new DropdownField('CountryCode', 'Country'));
        $CountryCode->setSource(IntlHelper::getCountries());
        $fields->replaceField('Email', new EmailField('Email', 'Email'));

        if (class_exists('LibPhoneNumberField')) {
            $fields->replaceField('PhoneNumber', $PhoneNumber = new PhoneField('PhoneNumber', 'Phone Number'));
            $fields->replaceField('FaxPhoneNumber', $FaxPhoneNumber = new PhoneField('FaxPhoneNumber', 'Fax Phone Number'));
            $fields->replaceField('MobilePhoneNumber', $MobilePhoneNumber = new PhoneField('MobilePhoneNumber', 'Mobile Phone Number'));

            $PhoneNumber->setCountryField('CountryCode');
            $FaxPhoneNumber->setCountryField('CountryCode');
            $MobilePhoneNumber->setCountryField('CountryCode');
        }

        // Fields usabillity
        // Persons
        /** @var GridField $personsGridfield */
        $personsGridfield = $fields->dataFieldByName('Persons');
        if ($personsGridfield) {
            $personsGridfieldConfig = $personsGridfield->getConfig();
            $personsGridfieldConfig->removeComponentsByType(GridFieldDataColumns::class);
            $personsGridfieldConfig->removeComponentsByType(GridFieldDeleteAction::class);
            $personsGridfieldConfig->addComponent($editableCols = new GridFieldEditableColumns());

            $editableCols->setDisplayFields(array(
                'FirstName' => function ($record, $column, $grid) {
                    return new TextField($column);
                },
                'Surname' => function ($record, $column, $grid) {
                    return new TextField($column);
                },
                'Email' => function ($record, $column, $grid) {
                    return new TextField($column);
                },
                'JobTitle' => function ($record, $column, $grid) {
                    return new TextField($column);
                },
                'IsDefault' => function ($record, $column, $grid) {
                    return new CheckboxField($column);
                },
            ));

            $personsGridfieldConfig->removeComponentsByType(GridFieldSortableHeader::class);
            $personsGridfieldConfig->removeComponentsByType(GridFieldFilterHeader::class);
            $personsGridfieldConfig->removeComponentsByType(GridFieldTitleHeader::class);
        }

        if (isset($_GET['debug_vies'])) {
            $fields->addFieldToTab('Root.Vies', new LiteralField('Vies', '<pre>' . json_encode($this->queryViesData(), JSON_PRETTY_PRINT) . '</pre>'));
        }

        $fields->makeFieldReadonly('ViesData');

        return $fields;
    }
}
