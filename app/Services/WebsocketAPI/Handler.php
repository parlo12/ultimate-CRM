<?php

namespace App\Services\WebsocketAPI;

use App\Http\Controllers\Customer\DLRController;
use App\Models\ChatBox;
use App\Models\ChatBoxMessage;
use App\Models\PhoneNumbers;

class Handler
{
    public function __construct(string $eventName, array $data)
    {
        match ($eventName) {
            'sms' => $this->handleSmsEvent(...$data),
        //    'deliveryStatus' => $this->handleDeliveryStatusEvent(...$data),
            default => $this->handleDefault($data),
        };
    }

    private function handleSmsEvent(string $deviceId, string $messageId, string $sender, string $content)
    {
        $number = PhoneNumbers::whereDeviceId($deviceId)->first();
        if ($number) {
            $sending_server = $number->sendingServer;

            $chatbox = ChatBox::where([
                'user_id'           => $number->user_id,
                'sending_server_id' => $sending_server->id,
            ])->where(function($query) use ($sender) {
                $query->where('from', $sender)->orWhere('to', $sender);
            })->first();

            
            if (! $chatbox->exists) {
                $chatbox = new ChatBox([
                    'user_id'           => $number->user_id,
                    'from'              => $number->number,
                    'to'                => $sender,
                    'sending_server_id' => $sending_server->id,
                ]);
                $chatbox->reply_by_customer = true;
                $chatbox->save();
            }

            $messageData  = [
                'box_id'            => $chatbox->id,
                'message'           => $content,
                'send_by'           => $chatbox->from == $sender ? 'from' : 'to',
                'sms_type'          => 'plain',
                'sending_server_id' => $sending_server->id,
                'external_uuid'     => $messageId,
            ];

            ChatBoxMessage::create($messageData);
        }
    }

    private function handleDeliveryStatusEvent(string $messageId, string $status , string $updatedAt)
    {
        DLRController::updateDLR($messageId, $status);
    }

    private function handleDefault($data)
    {
        print_r($data);
    }
}
