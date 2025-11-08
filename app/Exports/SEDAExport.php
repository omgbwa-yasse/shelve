<?php

namespace App\Exports;

use App\Models\Slip;
use App\Models\RecordPhysical;
use App\Models\SlipRecord;
use App\Models\Attachment;
use SimpleXMLElement;

class SEDAExport
{
    protected $xml;
    private const DATE_TIME_FMT = 'Y-m-d\\TH:i:s';

    public function __construct()
    {
        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ArchiveTransfer xmlns="fr:gouv:culture:archivesdefrance:seda:v2.1"></ArchiveTransfer>');
    }

    public function export($slips)
    {
    // MessageType
    $this->addComment();
    $this->addMessageIdentification();

    // BusinessMessageType
    $this->addArchivalAgreement();
    $this->addCodeListVersions();

    // BusinessRequestMessageType (ArchiveTransfer)
    $this->addArchivalAgency();
    $this->addControlAuthority();
    $this->addDerogation();
    $this->addRequester();
    $this->addOriginatingAgency();
    $this->addTransferringAgency();
    $this->addUnitIdentifiers($slips);

    // Payload
    $this->addDataObjectPackage($slips);

        return $this->xml->asXML();
    }

    public function exportRecords($records)
    {
        // MessageType
        $this->addComment();
        $this->addMessageIdentification();

        // BusinessMessageType
        $this->addArchivalAgreement();
        $this->addCodeListVersions();

        // BusinessRequestMessageType (ArchiveTransfer)
        $this->addArchivalAgency();
        $this->addControlAuthority();
        $this->addDerogation();
        $this->addRequester();
        $this->addOriginatingAgency();
        $this->addTransferringAgency();
        $this->addUnitIdentifiersForRecords($records);

        // Payload
        $this->addDataObjectPackageForRecords($records);

        return $this->xml->asXML();
    }

    protected function addMessageIdentification()
    {
        $this->xml->addChild('MessageIdentifier', 'SEDA_Export_' . time());
        $this->xml->addChild('Date', date('Y-m-d\TH:i:s'));
    }

    protected function addComment()
    {
        $this->xml->addChild('Comment', 'Export SEDA 2.1 généré par l’application');
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

    protected function addControlAuthority()
    {
        // Autorité de contrôle (obligatoire dans les messages de type demande)
        $authority = $this->xml->addChild('ControlAuthority');
        $identifier = $authority->addChild('Identifier', 'CA-001');
        $identifier->addAttribute('scheme', 'SIRET');
        $authority->addChild('Name', 'Autorité de contrôle');
    }

    protected function addRequester()
    {
        // Demandeur (obligatoire dans les messages de type demande)
        $requester = $this->xml->addChild('Requester');
        $identifier = $requester->addChild('Identifier', 'RQ-001');
        $identifier->addAttribute('scheme', 'SIRET');
        $requester->addChild('Name', 'Demandeur');
    }

    protected function addOriginatingAgency()
    {
        // Acteur SEDA: service producteur au sens transport (différent des métadonnées descriptives)
        $agency = $this->xml->addChild('OriginatingAgency');
        $identifier = $agency->addChild('Identifier', 'OA-001');
        $identifier->addAttribute('scheme', 'SIRET');
        $agency->addChild('Name', 'Service producteur');
    }

    protected function addDerogation()
    {
        // Dérogation (obligatoire: indique si une procédure de dérogation est nécessaire)
        $this->xml->addChild('Derogation', 'false');
    }

    protected function addCodeListVersions()
    {
        // CodeListVersions est obligatoire (peut être vide). On fournit quelques références usuelles.
        $lists = $this->xml->addChild('CodeListVersions');
        $lists->addChild('ReplyCodeListVersion', 'ReplyCodeListVersion1.0');
        $lists->addChild('MessageDigestAlgorithmCodeListVersion', 'MessageDigestAlgorithmCodeListVersion1.0');
        $lists->addChild('MimeTypeCodeListVersion', 'MimeTypeCodeListVersion1.0');
        $lists->addChild('FileFormatCodeListVersion', 'FileFormatCodeListVersion1.0');
        $lists->addChild('CompressionAlgorithmCodeListVersion', 'CompressionAlgorithmCodeListVersion1.0');
        $lists->addChild('DataObjectVersionCodeListVersion', 'DataObjectVersionCodeListVersion1.0');
        $lists->addChild('StorageRuleCodeListVersion', 'StorageRuleCodeListVersion1.0');
        $lists->addChild('AppraisalRuleCodeListVersion', 'AppraisalRuleCodeListVersion1.0');
        $lists->addChild('AccessRuleCodeListVersion', 'AccessRuleCodeListVersion1.0');
        $lists->addChild('DisseminationRuleCodeListVersion', 'DisseminationRuleCodeListVersion1.0');
        $lists->addChild('ReuseRuleCodeListVersion', 'ReuseRuleCodeListVersion1.0');
        $lists->addChild('ClassificationRuleCodeListVersion', 'ClassificationRuleCodeListVersion1.0');
        $lists->addChild('AuthorizationReasonCodeListVersion', 'AuthorizationReasonCodeListVersion1.0');
        $lists->addChild('RelationshipCodeListVersion', 'RelationshipCodeListVersion1.0');
        $lists->addChild('EncodingCodeListVersion', 'EncodingCodeListVersion1.0');
    }

    protected function addUnitIdentifiers($slips)
    {
        // Répertorie les identifiants des unités d’archives référencées dans le message de demande
        foreach ($slips as $slip) {
            $hasRecords = false;
            if (method_exists($slip, 'records')) {
                foreach ($slip->records as $record) {
                    $hasRecords = true;
                    $this->xml->addChild('UnitIdentifier', 'AU-R-' . $record->id);
                }
            }
            if (!$hasRecords) {
                // fallback au niveau du slip si aucune unité enfant
                $this->xml->addChild('UnitIdentifier', 'AU-S-' . $slip->id);
            }
        }
    }

    protected function addUnitIdentifiersForRecords($records)
    {
        foreach ($records as $record) {
            $this->xml->addChild('UnitIdentifier', 'AU-R-' . $record->id);
        }
    }

    protected function addDataObjectPackage($slips)
    {
        $package = $this->xml->addChild('DataObjectPackage');
        // Nous placerons d'abord les objets binaires/groupes, puis la description

        // Crée la section de description et de gestion
        $descriptiveMetadata = $package->addChild('DescriptiveMetadata');
        $package->addChild('ManagementMetadata');

        foreach ($slips as $slip) {
            // Pour chaque enregistrement du bordereau, créer les objets de données binaires
            if (!method_exists($slip, 'records')) {
                $this->addArchiveUnit($descriptiveMetadata, $slip, null, null, []);
                continue;
            }

            foreach ($slip->records as $record) {
                [$groupId, $bdoIds] = $this->addDataObjectGroupForRecord($package, $record);
                $this->addArchiveUnit($descriptiveMetadata, $slip, $record, $groupId, $bdoIds);
            }
        }
    }

    protected function addDataObjectPackageForRecords($records)
    {
        $package = $this->xml->addChild('DataObjectPackage');
        $descriptiveMetadata = $package->addChild('DescriptiveMetadata');
        $package->addChild('ManagementMetadata');

        foreach ($records as $record) {
            [$groupId, $bdoIds] = $this->addDataObjectGroupForRecord($package, $record);
            $this->addArchiveUnit($descriptiveMetadata, null, $record, $groupId, $bdoIds);
        }
    }

    protected function addArchiveUnit($parent, $slip, $record = null, $groupId = null, array $bdoIds = [])
    {
        $unit = $parent->addChild('ArchiveUnit');
        // Identifiant AU: si record, utiliser son id, sinon utiliser celui du slip
        $unitId = $record ? ('AU-R-' . $record->id) : ('AU-S-' . $slip->id);
        $unit->addAttribute('id', $unitId);
        $content = $unit->addChild('Content');
        $this->setContentBasics($content, $slip, $record);
        $this->addDatesAndIdentifiersToContent($content, $record);

        // Evénement métier conforme à SEDA 2.1 (Event > EventDateTime)
        $eventDate = $record ? optional($record->created_at)->format(self::DATE_TIME_FMT) : optional($slip->created_at)->format(self::DATE_TIME_FMT);
        $this->addEvent($content, $eventDate ?: date(self::DATE_TIME_FMT));

        // Mots-clés/Tags basés sur les conteneurs ou autres
        if ($record && method_exists($record, 'containers')) {
            foreach ($record->containers as $container) {
                if (!empty($container->code)) {
                    $content->addChild('Tag', $container->code);
                }
            }
        }

        // Mots-clés issus du thésaurus (subject)
        if ($record && method_exists($record, 'thesaurusConcepts')) {
            foreach ($record->thesaurusConcepts as $concept) {
                $label = $concept->preferred_label ?? $concept->notation ?? $concept->uri;
                if (!empty($label)) {
                    $kw = $content->addChild('Keyword');
                    $kw->addChild('KeywordContent', $label);
                    $kw->addChild('KeywordType', 'subject');
                }
            }
        }

        // Relation au parent (IsPartOf)
        if ($record && !empty($record->parent_id)) {
            $rel = $content->addChild('RelatedObjectReference');
            $isPartOf = $rel->addChild('IsPartOf');
            $isPartOf->addChild('ArchiveUnitRefId', 'AU-R-' . $record->parent_id);
        }

        // Références objets de données (groupes et objets)
        $this->addDataObjectReferences($unit, $groupId, $bdoIds);

        $management = $unit->addChild('Management');
        $accessRule = $management->addChild('AccessRule');
    $accessRule->addChild('Rule', 'ACC-00001');
        $startDate = $record ? optional($record->created_at)->format('Y-m-d') : optional($slip->created_at)->format('Y-m-d');
    $accessRule->addChild('StartDate', $startDate ?: date('Y-m-d'));

        // Ajoutez d'autres métadonnées selon vos besoins
    }

    protected function mapDescriptionLevel($levelName)
    {
        // Mappe les niveaux applicatifs vers les niveaux SEDA 2.1
        $map = [
            'fonds' => 'Fonds',
            'subfonds' => 'Subfonds',
            'class' => 'Class',
            'collection' => 'Collection',
            'series' => 'Series',
            'subseries' => 'Subseries',
            'recordgrp' => 'RecordGrp',
            'subgrp' => 'SubGrp',
            'file' => 'File',
            'item' => 'Item',
        ];
        if (empty($levelName)) {
            return 'Item';
        }
        $key = strtolower(trim($levelName));
        return $map[$key] ?? 'OtherLevel';
    }

    private function addDataObjectGroupForRecord(SimpleXMLElement $package, $record): array
    {
        $groupId = 'DOG-' . $record->id;
        $group = $package->addChild('DataObjectGroup');
        $group->addAttribute('id', $groupId);

        $bdoIds = [];
        if (method_exists($record, 'attachments')) {
            foreach ($record->attachments as $attachment) {
                $bdoIds[] = $this->addBinaryDataObject($group, $attachment);
            }
        }
        return [$groupId, $bdoIds];
    }

    private function addBinaryDataObject(SimpleXMLElement $group, $attachment): string
    {
        $bdoId = 'BDO-' . $attachment->id;
        $bdo = $group->addChild('BinaryDataObject');
        $bdo->addAttribute('id', $bdoId);
        $bdo->addChild('DataObjectVersion', 'Master');

        if (!empty($attachment->path)) {
            $bdo->addChild('Uri', $attachment->path);
        }
        if (!empty($attachment->crypt_sha512)) {
            $md = $bdo->addChild('MessageDigest', $attachment->crypt_sha512);
            $md->addAttribute('algorithm', 'SHA-512');
        }
        if (!empty($attachment->size)) {
            $bdo->addChild('Size', (string) $attachment->size);
        }
        $format = $bdo->addChild('FormatIdentification');
        if (!empty($attachment->mime_type)) {
            $format->addChild('MimeType', $attachment->mime_type);
        }
        $fileInfo = $bdo->addChild('FileInfo');
        if (!empty($attachment->name)) {
            $fileInfo->addChild('Filename', $attachment->name);
        }
        return $bdoId;
    }

    private function addEvent(SimpleXMLElement $content, string $dateTime): void
    {
        $event = $content->addChild('Event');
        $event->addChild('EventType', 'Creation');
        $event->addChild('EventDateTime', $dateTime);
    }

    private function setContentBasics(SimpleXMLElement $content, $slip, $record = null): void
    {
        $levelName = $record ? optional($record->level)->name : 'Item';
        $content->addChild('DescriptionLevel', $this->mapDescriptionLevel($levelName));

        $title = $record ? ($record->name ?: ('Record ' . $record->id)) : ($slip->name ?: ('Slip ' . $slip->id));
        $content->addChild('Title', $title);

        $desc = null;
        if ($record) {
            $desc = $record->content ?: null;
        } else {
            $desc = $slip->description ?: null;
        }
        if (!empty($desc)) {
            $content->addChild('Description', $desc);
        }

        $content->addChild('DocumentType', $record ? 'Unité documentaire' : 'Bordereau de versement');
    }

    private function addDatesAndIdentifiersToContent(SimpleXMLElement $content, $record = null): void
    {
        if (!$record) {
            return;
        }
        if (!empty($record->date_start)) {
            $content->addChild('StartDate', $record->date_start);
        }
        if (!empty($record->date_end)) {
            $content->addChild('EndDate', $record->date_end);
        }
        if (!empty($record->code)) {
            $content->addChild('SystemId', $record->code);
            $content->addChild('TransferringAgencyArchiveUnitIdentifier', $record->code);
        }
    }

    private function addDataObjectReferences(SimpleXMLElement $unit, ?string $groupId, array $bdoIds): void
    {
        if (!empty($groupId)) {
            $dor = $unit->addChild('DataObjectReference');
            $dor->addChild('DataObjectReferenceId', $groupId);
        }
        foreach ($bdoIds as $id) {
            $dor = $unit->addChild('DataObjectReference');
            $dor->addChild('DataObjectReferenceId', $id);
        }
    }
}
