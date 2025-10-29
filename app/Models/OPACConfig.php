<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OPACConfig extends Model
{
    use HasFactory;

    protected $table = 'opac_configs';

    protected $fillable = [
        'organisation_id',
        'visible_organisations',
        'show_statistics',
        'show_recent_records',
        'allow_downloads',
        'records_per_page',
        'allowed_file_types',
        'show_full_record_details',
        'show_attachments',
        'enable_advanced_search',
        'contact_email',
        'contact_phone',
        'contact_address',
        'site_title',
        'site_description',
        'logo_path',
        'custom_css',
        'footer_text',
        'show_activity_filter',
        'show_date_filter',
        'show_author_filter',
        'max_search_results',
    ];

    protected $casts = [
        'visible_organisations' => 'array',
        'allowed_file_types' => 'array',
        'show_statistics' => 'boolean',
        'show_recent_records' => 'boolean',
        'allow_downloads' => 'boolean',
        'show_full_record_details' => 'boolean',
        'show_attachments' => 'boolean',
        'enable_advanced_search' => 'boolean',
        'show_activity_filter' => 'boolean',
        'show_date_filter' => 'boolean',
        'show_author_filter' => 'boolean',
        'records_per_page' => 'integer',
        'max_search_results' => 'integer',
    ];

    /**
     * Relation avec l'organisation propriétaire de cette configuration
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Récupère les organisations visibles dans l'OPAC
     */
    public function getVisibleOrganisationsModels()
    {
        if (empty($this->visible_organisations)) {
            return collect();
        }

        return Organisation::whereIn('id', $this->visible_organisations)->get();
    }

    /**
     * Vérifie si une organisation est visible dans l'OPAC
     */
    public function isOrganisationVisible($organisationId)
    {
        return in_array($organisationId, $this->visible_organisations ?? []);
    }

    /**
     * Vérifie si un type de fichier est autorisé au téléchargement
     */
    public function isFileTypeAllowed($extension)
    {
        if (empty($this->allowed_file_types)) {
            return false;
        }

        return in_array(strtolower($extension), $this->allowed_file_types);
    }

    /**
     * Récupère la configuration par défaut
     */
    public static function getDefaultConfig()
    {
        return [
            'visible_organisations' => [],
            'show_statistics' => true,
            'show_recent_records' => true,
            'allow_downloads' => false,
            'records_per_page' => 20,
            'allowed_file_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            'show_full_record_details' => true,
            'show_attachments' => false,
            'enable_advanced_search' => true,
            'show_activity_filter' => true,
            'show_date_filter' => true,
            'show_author_filter' => true,
            'max_search_results' => 1000,
            'site_title' => 'OPAC - Online Public Access Catalog',
            'site_description' => 'Access to our digital archive catalog',
            'contact_email' => '',
            'contact_phone' => '',
            'contact_address' => '',
            'footer_text' => 'Powered by Shelve Archive Management System',
        ];
    }

    /**
     * Crée ou met à jour la configuration OPAC
     */
    public static function updateConfig(array $data)
    {
        $config = self::first();

        if (!$config) {
            $config = new self();
        }

        $config->fill($data);
        $config->save();

        return $config;
    }
}
