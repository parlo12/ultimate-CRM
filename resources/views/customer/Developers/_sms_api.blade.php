<div class="text-uppercase text-primary font-medium-2 mb-3">{{ __('locale.developers.sms_api') }}</div>

{!!  __('locale.description.sms_api', ['brandname' => config('app.name')])  !!}

<p class="font-medium-2 mt-2">{{ __('locale.developers.api_endpoint') }}</p>

<pre>
                                <code class="language-markup">
                                    {{ route('api.sms.send') }}
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">{{ __('locale.developers.parameters') }}</div>
<div class="table-responsive">
    <table class="table">
        <thead class="thead-primary">
        <tr>
            <th>{{ __('locale.developers.parameter') }}</th>
            <th>{{ __('locale.labels.required') }}</th>
            <th style="width:50%;">{{ __('locale.labels.description') }}</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td>Authorization</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>When calling our API, send your api token with the authentication type set as <code>Bearer</code>
                (Example: <code>Authorization: Bearer {api_token}</code>)
            </td>
        </tr>

        <tr>
            <td>Accept</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>Set to <code>application/json</code></td>
        </tr>

        </tbody>
    </table>
</div>


<div class="mt-4 mb-1 font-medium-2 text-primary">Send outbound SMS</div>
<p>{{ config('app.name') }}'s Programmable SMS API enables you to programmatically send SMS messages from your web
    application. First, you need to create a new message object. {{ config('app.name') }} returns the created message
    object with each request.</p>
<p> Send your first SMS message with this example request.</p>
<p class="font-medium-2 mt-2">{{ __('locale.developers.api_endpoint') }}</p>

<pre>
                                <code class="language-markup text-primary">
                                    {{ route('api.sms.send') }}
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">{{ __('locale.developers.parameters') }}</div>
<div class="table-responsive">
    <table class="table">
        <thead class="thead-primary">
        <tr>
            <th>{{ __('locale.developers.parameter') }}</th>
            <th>{{ __('locale.labels.required') }}</th>
            <th>{{ __('locale.labels.type') }}</th>
            <th style="width:40%;">{{ __('locale.labels.description') }}</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td>recipient</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>Number to send message. <code>Use comma (,)</code> to send multiple numbers. Ex. <code>31612345678,8801721970168</code>
            </td>
        </tr>

        <tr>
            <td>sender_id</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>The sender of the message. This can be a telephone number (including country code) or an alphanumeric
                string. In case of an alphanumeric string, the maximum length is 11 characters.
            </td>
        </tr>

        <tr>
            <td>type</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>The type of the message. For text message you have to insert <code>plain</code> as sms type.</td>
        </tr>

        <tr>
            <td>message</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>The body of the SMS message.</td>
        </tr>


        <tr>
            <td>schedule_time</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.no') }}</span></div>
            </td>
            <td>datetime</td>
            <td>The scheduled date and time of the message in RFC3339 format <code>(Y-m-d H:i)</code></td>
        </tr>

        <tr>
            <td>dlt_template_id</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.no') }}</span></div>
            </td>
            <td>string</td>
            <td>The ID of your registered DLT (Distributed Ledger Technology) content template.</td>
        </tr>


        </tbody>
    </table>
</div>

<div class="mt-2 font-medium-2 text-primary"> Example request for Single Number</div>
<pre>
                                <code class="language-php">
curl -X POST {{ route('api.sms.send') }} \
-H 'Authorization: Bearer {{ Auth::user()->api_token }}' \
-H 'Content-Type: application/json' \
-H 'Accept: application/json' \
-d '{
"recipient":"31612345678",
"sender_id":"YourName",
"type":"plain",
"message":"This is a test message"
}'
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary"> Example request for Multiple Numbers</div>
<pre>
                                <code class="language-php">
curl -X POST {{ route('api.sms.send') }} \
-H 'Authorization: Bearer {{ Auth::user()->api_token }}' \
-H 'Content-Type: application/json' \
-H 'Accept: application/json' \
-d '{
"recipient":"31612345678,880172145789",
"sender_id":"YourName",
"type":"plain",
"message":"This is a test message",
"schedule_time=2021-12-20 07:00"
}'
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">Returns</div>
<p>Returns a contact object if the request was successful. </p>
<pre>
                                <code class="language-json">
{
    "status": "success",
    "data": "sms reports with all details",
}
                                </code>
                            </pre>
<p>If the request failed, an error object will be returned.</p>
<pre>
                                <code class="language-json">
{
    "status": "error",
    "message" : "A human-readable description of the error."
}
                                </code>
                            </pre>


<div class="mt-4 mb-1 font-medium-2 text-primary">Send Campaign Using Contact list</div>
<p>{{ config('app.name') }}'s Programmable SMS API enables you to programmatically send Campaigns from your web
    application. First, you need to create a new message object. {{ config('app.name') }} returns the created message
    object with each request.</p>
<p> Send your first Campaign Using Contact List with this example request.</p>
<p class="font-medium-2 mt-2">{{ __('locale.developers.api_endpoint') }}</p>

<pre>
                                <code class="language-markup text-primary">
                                    {{ route('api.sms.campaign') }}
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">{{ __('locale.developers.parameters') }}</div>
<div class="table-responsive">
    <table class="table">
        <thead class="thead-primary">
        <tr>
            <th>{{ __('locale.developers.parameter') }}</th>
            <th>{{ __('locale.labels.required') }}</th>
            <th>{{ __('locale.labels.type') }}</th>
            <th style="width:40%;">{{ __('locale.labels.description') }}</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td>contact_list_id</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>Contact list to send message. <code>Use comma (,)</code> to send multiple contact lists. Ex. <code>6415907d0d7a6,6415907d0d37a</code>
            </td>
        </tr>

        <tr>
            <td>sender_id</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>The sender of the message. This can be a telephone number (including country code) or an alphanumeric
                string. In case of an alphanumeric string, the maximum length is 11 characters.
            </td>
        </tr>

        <tr>
            <td>type</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>The type of the message. For text message you have to insert <code>plain</code> as sms type.</td>
        </tr>

        <tr>
            <td>message</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>The body of the SMS message.</td>
        </tr>


        <tr>
            <td>schedule_time</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.no') }}</span></div>
            </td>
            <td>datetime</td>
            <td>The scheduled date and time of the message in RFC3339 format <code>(Y-m-d H:i)</code></td>
        </tr>

        <tr>
            <td>dlt_template_id</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.no') }}</span></div>
            </td>
            <td>string</td>
            <td>The ID of your registered DLT (Distributed Ledger Technology) content template.</td>
        </tr>


        </tbody>
    </table>
</div>

<div class="mt-2 font-medium-2 text-primary"> Example request for Single Contact List</div>
<pre>
                                <code class="language-php">
curl -X POST {{ route('api.sms.campaign') }} \
-H 'Authorization: Bearer {{ Auth::user()->api_token }}' \
-H 'Content-Type: application/json' \
-H 'Accept: application/json' \
-d '{
"recipient":"6415907d0d37a",
"sender_id":"YourName",
"type":"plain",
"message":"This is a test message"
}'
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary"> Example request for Multiple Contact Lists</div>
<pre>
                                <code class="language-php">
curl -X POST {{ route('api.sms.campaign') }} \
-H 'Authorization: Bearer {{ Auth::user()->api_token }}' \
-H 'Content-Type: application/json' \
-H 'Accept: application/json' \
-d '{
"recipient":"6415907d0d37a,6415907d0d7a6",
"sender_id":"YourName",
"type":"plain",
"message":"This is a test message",
"schedule_time=2021-12-20 07:00"
}'
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">Returns</div>
<p>Returns a contact object if the request was successful. </p>
<pre>
                                <code class="language-json">
{
    "status": "success",
    "data": "campaign reports with all details",
}
                                </code>
                            </pre>
<p>If the request failed, an error object will be returned.</p>
<pre>
                                <code class="language-json">
{
    "status": "error",
    "message" : "A human-readable description of the error."
}
                                </code>
                            </pre>


<div class="mt-4 mb-1 font-medium-2 text-primary">View an SMS</div>
<p>You can use {{ config('app.name') }}'s SMS API to retrieve information of an existing inbound or outbound SMS
    message.</p>
<p>You only need to supply the unique message id that was returned upon creation or receiving.</p>
<p class="font-medium-2 mt-2">{{ __('locale.developers.api_endpoint') }}</p>

<pre>
                                <code class="language-markup text-primary">
                                    {{config('app.url')}}/api/v3/sms/<span class="text-danger">{uid}</span>
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">{{ __('locale.developers.parameters') }}</div>
<div class="table-responsive">
    <table class="table">
        <thead class="thead-primary">
        <tr>
            <th>{{ __('locale.developers.parameter') }}</th>
            <th>{{ __('locale.labels.required') }}</th>
            <th>{{ __('locale.labels.type') }}</th>
            <th style="width:40%;">{{ __('locale.labels.description') }}</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td>uid</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>A unique random uid which is created on the {{ config('app.name') }} platform and is returned upon
                creation of the object.
            </td>
        </tr>

        </tbody>
    </table>
</div>

<div class="mt-2 font-medium-2 text-primary"> Example request</div>
<pre>
                                <code class="language-php">
curl -X GET {{ route('api.sms.view', ['uid' => '606812e63f78b']) }} \
-H 'Authorization: Bearer {{ Auth::user()->api_token }}' \
-H 'Content-Type: application/json' \
-H 'Accept: application/json' \
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">Returns</div>
<p>Returns a contact object if the request was successful. </p>
<pre>
                                <code class="language-json">
{
    "status": "success",
    "data": "sms data with all details",
}
                                </code>
                            </pre>
<p>If the request failed, an error object will be returned.</p>
<pre>
                                <code class="language-json">
{
    "status": "error",
    "message" : "A human-readable description of the error."
}
                                </code>
                            </pre>


<div class="mt-4 mb-1 font-medium-2 text-primary">View all messages</div>
<p class="font-medium-2 mt-2">{{ __('locale.developers.api_endpoint') }}</p>

<pre>
                                <code class="language-markup text-primary">
                                    {{config('app.url')}}/api/v3/sms/
                                </code>
                            </pre>


<div class="mt-2 font-medium-2 text-primary"> Example request</div>
<pre>
                                <code class="language-php">
curl -X GET {{ route('api.sms.index') }} \
-H 'Authorization: Bearer {{ Auth::user()->api_token }}' \
-H 'Content-Type: application/json' \
-H 'Accept: application/json' \
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">Returns</div>
<p>Returns a contact object if the request was successful. </p>
<pre>
                                <code class="language-json">
{
    "status": "success",
    "data": "sms reports with pagination",
}
                                </code>
                            </pre>
<p>If the request failed, an error object will be returned.</p>
<pre>
                                <code class="language-json">
{
    "status": "error",
    "message" : "A human-readable description of the error."
}
                                </code>
                            </pre>


<div class="mt-4 mb-1 font-medium-2 text-primary">View Campaign</div>
<p>You can use {{ config('app.name') }}'s Campaign API to retrieve information of an existing Campaigns.</p>
<p>You only need to supply the unique campaign id that was returned upon creation or receiving.</p>
<p class="font-medium-2 mt-2">{{ __('locale.developers.api_endpoint') }}</p>

<pre>
                                <code class="language-markup text-primary">
                                    {{config('app.url')}}/api/v3/campaign/<span class="text-danger">{uid}</span>
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">{{ __('locale.developers.parameters') }}</div>
<div class="table-responsive">
    <table class="table">
        <thead class="thead-primary">
        <tr>
            <th>{{ __('locale.developers.parameter') }}</th>
            <th>{{ __('locale.labels.required') }}</th>
            <th>{{ __('locale.labels.type') }}</th>
            <th style="width:40%;">{{ __('locale.labels.description') }}</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td>uid</td>
            <td>
                <div class="badge badge-light-primary text-uppercase mr-1 mb-1">
                    <span>{{ __('locale.labels.yes') }}</span></div>
            </td>
            <td>string</td>
            <td>A unique random uid which is created on the {{ config('app.name') }} platform and is returned upon
                creation of the object.
            </td>
        </tr>

        </tbody>
    </table>
</div>

<div class="mt-2 font-medium-2 text-primary"> Example request</div>
<pre>
                                <code class="language-php">
curl -X GET {{ route('api.campaign.view', ['uid' => '606812e63f78b']) }} \
-H 'Authorization: Bearer {{ Auth::user()->api_token }}' \
-H 'Content-Type: application/json' \
-H 'Accept: application/json' \
                                </code>
                            </pre>

<div class="mt-2 font-medium-2 text-primary">Returns</div>
<p>Returns a contact object if the request was successful. </p>
<pre>
                                <code class="language-json">
{
    "status": "success",
    "data": "campaign data with all details",
}
                                </code>
                            </pre>
<p>If the request failed, an error object will be returned.</p>
<pre>
                                <code class="language-json">
{
    "status": "error",
    "message" : "A human-readable description of the error."
}
                                </code>
                            </pre>





