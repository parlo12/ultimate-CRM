<?php

namespace App\Console\Commands;

use App\Http\Controllers\Customer\DLRController;
use ElephantIO\Client;
use Illuminate\Console\Command;
use App\Models\SendingServer;

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
        $sendingServer = SendingServer::where('settings', SendingServer::TYPE_WEBSOCKETAPI)->first();
        if ($sendingServer) {
            $client = Client::create($sendingServer->api_link);
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
}
