<?php

namespace App\Console\Commands;

use App\Models\Mail;
use Illuminate\Console\Command;

/**
 * MailSendController::store() (Internal Mail > Send) used to hardcode
 * mail_type = 'outgoing' even when the user picked an internal recipient
 * (recipient_type = 'internal'), instead of Mail::TYPE_INTERNAL. As a result
 * these communications went through the full outgoing workflow (N+1 visa
 * then mandatory DG signature) instead of being delivered directly to the
 * destination service after the N+1 visa.
 *
 * This command finds mails wrongly stored as 'outgoing' that actually have
 * an internal recipient (recipient_organisation_id set, no external
 * recipient fields) and reclassifies them as 'internal'. It only touches
 * the mail_type column — status, dg_signature_status, history, etc. are
 * left untouched, so already-completed records keep their real history.
 *
 * Usage:
 *   php artisan mails:fix-internal-type --dry-run   # report only
 *   php artisan mails:fix-internal-type             # apply fixes
 */
class FixInternalMailType extends Command
{
    protected $signature = 'mails:fix-internal-type {--dry-run : Show what would change without writing}';

    protected $description = "Reclassify 'outgoing' mails with an internal recipient as mail_type='internal'";

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = Mail::where('mail_type', Mail::TYPE_OUTGOING)
            ->whereNotNull('recipient_organisation_id')
            ->whereNull('external_recipient_id')
            ->whereNull('external_recipient_organization_id');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No mail found to reclassify.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%s %d mail(s) with mail_type=outgoing but an internal recipient.',
            $dryRun ? 'Found' : 'Reclassifying',
            $count
        ));

        if ($dryRun) {
            $query->orderBy('id')->each(function (Mail $mail) {
                $this->line(sprintf('  #%d  %s  (%s)', $mail->id, $mail->code, $mail->name));
            });
            return self::SUCCESS;
        }

        $updated = $query->update(['mail_type' => Mail::TYPE_INTERNAL]);
        $this->info(sprintf('Reclassified %d mail(s) to mail_type=internal.', $updated));

        return self::SUCCESS;
    }
}
