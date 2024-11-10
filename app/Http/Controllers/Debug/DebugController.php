<?php

    namespace App\Http\Controllers\Debug;

    use App\Http\Controllers\Controller;
    use App\Models\Contacts;
    use App\Models\ContactsCustomField;

    class DebugController extends Controller
    {
        public function index()
        {

            Contacts::with('contactGroupFields')->chunk(200, function ($contacts) {
                $insertData = [];
                foreach ($contacts as $contact) {
                    $customFields = [
                        'phone'      => $contact->phone,
                        'first_name' => $contact->first_name,
                        'last_name'  => $contact->last_name,
                    ];

                    foreach ($contact->contactGroupFields as $field) {
                        $fieldName = strtolower($field->tag);
                        if ($customFields[$fieldName] !== null) {
                            $insertData[] = [
                                'uid'        => uniqid(),
                                'contact_id' => $contact->id,
                                'field_id'   => $field->id,
                                'value'      => $customFields[$fieldName] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                ContactsCustomField::insert($insertData);
            });

        }

    }
