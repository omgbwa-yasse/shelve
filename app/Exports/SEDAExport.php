<?php

namespace App\Exports;

use App\Models\Slip;
use SimpleXMLElement;

class SEDAExport
{
    protected $xml;

    public function __construct()
    {
        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ArchiveTransfer xmlns="fr:gouv:culture:archivesdefrance:seda:v2.1"></ArchiveTransfer>');
    }

    public function export($slips)
    {
        $this->addMessageIdentification();
        $this->addArchivalAgreement();
        $this->addTransferringAgency();
        $this->addArchivalAgency();
        $this->addDataObjectPackage($slips);

        return $this->xml->asXML();
    }

    protected function addMessageIdentification()
    {
        $this->xml->addChild('MessageIdentifier', 'SEDA_Export_' . time());
        $this->xml->addChild('Date', date('Y-m-d\TH:i:s'));
        $this->xml->addChild('MessageIdentifier', 'SEDA_Export_' . time());
    }

    protected function addArchivalAgreement()
    {
        $agreement = $this->xml->addChild('ArchivalAgreement');
        $agreement->addChild('Identifier', 'AG-001');
        $agreement->addChild('Name', 'Accord de versement');
    }

    protected function addTransferringAgency()
    {
        $agency = $this->xml->addChild('TransferringAgency');
        $identifier = $agency->addChild('Identifier', 'TA-001');
        $identifier->addAttribute('scheme', 'SIRET');
        $agency->addChild('Name', 'Agence de transfert');
    }

    protected function addArchivalAgency()
    {
        $agency = $this->xml->addChild('ArchivalAgency');
        $identifier = $agency->addChild('Identifier', 'AA-001');
        $identifier->addAttribute('scheme', 'SIRET');
        $agency->addChild('Name', 'Service d\'archives');
    }

    protected function addDataObjectPackage($slips)
    {
        $package = $this->xml->addChild('DataObjectPackage');
        $descriptiveMetadata = $package->addChild('DescriptiveMetadata');

        foreach ($slips as $slip) {
            $this->addArchiveUnit($descriptiveMetadata, $slip);
        }
    }

    protected function addArchiveUnit($parent, $slip)
    {
        $unit = $parent->addChild('ArchiveUnit');
        $content = $unit->addChild('Content');
        $content->addChild('DescriptionLevel', 'Item');
        $content->addChild('Title', $slip->name);
        $content->addChild('Description', $slip->description);
        $content->addChild('DocumentType', 'Bordereau de versement');

        $eventDateTime = $content->addChild('EventDateTime', $slip->created_at->format('Y-m-d\TH:i:s'));
        $eventDateTime->addAttribute('xml:id', 'EVT-' . $slip->id);

        $management = $unit->addChild('Management');
        $accessRule = $management->addChild('AccessRule');
        $accessRule->addChild('Rule', 'ACC-00001');
        $accessRule->addChild('StartDate', $slip->created_at->format('Y-m-d'));

        // Ajoutez d'autres métadonnées selon vos besoins
    }
}
