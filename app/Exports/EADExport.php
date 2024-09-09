<?php

namespace App\Exports;

use App\Models\Slip;
use SimpleXMLElement;

class EADExport
{
    protected $xml;

    public function __construct()
    {
        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ead xmlns="urn:isbn:1-931666-22-9" xmlns:xlink="http://www.w3.org/1999/xlink"></ead>');
    }

    public function export($slips)
    {
        $this->addEadHeader();
        $this->addArchDesc($slips);

        return $this->xml->asXML();
    }

    protected function addEadHeader()
    {
        $eadheader = $this->xml->addChild('eadheader');
        $eadheader->addAttribute('countryencoding', 'iso3166-1');
        $eadheader->addAttribute('dateencoding', 'iso8601');
        $eadheader->addAttribute('langencoding', 'iso639-2b');
        $eadheader->addAttribute('repositoryencoding', 'iso15511');
        $eadheader->addAttribute('scriptencoding', 'iso15924');

        $eadheader->addChild('eadid', 'EAD_Export_' . time());

        $filedesc = $eadheader->addChild('filedesc');
        $titlestmt = $filedesc->addChild('titlestmt');
        $titlestmt->addChild('titleproper', 'Export des bordereaux de versement');
        $titlestmt->addChild('author', 'Système d\'archivage');

        $publicationstmt = $filedesc->addChild('publicationstmt');
        $publicationstmt->addChild('publisher', 'Service d\'archives');
        $publicationstmt->addChild('date', date('Y-m-d'));
    }

    protected function addArchDesc($slips)
    {
        $archdesc = $this->xml->addChild('archdesc');
        $archdesc->addAttribute('level', 'collection');

        $did = $archdesc->addChild('did');
        $did->addChild('unittitle', 'Collection des bordereaux de versement');
        $did->addChild('unitdate', date('Y-m-d'));
        $did->addChild('unitid', 'COL-BV-' . date('Ymd'));

        foreach ($slips as $slip) {
            $this->addComponent($archdesc, $slip);
        }
    }

    protected function addComponent($parent, $slip)
    {
        $c = $parent->addChild('c');
        $c->addAttribute('level', 'item');

        $did = $c->addChild('did');
        $did->addChild('unittitle', $slip->name);
        $did->addChild('unitid', $slip->code);
        $did->addChild('unitdate', $slip->created_at->format('Y-m-d'));
        $did->addChild('physdesc')->addChild('extent', '1 bordereau');

        $scopecontent = $c->addChild('scopecontent');
        $scopecontent->addChild('p', $slip->description);

        $odd = $c->addChild('odd');
        $odd->addChild('head', 'Informations supplémentaires');
        $odd->addChild('p', 'Créé par: ' . $slip->user->name);

        // Ajoutez d'autres éléments selon vos besoins
    }
}
