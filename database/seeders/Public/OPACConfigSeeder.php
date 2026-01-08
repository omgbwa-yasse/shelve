<?php

namespace Database\Seeders\Public;

use Illuminate\Database\Seeder;
use App\Models\OpacConfiguration;
use App\Models\Organisation;

class OPACConfigSeeder extends Seeder
{
    /**
     * Seed the OPAC configuration.
     */
    public function run(): void
    {
        $config = OpacConfiguration::first();

        if (!$config) {
            OpacConfiguration::create([
                'visible_organisations' => Organisation::pluck('id')->toArray(),
                'show_statistics' => true,
                'show_recent_records' => true,
                'allow_downloads' => true,
                'records_per_page' => 20,
                'allowed_file_types' => ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'],
                'show_full_record_details' => true,
                'show_attachments' => true,
                'enable_advanced_search' => true,
                'show_activity_filter' => true,
                'show_date_filter' => true,
                'show_author_filter' => true,
                'max_search_results' => 1000,
                'site_title' => 'Archive OPAC',
                'site_description' => 'Online Public Access Catalog for Archive Documents',
                'contact_email' => 'info@example.org',
                'footer_text' => 'Powered by Shelve Archive Management System',
            ]);

            echo "OPAC configuration created successfully!\n";
        } else {
            echo "OPAC configuration already exists.\n";
        }
    }
}

