<?php

namespace App\Console\Commands;

use App\Http\Controllers\Customer\DLRController;
use ElephantIO\Client;
use Illuminate\Console\Command;

class WebsocketReplyRegister extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket-api:reply';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync websocket-api replys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = "ws://localhost:4000";
        $client = Client::create($url);
        $client->connect();
        while (true) {
            if ($packet = $client->wait(null, 1)) {
                $data = $packet->data;
                print_r($data);
                
            }
        }

        return Command::SUCCESS;
    }
}
