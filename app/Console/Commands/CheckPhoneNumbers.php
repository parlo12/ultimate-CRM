<?php

namespace App\Console\Commands;

use App\Models\PhoneNumbers;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckPhoneNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'numbers:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check phone numbers expired date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Check Phone Numbers Expired Date');

        $phoneNumbers = PhoneNumbers::where('status', 'assigned')->where('validity_date', '<', Carbon::now()->endOfDay())->cursor();

        foreach ($phoneNumbers as $number) {
            $number->update([
                'status' => 'expired',
            ]);
        }
        $this->info('Check Phone Numbers Expired Date Done');

        return 0;
    }
}
