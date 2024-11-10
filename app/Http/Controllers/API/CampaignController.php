<?php

    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Campaigns\SendAPICampaign;
    use App\Http\Requests\Campaigns\SendAPISMS;
    use App\Library\SMSCounter;
    use App\Library\Tool;
    use App\Models\Campaigns;
    use App\Models\CustomerBasedSendingServer;
    use App\Models\Reports;
    use App\Models\Traits\ApiResponser;
    use App\Repositories\Contracts\CampaignRepository;
    use Carbon\Carbon;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Support\Facades\Auth;
    use libphonenumber\NumberParseException;
    use libphonenumber\PhoneNumberUtil;
    use App\Models\ContactGroups;
    use App\Models\ChatBox;
    use App\Models\ChatBoxMessage;
    use App\Models\Contacts;
    use App\Events\MessageReceived;
    use App\Models\Notifications;
    use App\Models\User;
    use App\Models\PhoneNumbers;

    use Log;
use Request;

    class CampaignController extends Controller
    {
        use ApiResponser;

        protected CampaignRepository $campaigns;

        /**
         * CampaignController constructor.
         */
        public function __construct(CampaignRepository $campaigns)
        {
            $this->campaigns = $campaigns;
        }

        /**
         * sms sending
         */
        public function smsSend(Campaigns $campaign, SendAPISMS $request, Carbon $carbon): JsonResponse
        {
            if (config('app.stage') == 'demo') {
                return $this->error('Sorry! This option is not available in demo mode');
            }

            $user = request()->user();
            if ( ! $user) {
                return $this->error(__('locale.auth.user_not_exist'));
            }

            if ( ! $user->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            $sms_type     = $request->get('type', 'plain');
            $sms_counter  = new SMSCounter();
            $message_data = $sms_counter->count($request->input('message'));

            if ($message_data->encoding == 'UTF16') {
                $sms_type = 'unicode';
            }

            if ( ! in_array($sms_type, ['plain', 'unicode', 'voice', 'mms', 'whatsapp', 'viber', 'otp'])) {
                return $this->error(__('locale.exceptions.invalid_sms_type'));
            }

            if ($sms_type == 'voice' && ( ! $request->filled('gender') || ! $request->filled('language'))) {
                return $this->error('Language and gender parameters are required');
            }

            if ($sms_type == 'mms' && ! $request->filled('media_url')) {
                return $this->error('media_url parameter is required');
            }

            if ($sms_type == 'mms' && filter_var($request->input('media_url'), FILTER_VALIDATE_URL) === false) {
                return $this->error('Valid media url is required.');
            }

            $validateData = $this->campaigns->checkQuickSendValidation([
                'sender_id' => $request->sender_id,
                'message'   => $request->message,
                'user_id'   => $user->id,
                'sms_type'  => $sms_type,
            ]);

            if ($validateData->getData()->status == 'error') {
                return $this->error($validateData->getData()->message);
            }

            try {

                $input          = $this->prepareInput($request, $user, $sms_type, $carbon);
                $sendingServers = CustomerBasedSendingServer::where('user_id', $user->id)->where('status', 1)->count();

                if ($sendingServers > 0 && isset($user->api_sending_server)) {
                    $input['sending_server'] = $user->api_sending_server;
                }

                $isBulkSms = substr_count($input['recipient'], ',') > 0;

                $data = $isBulkSms
                    ? $this->campaigns->sendApi($campaign, $input)
                    : $this->processSingleRecipient($campaign, $input);

                $status = optional($data->getData())->status;

                return $status === 'success'
                    ? $this->success($data->getData()->data ?? null, $data->getData()->message)
                    : $this->error($data->getData()->message ?? __('locale.exceptions.something_went_wrong'), 403);

            } catch (NumberParseException $exception) {
                return $this->error($exception->getMessage(), 403);
            }
        }

        /**
         * @return JsonResponse|mixed
         *
         * @throws NumberParseException
         */
        private function processSingleRecipient($campaign, &$input)
        {

            $phone             = str_replace(['+', '(', ')', '-', ' '], '', $input['recipient']);
            $phone             = ltrim($phone, '0');
            $phoneUtil         = PhoneNumberUtil::getInstance();
            $phoneNumberObject = $phoneUtil->parse('+' . $phone);
            if ( ! $phoneUtil->isPossibleNumber($phoneNumberObject)) {
                return $this->error(__('locale.customer.invalid_phone_number', ['phone' => $phone]));
            }

            if ($phoneNumberObject->isItalianLeadingZero()) {
                $input['recipient'] = '0' . $phoneNumberObject->getNationalNumber();
            } else {
                $input['recipient'] = $phoneNumberObject->getNationalNumber();
            }

            $input['country_code'] = $phoneNumberObject->getCountryCode();
            $input['region_code']  = $phoneUtil->getRegionCodeForNumber($phoneNumberObject);

            return $this->campaigns->quickSend($campaign, $input);
        }

        /**
         * @return array
         */
        private function prepareInput($request, $user, $sms_type, $carbon)
        {
            $input = [
                'sender_id' => $request->input('sender_id'),
                'sms_type'  => $sms_type,
                'api_key'   => $user->api_token,
                'user'      => $user,
                'recipient' => $request->input('recipient'),
                'delimiter' => ',',
                'message'   => $request->input('message'),
            ];

            switch ($sms_type) {
                case 'voice':
                    $input['language'] = $request->input('language');
                    $input['gender']   = $request->input('gender');
                    break;
                case 'mms':
                case 'whatsapp':
                case 'viber':
                    if ($request->filled('media_url')) {
                        $input['media_url'] = $request->input('media_url');
                    }
                    break;
            }

            if ($request->filled('schedule_time')) {
                $input['schedule']        = true;
                $input['schedule_date']   = $carbon->parse($request->input('schedule_time'))->toDateString();
                $input['schedule_time']   = $carbon->parse($request->input('schedule_time'))->setSeconds(0)->format('H:i');
                $input['timezone']        = $user->timezone;
                $input['frequency_cycle'] = 'onetime';
            }

            return $input;
        }

        /**
         * view single sms reports
         */
        public function viewSMS(Reports $uid): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }


            if ( ! request()->user()->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            if (request()->user()->can('view_reports')) {
                $reports = Reports::select('uid', 'to', 'from', 'message', 'customer_status', 'cost')->find($uid->id);
                if ($reports) {
                    return $this->success($reports);
                }

                return $this->error('SMS Info not found');
            }

            return $this->error(__('locale.http.403.description'), 403);
        }


        /**
         * get all messages
         */
        public function viewAllSMS(): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            if ( ! request()->user()->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            if (request()->user()->can('view_reports')) {
                $reports = Reports::select('uid', 'to', 'from', 'message', 'customer_status', 'cost')->orderBy('created_at', 'desc')->paginate(25);
                if ($reports) {
                    return $this->success($reports);
                }

                return $this->error('SMS Info not found');
            }

            return $this->error(__('locale.http.403.description'), 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Version 3.7
        |--------------------------------------------------------------------------
        |
        | Send Campaign Using API
        |
        */

        public function campaign(Campaigns $campaign, SendAPICampaign $request)
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $input = $request->all();
            $user  = request()->user();

            if ( ! $user) {
                return $this->error(__('locale.auth.user_not_exist'));
            }

            if ( ! $user->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            $sendingServers = CustomerBasedSendingServer::where('user_id', $user->id)->where('status', 1)->count();

            if ($sendingServers > 0 && isset($user->api_sending_server)) {
                $input['sending_server'] = $user->api_sending_server;
            }

            $sms_type     = $request->get('type', 'plain');
            $sms_counter  = new SMSCounter();
            $message_data = $sms_counter->count($request->input('message'));

            if ($message_data->encoding == 'UTF16') {
                $sms_type = 'unicode';
            }

            if ( ! in_array($sms_type, ['plain', 'unicode', 'voice', 'mms', 'whatsapp', 'viber', 'otp'])) {
                return $this->error(__('locale.exceptions.invalid_sms_type'));
            }

            if ($sms_type == 'voice' && ( ! $request->filled('gender') || ! $request->filled('language'))) {
                return $this->error('Language and gender parameters are required');
            }

            if ($sms_type == 'mms' && ! $request->filled('media_url')) {
                return $this->error('media_url parameter is required');
            }

            if ($sms_type == 'mms' && filter_var($request->input('media_url'), FILTER_VALIDATE_URL) === false) {
                return $this->error('Valid media url is required.');
            }
            $input['api_key']  = $user->api_token;
            $input['timezone'] = $user->timezone;
            $input['name']     = 'API_' . time();

            unset($input['sender_id']);

            if ($request->get('sender_id') !== null) {
                if (is_numeric($request->get('sender_id'))) {
                    $input['originator']   = 'phone_number';
                    $input['phone_number'] = [$request->get('sender_id')];
                } else {
                    $input['sender_id']  = [$request->get('sender_id')];
                    $input['originator'] = 'sender_id';
                }
            }

            if ($request->input('schedule_time') !== null) {
                $input['schedule']        = true;
                $input['schedule_date']   = Carbon::parse($request->input('schedule_time'))->toDateString();
                $input['schedule_time']   = Carbon::parse($request->input('schedule_time'))->setSeconds(0)->format('H:i');
                $input['frequency_cycle'] = 'onetime';
            }

            $data = $this->campaigns->apiCampaignBuilder($campaign, $input);

            if (isset($data->getData()->status)) {

                if ($data->getData()->status == 'success') {
                    return $this->success($data->getData()->data, $data->getData()->message);
                }

                return $this->error($data->getData()->message);

            }

            return $this->error(__('locale.exceptions.something_went_wrong'));

        }


        /**
         * view campaign
         */
        public function viewCampaign(Campaigns $uid): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }


            if ( ! request()->user()->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            if (request()->user()->can('view_reports')) {

                $reportStatusCounts = Reports::where('user_id', Auth::user()->id)->where('campaign_id', $uid->id)->selectRaw('
        COUNT(CASE WHEN customer_status = "Enroute" THEN 1 END) as enroute_count,
        COUNT(CASE WHEN customer_status = "Delivered" THEN 1 END) as delivered_count,
        COUNT(CASE WHEN customer_status = "Expired" THEN 1 END) as expired_count,
        COUNT(CASE WHEN customer_status = "Undelivered" THEN 1 END) as undelivered_count,
        COUNT(CASE WHEN customer_status = "Rejected" THEN 1 END) as rejected_count,
        COUNT(CASE WHEN customer_status = "Accepted" THEN 1 END) as accepted_count,
        COUNT(CASE WHEN customer_status = "Skipped" THEN 1 END) as skipped_count,
        COUNT(CASE WHEN customer_status NOT IN ("Enroute", "Delivered", "Expired", "Undelivered", "Rejected", "Accepted", "Skipped") THEN 1 END) as failed_count
    ')
                    ->first();

                $data = [
                    'id'         => $uid->uid,
                    'name'       => $uid->campaign_name,
                    'message'    => $uid->message,
                    'status'     => $uid->status,
                    'type'       => $uid->sms_type,
                    'created_at' => Tool::customerDateTime($uid->created_at),
                ];

                if ( ! empty($uid->run_at)) {
                    $data['start_at'] = Tool::customerDateTime($uid->run_at);
                }

                if ($uid->status == 'done') {
                    $data['delivery_at'] = Tool::customerDateTime($uid->delivery_at);
                }

                if ($reportStatusCounts) {
                    $data['stats'] = $reportStatusCounts;
                }


                return $this->success($data, 'Campaign successfully retrieved');
            }

            return $this->error(__('locale.http.403.description'), 403);
        }

        public function create_chatbox_entry(Request $request)
        {
            $from=$request->input('from');
            $to=$request->input('to');
            Log::info($from);
            $message = '';
            $contact = Contacts::firstWhere('phone', $from);
            $group_id = ContactGroups::find($contact->group_id);
        
            $subscriber = Contacts::where('group_id', $contact->group_id)
                ->where('uid', $contact->uid)
                ->first();
        
            if (! $subscriber) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This is not a subscriber',
                ]);
            }  
            $output = $subscriber->only('uid', 'phone', 'status');
        
            $values = [];
            $message = "Subscriber details:\n";  // Start creating the human-readable message.
        
            // Iterate over the custom fields and create a human-readable message.
            foreach ($group_id->getFields as $field) {
                // Skip the 'PHONE' field if necessary
                $fieldValue = $subscriber->getValueByField($field);
                if ($fieldValue) {
                    $values[$field->tag] = $fieldValue;
                    // Append each custom field and its value to the message string
                    $message .= ucfirst(strtolower($field->tag)) . ": " . $fieldValue . "\n";
                }
            }
            $phone_number = PhoneNumbers::where('number', $from)
                    ->where('status', 'assigned')
                    ->first();
            $sending_server_id=$phone_number->sending_server_id;
            $user_id=$phone_number->user_id;
            $user    = User::find($user_id);
            $chatbox = ChatBox::firstOrNew([
                'user_id'           => $user_id,
                'from'              => $to,
                'to'                => $from,
                'sending_server_id' => $sending_server_id,
            ]);

            if ( ! $chatbox->exists) {
                if (isset($input['reply_by_customer'])) {
                    $chatbox->reply_by_customer = true;
                }

                $chatbox->save();
            }

            ChatBoxMessage::create([
                'box_id'            => $chatbox->id,
                'message'           => $message,
                'send_by'           => 'from',
                'sms_type'          => 'plain',
                'sending_server_id' => $sending_server->id,
            ]);
        
            Notifications::create([
                'user_id'           => $user_id,
                'notification_for'  => 'customer',
                'notification_type' => 'chatbox',
                'message'           => 'New chat message arrived',
            ]);
            event(new MessageReceived($user, $message, $chatbox ));        
            // Return response with the human-readable message
            return response()->json([
                'status'  => $output,
                'message' => $message, // Include the human-readable message here
            ]);
        }
        
    }
