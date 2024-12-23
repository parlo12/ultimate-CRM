<?php

namespace App\Console\Commands;

use App\Http\Controllers\Customer\DLRController;
use App\Models\SendingServer;
use ElephantIO\Client;
use Illuminate\Console\Command;

class WebsocketAPIDLR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket-api:dlr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check websocket-api message delivery reports';

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
                if ($packet = $client->wait('messageStatusUpdate', 1)) {
                    $data = $packet->data;
                    if ($data['status'] == strtolower("Delivered"))
                        DLRController::updateDLR($data['messageId'], 'Delivered');
                }
            }

            return Command::SUCCESS;
        }
    }
}
