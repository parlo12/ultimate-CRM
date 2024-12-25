<?php

namespace App\Console\Commands;

use ElephantIO\Client;
use Illuminate\Console\Command;
use App\Models\SendingServer;
use App\Services\WebsocketAPI\Handler;
use Faker\Provider\bg_BG\PhoneNumber;
use Illuminate\Support\Facades\Cache;

class WebsocketAPIListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket-api:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync websocket-api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sendingServer = SendingServer::where('settings', SendingServer::TYPE_WEBSOCKETAPI)->first();
        if ($sendingServer) {
            $client = Client::create($sendingServer->api_link . '?apiKey=' . $sendingServer->auth_token);
            $client->connect();
            $this->info('Connected to Websocket API');
            while (true) {

                if ($message = Cache::pull('outgoingSMS')) {
                    $message = json_decode($message, true);
                    $client->emit('outgoingSMS', [
                        'deviceId' => $message['device_id'],
                        'receiver' => $message['phone'],
                        'content' => $message['message'],
                    ]);
                }
                if ($packet = $client->wait(null, 1)) {
                    new Handler($packet->event, $packet->data);
                }
            }

            return Command::SUCCESS;
        }
    }
}
