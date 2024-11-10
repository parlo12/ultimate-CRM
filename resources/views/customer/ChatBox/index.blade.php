@extends('layouts/contentLayoutMaster')

@section('title', __('locale.menu.Chat Box'))


@section('vendor-style')
    <!-- vendor css files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection


@section('page-style')
    <!-- Page css files -->
    <link rel="stylesheet" href="{{ asset(mix('css/base/pages/app-chat.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/base/pages/app-chat-list.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">

    <style>
        /* For screens smaller than 576px */
        @media (max-width: 576px) {

            /* Set the max-width of the image or video to 100% to make it responsive */
            img,
            video {
                max-width: 100%;
                height: auto;
            }
        }

        /* For screens between 576px and 768px */
        @media (min-width: 576px) and (max-width: 768px) {

            img,
            video {
                max-width: 100%;
                height: auto;
            }
        }

        /* For screens between 768px and 992px */
        @media (min-width: 768px) and (max-width: 992px) {

            img,
            video {
                max-width: 100%;
                height: auto;
            }
        }

        /* For screens between 992px and 1200px */
        @media (min-width: 992px) and (max-width: 1200px) {

            img,
            video {
                max-width: 100%;
                height: auto;
            }
        }

        /* For screens larger than 1200px */
        @media (min-width: 1200px) {

            img,
            video {
                max-width: 100%;
                height: auto;
            }
        }

        .preserve-whitespace {
            white-space: pre-wrap;
        }
    </style>

@endsection

@section('content-sidebar')
    @include('customer.ChatBox._sidebar')
@endsection


@section('content')
    <div class="body-content-overlay"></div>
    <!-- Main chat area -->
    <section class="chat-app-window">
        <!-- To load Conversation -->
        <div class="start-chat-area">
            <div class="mb-1 start-chat-icon">
                <i data-feather="message-square"></i>
            </div>
            <h4 class="sidebar-toggle start-chat-text d-block d-md-none">
                {{ __('locale.labels.new_conversion') }}
            </h4>
            <h4 class="sidebar-toggle start-chat-text d-none d-md-block">
                <a href="{{ route('customer.chatbox.new') }}" class="text-dark">{{ __('locale.labels.new_conversion') }}</a>
            </h4>
        </div>
        <!--/ To load Conversation -->

        <!-- Active Chat -->
        <div class="active-chat d-none">
            <!-- Chat Header -->
            <div class="chat-navbar">
                <header class="chat-header">
                    <div class="d-flex align-items-center">
                        <div class="sidebar-toggle d-block d-lg-none me-1">
                            <i data-feather="menu" class="font-medium-5"></i>
                        </div>
                        <div class="avatar avatar-border user-profile-toggle m-0 me-1"></div>
                    </div>
                    <div class="d-flex align-items-center">

                        <span class="view-chat-contact" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="{{ __('locale.labels.view') }}"> <i data-feather="eye"
                                class="cursor-pointer font-medium-2 text-primary"></i> </span>

                        <span class="add-to-blacklist" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="{{ __('locale.labels.block') }}"> <i data-feather="shield"
                                class="cursor-pointer font-medium-2 mx-1 text-primary"></i> </span>

                        <span class="remove-btn" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="{{ __('locale.buttons.delete') }}"><i data-feather="trash"
                                class="cursor-pointer font-medium-2 text-danger"></i></span>

                    </div>
                </header>
            </div>
            <!--/ Chat Header -->

            <!-- User Chat messages -->
            <div class="user-chats d-flex">
                <div class="chats col-8 me-1">
                    <div class="chat_history"></div>
                </div>
                <div class="col-4 h-100" style="position: sticky; top: 0;">
                    <div class="overflow-auto" style="max-height: 100%;">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs d-flex" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="update-contact-tab" data-bs-toggle="tab"
                                    data-bs-target="#update-contact" type="button" role="tab"
                                    aria-controls="update-contact" aria-selected="true">Update</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="add-note-tab" data-bs-toggle="tab" data-bs-target="#add-note"
                                    type="button" role="tab" aria-controls="add-note" aria-selected="false">Add
                                    Note</button>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content" id="myTabContent">
                            <!-- Update Contact Tab -->
                            <div class="tab-pane fade show active" id="update-contact" role="tabpanel"
                                aria-labelledby="update-contact-tab">

                            </div>

                            <!-- Add Note Tab -->
                            <div class="tab-pane fade" id="add-note" role="tabpanel" aria-labelledby="add-note-tab">

                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <!-- User Chat messages -->

            <!-- Submit Chat form -->
            <form class="chat-app-form" action="javascript:void(0);" onsubmit="enter_chat();">

                <div class="input-group input-group-merge me-1 form-send-message">
                    <textarea type="text" id="message" class="form-control message"
                        placeholder="{{ __('locale.campaigns.type_your_message') }}"></textarea>

                </div>
                <div class="align-items-center">
                    <div>
                        <span id="file-name" class=""></span> <!-- Display the file name here -->
                    </div>
                    <div>
                        <label for="file-upload" class="attachment" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Attachment" style="cursor: pointer;">
                            <i data-feather="paperclip" class="cursor-pointer font-medium-2 text-primary"></i>
                        </label>
                        <input type="file" id="file-upload" style="display: none;">
                    </div>
                </div>



                <div class=" me-1">
                    <select class="form-select select2" id="sms_template" data-placeholder="Select Template">
                        <option value="0">Select Template</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>


                <button type="button" class="btn btn-primary send" onclick="enter_chat();">
                    <i data-feather="send" class="d-lg-none"></i>
                    <span class="d-none d-lg-block">{{ __('locale.buttons.send') }}</span>
                </button>
            </form>
            <!--/ Submit Chat form -->
        </div>

        <!--/ Active Chat -->
    </section>
    <!--/ Main chat area -->
@endsection

@section('vendor-script')
    <!-- vendor files -->
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection


@section('page-script')
    <!-- Page js files -->
    <script src="{{ asset(mix('js/scripts/pages/chat.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
    @if (config('broadcasting.connections.pusher.app_id'))
        <script src="{{ asset(mix('js/scripts/echo.js')) }}"></script>
    @endif

    <script>
        document.getElementById('file-upload').addEventListener('change', function() {
            let fileName = this.files[0] ? this.files[0].name : '';
            let clippedFileName = fileName.length > 10 ? fileName.slice(0, 10) + '...' : fileName;
            document.getElementById('file-name').textContent = clippedFileName;
        });


        // autoscroll to bottom of Chat area
        let chatContainer = $(".user-chats"),
            details,
            chatHistory = $(".chat_history");

        // Basic Select2 select
        $(".select2").each(function() {
            let $this = $(this);
            $this.wrap('<div class="position-relative"></div>');
            $this.select2({
                // the following code is used to disable x-scrollbar when click in select input and
                // take 100% width in responsive also
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $this.parent(),
                placeholder: $this.data('placeholder'),
            });
        });


        $("#sms_template").on('change', function() {

            let template_id = $(this).val(),
                $get_msg = $("#message");

            if (template_id === '0') {
                return false;
            }

            $.ajax({
                url: "{{ url('templates/show-data') }}" + '/' + template_id,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                cache: false,
                success: function(data) {
                    if (data.status === 'success') {
                        const caretPos = $get_msg[0].selectionStart;
                        const textAreaTxt = $get_msg.val();
                        let txtToAdd = data.message;

                        $get_msg.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt
                            .substring(caretPos)).val().length;

                    } else {
                        toastr['warning'](data.message, "{{ __('locale.labels.attention') }}", {
                            closeButton: true,
                            positionClass: 'toast-top-right',
                            progressBar: true,
                            newestOnTop: true,
                            rtl: isRtl
                        });
                    }
                },
                error: function(reject) {
                    if (reject.status === 422) {
                        let errors = reject.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr['warning'](value[0],
                                "{{ __('locale.labels.attention') }}", {
                                    closeButton: true,
                                    positionClass: 'toast-top-right',
                                    progressBar: true,
                                    newestOnTop: true,
                                    rtl: isRtl
                                });
                        });
                    } else {
                        toastr['warning'](reject.responseJSON.message,
                            "{{ __('locale.labels.attention') }}", {
                                closeButton: true,
                                positionClass: 'toast-top-right',
                                progressBar: true,
                                newestOnTop: true,
                                rtl: isRtl
                            });
                    }
                }
            });
        });
        let messageInterval; // Declare the variable to hold the interval

        function startMessagePulling(chat_id) {
            messageInterval = setInterval(function() {
                pullMessages(chat_id); // Replace 'your_chat_id' with the actual chat ID
            }, 7000);
        }

        function pullMessages(chat_id) {
            console.log('Pullin messages');

            $.post(`{{ url('/chat-box') }}/${chat_id}/messages`, {
                    _token: "{{ csrf_token() }}"
                })
                .done(function(response) {
                    chatHistory.empty();
                    let details = `<input type="hidden" value="${chat_id}" name="chat_id" class="chat_id">`;

                    const cwData = JSON.parse(response.data);

                    cwData.forEach((sms) => {
                        let media_url = '';
                        if (sms.media_url !== null) {
                            let fileType = isImageOrVideo(sms.media_url);

                            if (fileType === 'video') {
                                media_url =
                                    `<p><video src="${sms.media_url}" controls>Your browser does not support the video tag.</video></p>`;
                            } else if (fileType === 'audio') {
                                media_url =
                                    `<p><audio src="${sms.media_url}" controls>Your browser does not support the audio element.</audio></p>`;
                            } else if (fileType === 'image') {
                                media_url = `<p><img src="${sms.media_url}" alt="Image"/></p>`;
                            } else {
                                // For other file types, provide a downloadable link
                                media_url = `
    <p>
        <a class="text-white" href="${sms.media_url}" download>
            <img src="https://godspeedoffers.com/mms/document.png" alt="Download file" class="cursor-pointer" style="width: 100%; height: auto;"/>
            <span class="text-wrap" style="max-width: calc(100% - 30px);"> <!-- Adjust max-width based on image width -->
            ${sms.media_url} 
        </span>
        </a>
    </p>`;
                            }
                        }


                        let message = '';
                        if (sms.message !== null) {
                            message = `<p class="preserve-whitespace">${sms.message}</p>`;
                        }

                        const chatHtml = `<div class="chat ${sms.send_by === 'to' ? 'chat-left' : ''}">
                <div class="chat-avatar">
                    <span class="avatar box-shadow-1 cursor-pointer">
                        <img src="{{ asset('images/profile/profile.jpg') }}" alt="avatar" height="36" width="36"/>
                    </span>
                </div>
                <div class="chat-body">
                    <div class="chat-content">
                        ${media_url}
                        ${message}
                        <p class="chat-time text-muted mt-1">${sms.created_at}</p>
                    </div>
                </div>
            </div>`;

                        details += chatHtml;
                    });

                    chatHistory.append(details);
                    // chatContainer.animate({
                    //     scrollTop: chatContainer[0].scrollHeight
                    // }, 400);
                })
                .fail(function(xhr, status, error) {
                    console.log(error);
                });
        }

        $(".chat-users-list li").on("click", function() {
            clearInterval(messageInterval);

            chatHistory.empty();
            chatContainer.animate({
                scrollTop: chatContainer[0].scrollHeight
            }, 0)

            const chat_id = $(this).data('id');
            startMessagePulling(chat_id);



            $.post(
                    `{{ url('/chat-box') }}/${chat_id}/messages`, {
                        _token: "{{ csrf_token() }}"
                    }
                )
                .done(function(response) {
                    let details = `<input type="hidden" value="${chat_id}" name="chat_id" class="chat_id">`;

                    const cwData = JSON.parse(response.data);

                    cwData.forEach((sms) => {
                        let media_url = '';
                        if (sms.media_url !== null) {
                            let fileType = isImageOrVideo(sms.media_url);

                            if (fileType === 'video') {
                                media_url =
                                    `<p><video src="${sms.media_url}" controls>Your browser does not support the video tag.</video></p>`;
                            } else if (fileType === 'audio') {
                                media_url =
                                    `<p><audio src="${sms.media_url}" controls>Your browser does not support the audio element.</audio></p>`;
                            } else if (fileType === 'image') {
                                media_url = `<p><img src="${sms.media_url}" alt="Image"/></p>`;
                            } else {
                                // For other file types, provide a downloadable link
                                media_url = `
    <p>
        <a class="text-secondary" href="${sms.media_url}" download>
            <img src="https://godspeedoffers.com/mms/document.png" alt="Download file" class="cursor-pointer" style="width: 100%; height: auto;"/>
            Doc
        </a>
    </p>`;
                            }
                        }


                        let message = '';
                        if (sms.message !== null) {
                            // Apply the preserve-whitespace class to maintain the message formatting
                            message = `<p class="preserve-whitespace">${sms.message}</p>`;
                        }

                        const chatHtml = `<div class="chat ${sms.send_by === 'to' ? 'chat-left' : ''}">
                    <div class="chat-avatar">
                        <span class="avatar box-shadow-1 cursor-pointer">
                            <img src="{{ asset('images/profile/profile.jpg') }}" alt="avatar" height="36" width="36"/>
                        </span>
                    </div>
                    <div class="chat-body">
                        <div class="chat-content">
                            ${media_url}
                            ${message}
                            <p class="chat-time text-muted mt-1">${sms.created_at}</p>
                        </div>
                    </div>
                </div>`;

                        details += chatHtml;
                    });

                    chatHistory.append(details);
                    chatContainer.animate({
                        scrollTop: chatContainer[0].scrollHeight
                    }, 400);
                })
                .fail(function(xhr, status, error) {
                    console.log(error);
                });
            $.post(`{{ url('/chat-box') }}/${chat_id}/additional_info`, {
                    _token: "{{ csrf_token() }}"
                })
                .done(function(response) {
                    console.log(response);

                    // Populate the form with response data
                    document.getElementById('update-contact').innerHTML = `
        <form id="update-contact-form">
            <input type="text" readonly class="d-none" id="chat_id" name="chat_id" value="${chat_id}">
            <div class="mb-1">
                <label for="first-name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first-name" name="first_name" value="${response.first_name}">
            </div>
            <div class="mb-1">
                <label for="last-name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last-name" name="last_name" value="${response.last_name}">
            </div>
            <div class="mb-1">
                <label for="user-org" class="form-label">User & Org</label>
                <select class="form-select" id="user-org" name="user_org">
                    <option selected>Select org</option>
                    ${response.user_and_orgs.map(user_org => `
                                                        <option value="${user_org.organisation_id}">${user_org.user_name} - ${user_org.organisation_name}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="mb-1">
                <label for="leadStatus" class="form-label">Lead Status</label>
                <select class="form-select" id="leadStatus" name="lead_status">
                    <option selected>Select Status</option>
                    <option value="1">Offer Made</option>
                    <option value="2">Lead Generated</option>
                    <option value="3">Contract Executed</option>
                    <option value="4">Deal Closed</option>
                </select>
            </div>
            <div class="mb-1">
                <label for="contact-group" class="form-label">Contact Group</label>
                <select class="form-select" id="contact-group" name="contact_group">
                    <option selected>Select Group</option>
                    ${response.groups.map(group => `
                                                        <option value="${group.id}" ${group.id == response.group_id ? 'selected' : ''}>${group.name}</option>`
                    ).join('')}
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>`;

                    // Make Contact Group read-only if group_id is not null
                    if (response.group_id !== null) {
                        document.getElementById('contact-group').setAttribute('disabled', true);
                    }

                    // Add event listener for form submission
                    $('#update-contact-form').on('submit', function(e) {
                        e.preventDefault(); // Prevent default form submission
                        const formData = $(this).serialize(); // Serialize the form data
                        $.ajax({
                            url: '{{ url('/chat-box/update-contact') }}', // Replace with your Laravel endpoint
                            type: 'POST',
                            data: formData,
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}" // CSRF token for security
                            },
                            success: function(response) {
                                toastr['success'](response.message, 'Success!!', {
                                    closeButton: true,
                                    positionClass: 'toast-top-right',
                                    progressBar: true,
                                    newestOnTop: true,
                                    rtl: isRtl
                                });
                                console.log('Contact updated successfully:', response);
                            },
                            error: function(xhr, status, error) {
                                toastr['warning']('Server Error',
                                    "{{ __('locale.labels.attention') }}", {
                                        closeButton: true,
                                        positionClass: 'toast-top-right',
                                        progressBar: true,
                                        newestOnTop: true,
                                        rtl: isRtl
                                    });
                                console.log('Error updating contact:', error);
                            }
                        });
                    });
                })
                .fail(function(xhr, status, error) {
                    console.log(error);
                });
            $.post(
                    `{{ url('/chat-box') }}/${chat_id}/get-note`, {
                        _token: "{{ csrf_token() }}"
                    }
                )
                .done(function(response) {
                    console.log(response);

                    // Populate the form with response data
                    document.getElementById('add-note').innerHTML = `
        <form id='add-note-form'>
            <input type="text" readonly class="d-none" id="chat_id" name="chat_id" value="${chat_id}">
            <div class="mb-1">
                <label for="addNote" class="form-label">Add Note</label>
                <textarea class="form-control" id="addNote" name="addNote" rows="7">${response.note ? response.note : ''}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    `;

                    // Add event listener for form submission
                    $('#add-note-form').on('submit', function(e) {
                        e.preventDefault(); // Prevent default form submission
                        const formData = $(this).serialize(); // Serialize the form data
                        $.ajax({
                            url: '{{ url('/chat-box/add-note') }}', // Replace with your Laravel endpoint
                            type: 'POST',
                            data: formData,
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}" // CSRF token for security
                            },
                            success: function(response) {
                                toastr['success'](response.message, 'Success!!', {
                                    closeButton: true,
                                    positionClass: 'toast-top-right',
                                    progressBar: true,
                                    newestOnTop: true,
                                    rtl: isRtl
                                });
                                console.log('Note updated successfully:', response);
                            },
                            error: function(xhr, status, error) {
                                toastr['warning']('Server Error',
                                    "{{ __('locale.labels.attention') }}", {
                                        closeButton: true,
                                        positionClass: 'toast-top-right',
                                        progressBar: true,
                                        newestOnTop: true,
                                        rtl: isRtl
                                    });
                                console.log('Error updating note:', error);
                            }
                        });
                    });
                })
                .fail(function(xhr, status, error) {
                    console.log(error);
                });

        });



        function isImageOrVideo(url) {
            const ext = url.substr(url.lastIndexOf('.') + 1);
            const imageExts = ['jpg', 'jpeg', 'gif', 'png'];
            const videoExts = ['mp4', 'avi', 'mov', 'wmv'];
            const audioExts = ['ogg', 'mp3'];

            if (imageExts.indexOf(ext.toLowerCase()) !== -1) {
                return 'image';
            } else if (videoExts.indexOf(ext.toLowerCase()) !== -1) {
                return 'video';
            } else if (audioExts.indexOf(ext.toLowerCase()) !== -1) {
                return 'audio';
            } else {
                return false;
            }
        }

        // Add message to chat
        function enter_chat() {
            let message = $(".message"),
                chatBoxId = $(".chat_id").val(),
                messageValue = message.val(),
                fileInput = document.getElementById('file-upload'),
                formData = new FormData();

            formData.append('message', messageValue);
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('file', fileInput.files[0]); // Append file from file input

            $.ajax({
                url: "{{ url('/chat-box') }}" + '/' + chatBoxId + '/reply',
                type: "POST",
                data: formData,
                processData: false, // Prevent jQuery from processing the data
                contentType: false, // Prevent jQuery from setting the content type
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        toastr['success'](response.message, 'Success!!', {
                            closeButton: true,
                            positionClass: 'toast-top-right',
                            progressBar: true,
                            newestOnTop: true,
                            rtl: isRtl
                        });

                        let html = '<div class="chat">' +
                            '<div class="chat-avatar">' +
                            '<span class="avatar box-shadow-1 cursor-pointer">' +
                            '<img src="{{ asset('images/profile/profile.jpg') }}" alt="avatar" height="36" width="36"/>' +
                            '</span>' +
                            '</div>' +
                            '<div class="chat-body">' +
                            '<div class="chat-content">' +
                            '<p>' + messageValue + '</p>' +
                            '<p>test</p>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                        chatHistory.append(html);
                        message.val("");
                        fileInput.value = ""; // Reset file input after successful upload
                        $(".user-chats").scrollTop($(".user-chats > .chats").height());
                    } else {
                        toastr['warning'](response.message, "{{ __('locale.labels.attention') }}", {
                            closeButton: true,
                            positionClass: 'toast-top-right',
                            progressBar: true,
                            newestOnTop: true,
                            rtl: isRtl
                        });
                    }
                },
                error: function(reject) {
                    if (reject.status === 422) {
                        let errors = reject.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr['warning'](value[0], "{{ __('locale.labels.attention') }}", {
                                closeButton: true,
                                positionClass: 'toast-top-right',
                                progressBar: true,
                                newestOnTop: true,
                                rtl: isRtl
                            });
                        });
                    } else {
                        toastr['warning'](reject.responseJSON.message, "{{ __('locale.labels.attention') }}", {
                            closeButton: true,
                            positionClass: 'toast-top-right',
                            progressBar: true,
                            newestOnTop: true,
                            rtl: isRtl
                        });
                    }
                }
            });
        }



        $(".remove-btn").on('click', function(event) {
            event.preventDefault();
            let sms_id = $(".chat_id").val();

            Swal.fire({
                title: "{{ __('locale.labels.are_you_sure') }}",
                text: "{{ __('locale.labels.able_to_revert') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "{{ __('locale.labels.delete_it') }}",
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ms-1'
                },
                buttonsStyling: false,
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: "{{ url('/chat-box') }}" + '/' + sms_id + '/delete',
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                toastr['success'](response.message,
                                    '{{ __('locale.labels.success') }}!!', {
                                        closeButton: true,
                                        positionClass: 'toast-top-right',
                                        progressBar: true,
                                        newestOnTop: true,
                                        rtl: isRtl
                                    });

                                setTimeout(function() {
                                    window.location
                                        .reload(); // then reload the page.(3)
                                }, 3000);

                            } else {
                                toastr['warning'](response.message,
                                    '{{ __('locale.labels.warning') }}!', {
                                        closeButton: true,
                                        positionClass: 'toast-top-right',
                                        progressBar: true,
                                        newestOnTop: true,
                                        rtl: isRtl
                                    });
                            }
                        },
                        error: function(reject) {
                            if (reject.status === 422) {
                                let errors = reject.responseJSON.errors;
                                $.each(errors, function(key, value) {
                                    toastr['warning'](value[0],
                                        "{{ __('locale.labels.attention') }}", {
                                            closeButton: true,
                                            positionClass: 'toast-top-right',
                                            progressBar: true,
                                            newestOnTop: true,
                                            rtl: isRtl
                                        });
                                });
                            } else {
                                toastr['warning'](reject.responseJSON.message,
                                    "{{ __('locale.labels.attention') }}", {
                                        closeButton: true,
                                        positionClass: 'toast-top-right',
                                        progressBar: true,
                                        newestOnTop: true,
                                        rtl: isRtl
                                    });
                            }
                        }
                    });
                }
            })

        })

        $(".add-to-blacklist").on('click', function(event) {
            event.preventDefault();
            let sms_id = $(".chat_id").val();

            Swal.fire({
                title: "{{ __('locale.labels.are_you_sure') }}",
                text: "{{ __('locale.labels.remove_blacklist') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "{{ __('locale.labels.block') }}",
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ms-1'
                },
                buttonsStyling: false,
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: "{{ url('/chat-box') }}" + '/' + sms_id + '/block',
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {

                            if (response.status === 'success') {
                                toastr['success'](response.message,
                                    '{{ __('locale.labels.success') }}!!', {
                                        closeButton: true,
                                        positionClass: 'toast-top-right',
                                        progressBar: true,
                                        newestOnTop: true,
                                        rtl: isRtl
                                    });

                                setTimeout(function() {
                                    window.location
                                        .reload(); // then reload the page.(3)
                                }, 3000);

                            } else {
                                toastr['warning'](response.message,
                                    '{{ __('locale.labels.warning') }}!', {
                                        closeButton: true,
                                        positionClass: 'toast-top-right',
                                        progressBar: true,
                                        newestOnTop: true,
                                        rtl: isRtl
                                    });
                            }
                        },
                        error: function(reject) {
                            if (reject.status === 422) {
                                let errors = reject.responseJSON.errors;
                                $.each(errors, function(key, value) {
                                    toastr['warning'](value[0],
                                        "{{ __('locale.labels.attention') }}", {
                                            closeButton: true,
                                            positionClass: 'toast-top-right',
                                            progressBar: true,
                                            newestOnTop: true,
                                            rtl: isRtl
                                        });
                                });
                            } else {
                                toastr['warning'](reject.responseJSON.message,
                                    "{{ __('locale.labels.attention') }}", {
                                        closeButton: true,
                                        positionClass: 'toast-top-right',
                                        progressBar: true,
                                        newestOnTop: true,
                                        rtl: isRtl
                                    });
                            }
                        }
                    });
                }
            })

        })
        $(".view-chat-contact").on('click', function(event) {
            event.preventDefault();
            let sms_id = $(".chat_id").val();

            // Skip the confirmation dialog and directly send the AJAX request
            $.ajax({
                url: "{{ url('/chat-box') }}" + '/' + sms_id + '/view-chat-contact',
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status) {
                        let customFields = response.status.custom_fields;
                        let customFieldsString = '';

                        $.each(customFields, function(key, value) {
                            customFieldsString += key + ': ' + value + '<br>';
                        });

                        toastr['success'](customFieldsString,
                            '{{ __('locale.labels.customer_info') }}!!', {
                                closeButton: true,
                                positionClass: 'toast-top-right',
                                progressBar: true,
                                newestOnTop: true,
                                rtl: isRtl,
                                timeOut: 0, // No timeout; the toast will only close manually
                                extendedTimeOut: 0, // Disable extended timeout as well
                                tapToDismiss: false
                            });

                    } else {
                        toastr['warning'](response.message,
                            '{{ __('locale.labels.warning') }}!', {
                                closeButton: true,
                                positionClass: 'toast-top-right',
                                progressBar: true,
                                newestOnTop: true,
                                rtl: isRtl,
                                timeOut: 0, // No timeout; the toast will only close manually
                                extendedTimeOut: 0 // Disable extended timeout as well
                            });
                    }
                },
                error: function(reject) {
                    if (reject.status === 422) {
                        let errors = reject.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr['warning'](value[0],
                                "{{ __('locale.labels.attention') }}", {
                                    closeButton: true,
                                    positionClass: 'toast-top-right',
                                    progressBar: true,
                                    newestOnTop: true,
                                    rtl: isRtl,
                                    timeOut: 0, // No timeout; the toast will only close manually
                                    extendedTimeOut: 0 // Disable extended timeout as well
                                });
                        });
                    } else {
                        toastr['warning'](reject.responseJSON.message,
                            "{{ __('locale.labels.attention') }}", {
                                closeButton: true,
                                positionClass: 'toast-top-right',
                                progressBar: true,
                                newestOnTop: true,
                                rtl: isRtl,
                                timeOut: 0, // No timeout; the toast will only close manually
                                extendedTimeOut: 0 // Disable extended timeout as well
                            });
                    }
                }
            });
        });


        @if (config('broadcasting.connections.pusher.app_id'))
            let activeChatID = $('.chat-users-list li.active').attr('data-id');

            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: "{{ config('broadcasting.connections.pusher.key') }}",
                cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
                encrypted: true,
                authEndpoint: '{{ config('app.url') }}/broadcasting/auth'
            });

            Pusher.logToConsole = false;

            Echo.private('chat').listen('MessageReceived', (e) => {
                // chatHistory.empty();
                chatContainer.animate({
                    scrollTop: chatContainer[0].scrollHeight
                }, 0);

                let chat_id = e.data.uid;
                let box_id = e.data.id;

                $.ajax({
                    url: `{{ url('/chat-box') }}/${chat_id}/notification`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        activeChatID = $('.chat-users-list li.active').attr('data-id');
                        let details =
                            `<input type="hidden" value="${chat_id}" name="chat_id" class="chat_id">`;
                        const $contact = $(`.media-list li[data-box-id=${box_id}]`);
                        const $counter = $(".counter", $contact).removeAttr('hidden');
                        $(".notification_count", $contact).html(response.notification);

                        const sms = JSON.parse(response.data);
                        let media_url = '';
                        let message = '';

                        if (sms.media_url !== null) {
                            let fileType = isImageOrVideo(sms.media_url);
                            if (fileType === 'video') {
                                media_url =
                                    `<p><video src="${sms.media_url}" controls>Your browser does not support the video tag. <video/></p>`;
                            } else if (fileType === 'audio') {
                                media_url =
                                    `<p><audio src="${sms.media_url}" controls>Your browser does not support the audio element. </audio></p>`;
                            } else {
                                media_url = `<p><img src="${sms.media_url}" alt=""/></p>`;
                            }
                        }

                        if (sms.message !== null) {
                            message = `<p>${sms.message}</p>`;
                        }

                        if (sms.send_by === 'to') {
                            details += `<div class="chat chat-left">
                        <div class="chat-avatar">
                          <a class="avatar m-0" href="#">
                            <img src="{{ asset('images/profile/profile.jpg') }}" alt="avatar" height="40" width="40"/>
                          </a>
                        </div>
                        <div class="chat-body">
                          <div class="chat-content">
                            ${media_url}
                            ${message}
                            <p class="chat-time text-muted mt-1">${sms.created_at}</p>
                          </div>
                        </div>
                      </div>`;
                        } else {
                            details += `<div class="chat">
                        <div class="chat-avatar">
                          <a class="avatar m-0" href="#">
                            <img src="{{ route('user.avatar', Auth::user()->uid) }}" alt="avatar" height="40" width="40"/>
                          </a>
                        </div>
                        <div class="chat-body">
                          <div class="chat-content">
                          ${media_url}
                          ${message}
                          <p class="chat-time text-muted mt-1">${sms.created_at}</p>
                          </div>
                          </div>
                          </div>`;
                        }

                        if (chat_id === activeChatID) {
                            chatHistory.append(details);
                            chatContainer.animate({
                                scrollTop: chatContainer[0].scrollHeight
                            }, 0);
                        } else {
                            $counter.html(response.notification);
                            $counter.removeAttr('hidden');
                        }
                    }
                });
            });
        @endif
    </script>
@endsection
