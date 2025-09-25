<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contact;
use Carbon\Carbon;

class UpdateContactStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update contact status 1hari sekali setiap jam 9 malam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        // Menemukan kontak yang dibuat lebih dari satu bulan yang lalu
        // dan statusnya masih '1'
        $contactsToUpdate = Contact::where('status', '1')
                                    ->where('created_at', '<', $oneMonthAgo)
                                    ->get();

        $updatedCount = 0;
        foreach ($contactsToUpdate as $contact) {
            $contact->status = '0';
            $contact->save();
            $updatedCount++;
        }

        $this->info("{$updatedCount} contacts updated to status '0'.");

        return 0;
    }
}
