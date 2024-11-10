<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatBox\SentRequest;
use App\Models\Blacklists;
use App\Models\Campaigns;
use App\Models\ChatBox;
use App\Models\ChatBoxMessage;
use App\Models\Contacts;
use App\Models\Country;
use App\Models\CustomerBasedPricingPlan;
use App\Models\CustomerBasedSendingServer;
use App\Models\PhoneNumbers;
use App\Models\PlansCoverageCountries;
use App\Models\SendingServer;
use App\Models\Templates;
use App\Repositories\Contracts\CampaignRepository;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use App\Models\ContactGroups;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Repositories\Contracts\ContactsRepository;
use App\Library\Tool;

use Log;

class ChatBoxController extends Controller
{
    protected CampaignRepository $campaigns;
    protected ContactsRepository $contactGroups;

    /**
     * ChatBoxController constructor.
     */
    public function __construct(CampaignRepository $campaigns, ContactsRepository $contactGroups)
    {
        $this->campaigns = $campaigns;
        $this->contactGroups = $contactGroups;
    }

    /**
     * get all chat box
     *
     * @throws AuthorizationException
     */
    public function index(): View|Factory|Application
    {
        $this->authorize('chat_box');
    
        $pageConfigs = [
            'pageHeader'    => false,
            'contentLayout' => 'content-left-sidebar',
            'pageClass'     => 'chat-application',
        ];
        $currentPage = request()->get('page', 1);

        $chat_box = ChatBox::where('user_id', Auth::user()->id)
            ->select('uid', 'id', 'to', 'from', 'updated_at', 'notification', 'is_starred')
            ->where('reply_by_customer', true)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
            $read_box = ChatBox::where('user_id', Auth::user()->id)
            ->select('uid', 'id', 'to', 'from', 'updated_at', 'notification', 'is_starred')
            ->where('reply_by_customer', true)
            ->where('notification', '==',0)
            ->orderBy('updated_at', 'desc')
            ->get();
            $starred_chats= ChatBox::where('user_id', Auth::user()->id)
            ->select('uid', 'id', 'to', 'from', 'updated_at', 'notification', 'is_starred')
            ->where('reply_by_customer', true)
            ->where('is_starred', true)
            ->orderBy('updated_at', 'desc')
            ->get();
        $unread_count = ChatBox::where('notification', '>', 0)
            ->where('reply_by_customer', true)
            ->where('user_id', Auth::user()->id)
            ->count();
    
        $templates = Templates::where('status', true)->where('user_id', auth()->user()->id)->get();
    
        return view('customer.ChatBox.index', [
            'pageConfigs' => $pageConfigs,
            'chat_box'    => $chat_box,
            'templates'   => $templates,
            'read_box'=>$read_box,
            'starred_chats'=>$starred_chats,
            'unread_chats'=> $unread_count
        ]);
    }
    
  public function refresh_sidebar(): JsonResponse
    {
        $unread_count = ChatBox::where('notification', '>', 0)
            ->where('reply_by_customer', true)
            ->where('user_id', Auth::user()->id)
            ->count();
    
            $chat_box = ChatBox::where('user_id', Auth::user()->id)
            ->where('reply_by_customer', true)
            ->orderBy('updated_at', 'desc')
            ->get();
    
        $unread_chat = ChatBox::where('user_id', Auth::user()->id)
            ->where('notification', '>', 0)
            ->where('reply_by_customer', true)
            ->orderBy('updated_at', 'desc')
            ->get();
    
        $starred_chats = ChatBox::where('user_id', Auth::user()->id)
            ->where('is_starred', true)
            ->where('reply_by_customer', true)
            ->orderBy('updated_at', 'desc')
            ->get();
    
        return response()->json([
            'status' => 'success',
            'chat_box' => $chat_box,
            'read_box'=>$chat_box,
            'starred_chats'=>$starred_chats,
            'unread_box'    => $unread_count,
            'unread_chats'=> $unread_count
        ]);
    }
    private function contact_info() {}

    /**
     * start new conversation
     *
     * @throws AuthorizationException
     */
    public function new(): View|Factory|RedirectResponse|Application
    {
        $this->authorize('chat_box');

        $breadcrumbs = [
            ['link' => url('dashboard'), 'name' => __('locale.menu.Dashboard')],
            ['link' => url('chat-box'), 'name' => __('locale.menu.Chat Box')],
            ['name' => __('locale.labels.new_conversion')],
        ];

        $phone_numbers = PhoneNumbers::where('user_id', Auth::user()->id)->where('status', 'assigned')->cursor();

        if (! Auth::user()->customer->activeSubscription()) {
            return redirect()->route('customer.chatbox.index')->with([
                'status'  => 'error',
                'message' => __('locale.customer.no_active_subscription'),
            ]);
        }

        $plan_id = Auth::user()->customer->activeSubscription()->plan_id;

        $coverage = CustomerBasedPricingPlan::where('user_id', Auth::user()->id)->where('status', true)->cursor();
        if ($coverage->count() < 1) {
            $coverage = PlansCoverageCountries::where('plan_id', $plan_id)->where('status', true)->cursor();
        }

        $sendingServers = CustomerBasedSendingServer::where('user_id', auth()->user()->id)->where('status', 1)->get();
        $templates      = Templates::where('status', true)->where('user_id', auth()->user()->id)->get();

        return view('customer.ChatBox.new', compact('breadcrumbs', 'phone_numbers', 'coverage', 'sendingServers', 'templates'));
    }

    /**
     * start new conversion
     *
     *
     * @throws AuthorizationException|NumberParseException
     */
    public function sent(Campaigns $campaign, SentRequest $request): RedirectResponse
    {
        if (config('app.stage') === 'demo') {
            return redirect()->route('customer.chatbox.index')->with([
                'status'  => 'error',
                'message' => __('locale.demo_mode_not_available'),
            ]);
        }

        $this->authorize('chat_box');

        $sendingServers = CustomerBasedSendingServer::where('user_id', Auth::user()->id)->where('status', 1)->count();

        if ($sendingServers && ! isset($request->sending_server)) {
            return redirect()->route('customer.chatbox.index')->with([
                'status'  => 'error',
                'message' => 'Please select your sending server',
            ]);
        }


        $input    = $request->except('_token');
        $senderId = $request->input('sender_id');
        $sms_type = $request->input('sms_type');

        $user    = Auth::user();
        $country = Country::find($request->input('country_code'));

        if (! $country) {
            return redirect()->route('customer.chatbox.index')->with([
                'status'  => 'error',
                'message' => "Permission to send an SMS has not been enabled for the region indicated by the 'To' number: " . $input['recipient'],
            ]);
        }

        $phoneNumberUtil   = PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse('+' . $country->country_code . $request->input('recipient'));
        $countryCode       = $phoneNumberObject->getCountryCode();

        if ($phoneNumberObject->isItalianLeadingZero()) {
            $phone = '0' . preg_replace("/^$countryCode/", '', $phoneNumberObject->getNationalNumber());
        } else {
            $phone = preg_replace("/^$countryCode/", '', $phoneNumberObject->getNationalNumber());
        }

        $input['country_code'] = $countryCode;
        $input['recipient']    = $phone;
        $input['region_code']  = $phoneNumberUtil->getRegionCodeForNumber($phoneNumberObject);
        $input['user']         = Auth::user();

        $planId = $user->customer->activeSubscription()->plan_id;

        $coverage = CustomerBasedPricingPlan::where('user_id', $user->id)
            ->where('status', true)
            ->with('sendingServer')
            ->first();

        if (! $coverage) {
            $coverage = PlansCoverageCountries::where('plan_id', $planId)
                ->where('status', true)
                ->with('sendingServer')
                ->first();
        }

        if (! $coverage) {
            return redirect()->route('customer.chatbox.index')->with([
                'status'  => 'error',
                'message' => 'Price Plan unavailable',
            ]);
        }

        $sendingServer = isset($$request->sending_server) ? SendingServer::find($request->sending_server) : $coverage->sendingServer;

        if (! $sendingServer) {
            return redirect()->route('customer.chatbox.index')->with([
                'status'  => 'error',
                'message' => __('locale.campaigns.sending_server_not_available'),
            ]);
        }

        $db_sms_type = $sms_type == 'unicode' ? 'plain' : $sms_type;

        if (! $sendingServer->{$db_sms_type}) {
            return redirect()->route('customer.chatbox.index')->with([
                'status'  => 'error',
                'message' => __('locale.sending_servers.sending_server_sms_capabilities', ['type' => strtoupper($db_sms_type)]),
            ]);
        }

        if ($sendingServer->settings === 'Whatsender' || $sendingServer->type === 'whatsapp') {
            $input['sms_type'] = 'whatsapp';
        }

        $db_sms_type       = ($sms_type === 'unicode') ? 'plain' : $sms_type;
        $capabilities_type = ($sms_type === 'plain' || $sms_type === 'unicode') ? 'sms' : $sms_type;

        if ($user->customer->getOption('sender_id_verification') === 'yes') {
            $number = PhoneNumbers::where('user_id', $user->id)
                ->where('number', $senderId)
                ->where('status', 'assigned')
                ->first();

            if (! $number) {
                return redirect()->route('customer.chatbox.index')->with([
                    'status'  => 'error',
                    'message' => __('locale.sender_id.sender_id_invalid', ['sender_id' => $senderId]),
                ]);
            }

            $capabilities = str_contains($number->capabilities, $capabilities_type);

            if (! $capabilities) {
                return redirect()->route('customer.chatbox.index')->with([
                    'status'  => 'error',
                    'message' => __('locale.sender_id.sender_id_sms_capabilities', ['sender_id' => $senderId, 'type' => $db_sms_type]),
                ]);
            }

            $input['originator']   = 'phone_number';
            $input['phone_number'] = $senderId;
        }

        $input['reply_by_customer'] = true;

        $data = $this->campaigns->quickSend($campaign, $input);

        if (isset($data->getData()->status)) {
            return redirect()->route('customer.chatbox.index')->with([
                'status'  => $data->getData()->status,
                'message' => $data->getData()->message,
            ]);
        }

        return redirect()->route('customer.chatbox.index')->with([
            'status'  => 'error',
            'message' => __('locale.exceptions.something_went_wrong'),
        ]);
    }
    /**
     * update chat contact info
     */
    public function update_contact(Request $request)
    {
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $userOrg = $request->input('user_org');
        $leadStatus = $request->input('lead_status');
        $contactGroup = $request->input('contact_group');
        $chat_id = $request->input('chat_id');
        $chat_box = ChatBox::firstWhere('uid', $chat_id);
        $contact = Contacts::firstWhere('phone', $chat_box->to);
        $request->merge([
            'PHONE' => $chat_box->to,
        ]);
        if ($contact) {
            $contact->updateFields($request->all());
            $this->update_lead_status($first_name, $last_name, $chat_box->to, $userOrg, $leadStatus);
        } else {

            $contactGroup = ContactGroups::find($contactGroup);
            $new = $this->contactGroups->createContactFromRequest($contactGroup, $request->all());
            $this->update_lead_status($first_name, $last_name, $chat_box->to, $userOrg, $leadStatus);
        }
        return response()->json([
            'status'  => 'success',
            'response' => response()->json($contact),
            'message' => 'Contact details updated successfully'
        ]);
    }
    /**
     * add note
     */
    public function add_note(Request $request)
    {
        $add_note = $request->input('addNote');
        $chat_id = $request->input('chat_id');
        $chat_box = ChatBox::firstWhere('uid', $chat_id);
        $chat_box->note = $add_note;
        $chat_box->save();
        return response()->json([
            'status'  => 'success',
            'response' => response()->json($chat_box),
            'message' => 'Notes for this contact updated successfully'
        ]);
    }
 private function update_lead_status($first_name, $last_name, $phone, $user_org, $lead_status)
{
    $response = Http::get('https://internaltools.godspeedoffers.com/api/update-lead-status', [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'phone' => $phone,
        'user_org' => $user_org,
        'lead_status' => $lead_status,
    ]);

    if ($response->successful()) {
        return response()->json(['message' => 'Lead status updated successfully.']);
    } else {
        return response()->json(['error' => 'Failed to update lead status.'], $response->status());
    }
}


    /**
     * get chat messages
     */
    public function messages(ChatBox $box): JsonResponse
    {
        $box->update([
            'notification' => 0,
        ]);


        $timezone = Auth::user()->timezone ?? config('app.timezone');

        $data = ChatBoxMessage::where('box_id', $box->id)
            ->orderBy('created_at')
            ->select('message', 'send_by', 'media_url', 'box_id', 'created_at')
            ->get(['message', 'send_by', 'media_url', 'box_id', 'created_at'])
            ->toArray();

        $data = array_map(function ($message) use ($timezone) {
            $message['created_at'] = Carbon::parse($message['created_at'])->timezone($timezone)->format(config('app.date_format') . ', g:i A');

            return $message;
        }, $data);

        $jsonData = json_encode($data, true);

        return response()->json([
            'status' => 'success',
            'data'   => $jsonData,
        ]);
    }
    /**
     * get note
     */
    public function get_note(ChatBox $box): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'note'   => $box->note,
        ]);
    }
    /**
     * toggle star
     */
    public function toggle_star(ChatBox $box): JsonResponse
    {
        $box->is_starred = !$box->is_starred;
            $box->save();
            return response()->json([
                'status' => 'success',
                'message'   => 'Chat marked as favorite',
            ]
            );
    }
    /**
     * get chat additional_info
     */
    public function additional_info(ChatBox $box): JsonResponse
    {
        $info = array();
        $contact_group_id = null;
        $first_name = '';
        $last_name = '';
        $contact = Contacts::firstWhere('phone', $box->to);
        if ($contact) {
            $group = ContactGroups::find($contact->group_id);
            $contact_group_id = $contact->group_id;
        } else {
            $group = null;
        }
        $groups = ContactGroups::all();
        if (! $group) {
            $contact_group_id = null;
            $first_name = '';
            $last_name = '';
        } else {
            foreach ($group->getFields as $field) {
                $subscriber = Contacts::where('group_id', $contact->group_id)->where('uid', $contact->uid)->first();
                if ($field->tag === 'FIRST_NAME') {
                    $first_name = trim($subscriber->getValueByField($field));
                }
                if ($field->tag === 'LAST_NAME') {
                    $last_name = trim($subscriber->getValueByField($field));
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'group_id'   => $contact_group_id,
            'groups' => $groups,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_and_orgs' => $this->fetchUsersAndOrgs()->original
        ]);
    }

    private function fetchUsersAndOrgs()
    {
        // Send GET request to the API
        $response = Http::get('https://internaltools.godspeedoffers.com/api/get-user-and-orgs');

        // Check if the request was successful
        if ($response->successful()) {
            // Parse and return the response data
            $data = $response->json();
            return response()->json($data);
        } else {
            // Handle error and return an appropriate response
            return response()->json(['error' => 'Failed to fetch data from API'], $response->status());
        }
    }
    /**
     * get chat messages
     */
    public function messagesWithNotification(ChatBox $box): JsonResponse
    {
        $data = ChatBoxMessage::where('box_id', $box->id)->select('message', 'send_by', 'media_url', 'box_id', 'created_at')->latest()->first()->toJson();


        return response()->json([
            'status'       => 'success',
            'data'         => $data,
            'notification' => $box->notification,
        ]);
    }

    /**
     * reply message
     *
     *
     * @throws AuthorizationException
     * @throws NumberParseException
     */
    public function reply(ChatBox $box, Campaigns $campaign, Request $request): JsonResponse
    {
        if (config('app.stage') == 'demo') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sorry! This option is not available in demo mode',
            ]);
        }

        $this->authorize('chat_box');

        if (empty($request->message)) {
            return response()->json([
                'status'  => 'error',
                'message' => __('locale.campaigns.insert_your_message'),
            ]);
        }

        $user = Auth::user();

        $sending_server = SendingServer::find($box->sending_server_id);

        if (! $sending_server) {
            return response()->json([
                'status'  => 'error',
                'message' => __('locale.campaigns.sending_server_not_available'),
            ]);
        }

        $sender_id = $box->from;

       
        if ($request->hasFile('file')) {
            $media_url = Tool::uploadImage($request->file('file'));
        } else {
            // Handle the case where no file is uploaded, if necessary
            $media_url = null; // or set a default value
        }

        if ($sending_server->settings == 'Whatsender' || $sending_server->type == 'whatsapp') {
            $sms_type          = 'whatsapp';
            $capabilities_type = $sms_type;
        } 
        else if($media_url){
            Log::info('Trying to send MMS');
            $sms_type          = 'mms';
            $capabilities_type = 'mms';
        }
        else {
            $sms_type          = 'plain';
            $capabilities_type = 'sms';
        }
        $input = [
            'sender_id'      => $sender_id,
            'originator'     => 'phone_number',
            'sending_server' => $sending_server->id,
            'sms_type'       => $sms_type,
            'message'        => $request->message,
            'exist_c_code'   => 'yes',
            'user'           => $user,
            'media_url'      =>$media_url,
        ];

        if ($user->customer->getOption('sender_id_verification') == 'yes') {

            $number = PhoneNumbers::where('user_id', $user->id)->where('number', $sender_id)->where('status', 'assigned')->first();

            if (! $number) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('locale.sender_id.sender_id_invalid', ['sender_id' => $sender_id]),
                ]);
            }

            $capabilities = str_contains($number->capabilities, $capabilities_type);

            if (! $capabilities) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('locale.sender_id.sender_id_sms_capabilities', ['sender_id' => $sender_id, 'type' => $sms_type]),
                ]);
            }

            $input['originator']   = 'phone_number';
            $input['phone_number'] = $sender_id;
        }

        try {

            $phoneUtil         = PhoneNumberUtil::getInstance();
            $phoneNumberObject = $phoneUtil->parse('+' . $box->to);

            if ($phoneUtil->isPossibleNumber($phoneNumberObject)) {
                $input['country_code'] = $phoneNumberObject->getCountryCode();
                $input['recipient']    = $phoneNumberObject->getNationalNumber();
                $input['region_code']  = $phoneUtil->getRegionCodeForNumber($phoneNumberObject);

                $data = $this->campaigns->quickSend($campaign, $input);

                if (isset($data->getData()->status)) {
                    if ($data->getData()->status == 'success') {
                        return response()->json([
                            'status'  => 'success',
                            'message' => __('locale.campaigns.message_successfully_delivered'),
                        ]);
                    }

                    return response()->json([
                        'status'  => $data->getData()->status,
                        'message' => $data->getData()->message,
                    ]);
                }

                return response()->json([
                    'status'  => 'error',
                    'message' => __('locale.exceptions.something_went_wrong'),
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => __('locale.customer.invalid_phone_number', ['phone' => $box->to]),
            ]);
        } catch (NumberParseException $exception) {
            return response()->json([
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * delete chatbox messages
     */
    public function delete(ChatBox $box): JsonResponse
    {
        $messages = ChatBoxMessage::where('box_id', $box->id)->delete();
        if ($messages) {
            $box->delete();

            return response()->json([
                'status'  => 'success',
                'message' => __('locale.campaigns.sms_was_successfully_deleted'),
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => __('locale.exceptions.something_went_wrong'),
        ]);
    }
    public function view(ChatBox $box): JsonResponse
    {
        $messages = ChatBoxMessage::where('box_id', $box->id)->delete();
        if ($messages) {
            $box->delete();

            return response()->json([
                'status'  => 'success',
                'message' => __('locale.campaigns.sms_was_successfully_deleted'),
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => __('locale.exceptions.something_went_wrong'),
        ]);
    }
    /**
     * add to blacklist
     */
    public function block(ChatBox $box): JsonResponse
    {
        $status = Blacklists::create([
            'user_id' => auth()->user()->id,
            'number'  => $box->to,
            'reason'  => 'Blacklisted by ' . auth()->user()->displayName(),
        ]);

        if ($status) {

            $contact = Contacts::where('phone', $box->to)->first();
            $contact?->update([
                'status' => 'unsubscribe',
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => __('locale.blacklist.blacklist_successfully_added'),
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => __('locale.exceptions.something_went_wrong'),
        ]);
    }
    public function view_chat_contact(ChatBox $box): JsonResponse
    {
        Log::info($box->to);
        $contact = Contacts::firstWhere('phone', $box->to);
        $group_id = ContactGroups::find($contact->group_id);

        $subscriber = Contacts::where('group_id', $contact->group_id)->where('uid', $contact->uid)->first();

        if (! $subscriber) {
            return response()->json(['status' => 'error', 'message' => __('locale.http.404.description')], 404);
        }

        $output = $subscriber->only('uid', 'phone', 'status');

        $values = [];

        foreach ($group_id->getFields as $field) {
            // if ($field->tag != 'PHONE') {
            $values[$field->tag] = $subscriber->getValueByField($field);
            //}
        }
        $values['Workflow_message'] = $this->get_message($box->to);
        $output['custom_fields'] = $values;

        return response()->json([
            'status'  => $output,
            'message' => __('locale.blacklist.blacklist_successfully_added'),
        ]);
    }
    private function get_message($phoneNumber)
    {
        try {
            $response = Http::get("https://internaltools.godspeedoffers.com/api/get-message/{$phoneNumber}");
            
            // Check if the response status is OK
            if ($response->successful()) {
                $data = $response->json();

                // Ensure the message key exists in the response data
                if (isset($data['message'])) {
                    return $data['message'];
                }
            }
        } catch (\Exception $e) {
            Log::info("Error: $e");
            // Log the exception if needed
        }

        // Return fallback message on failure
        return 'No workflow message found';
    }
}
