@extends('layouts/contentLayoutMaster')

@section('title', __('locale.phone_numbers.update_number'))

@section('vendor-style')
    <!-- vendor css files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')

    <!-- Basic Vertical form layout section start -->
    <section id="basic-vertical-layouts">
        <div class="row match-height">
            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('locale.phone_numbers.update_number') }}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">

                            <p>{!!  __('locale.description.phone_number') !!}</p>

                            <form class="form form-vertical"
                                  action="{{ route('customer.numbers.update',  $number->uid) }}" method="post">
                                {{ method_field('PUT') }}
                                @csrf
                                <div class="form-body">
                                    <div class="row">

                                        <div class="col-12">
                                            <div class="mb-1">
                                                <label for="number"
                                                       class="form-label">{{ __('locale.labels.number') }}</label>
                                                <input type="text" id="number"
                                                       class="form-control"
                                                       value="{{ $number->number }}" name="number" disabled >
                                            </div>
                                        </div>
                                        @if($number->sendingServer->settings == \App\Models\SendingServer::TYPE_WEBSOCKETAPI)
                                            <div class="col-12">
                                                <div class="mb-1">
                                                    <label for="device_id"
                                                        class="form-label required">{{ __('locale.labels.device_id') }}</label>
                                                    <input type="text" id="device_id"
                                                        class="form-control @error('device_id') is-invalid @enderror"
                                                        value="{{ $number->device_id }}" name="device_id" required
                                                        placeholder="{{__('locale.labels.required')}}" autofocus>
                                                    @error('device_id')
                                                    <p><small class="text-danger">{{ $message }}</small></p>
                                                    @enderror
                                            </div>
                                        @endif

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary mb-1"><i
                                                        data-feather="save"></i> {{ __('locale.buttons.save') }}
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- // Basic Vertical form layout section end -->

@endsection

@section('vendor-script')
    <!-- vendor files -->
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection



@section('page-script')

    <script>
        $(document).ready(function () {

            let firstInvalid = $('form').find('.is-invalid').eq(0);

            if (firstInvalid.length) {
                $('body, html').stop(true, true).animate({
                    'scrollTop': firstInvalid.offset().top - 200 + 'px'
                }, 200);
            }

        });
    </script>
@endsection
