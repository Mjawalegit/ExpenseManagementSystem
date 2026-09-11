@extends('layouts.app')

@section('title', 'Create Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Create Expense</div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div id="gps-status" class="alert alert-info d-none"></div>

                <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" id="expense-form">
                    @csrf

                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('expense_date') is-invalid @enderror" id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                        @error('expense_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required min="0.01" step="0.01">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Receipt is mandatory for expenses above ₹1,000.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="receipt" class="form-label">Receipt</label>
                        <input type="file" class="form-control @error('receipt') is-invalid @enderror" id="receipt" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Allowed formats: JPG, PNG, PDF. Max size: 5MB. Required for expenses above ₹1,000.</div>
                        @error('receipt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">GPS Location</label>
                        <div id="gps-info" class="form-text">Click the button below to capture your current location.</div>
                        <button type="button" class="btn btn-outline-primary mt-2" id="capture-gps">Capture Location</button>
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="location_accuracy" id="location_accuracy">
                        <input type="hidden" name="location_captured_at" id="location_captured_at">
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Expense</button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('capture-gps').addEventListener('click', function() {
    const gpsInfo = document.getElementById('gps-info');
    const button = this;
    button.disabled = true;
    button.textContent = 'Capturing...';

    if (!navigator.geolocation) {
        gpsInfo.textContent = 'Geolocation is not supported by your browser.';
        gpsInfo.classList.add('text-danger');
        button.disabled = false;
        button.textContent = 'Capture Location';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            document.getElementById('location_accuracy').value = position.coords.accuracy;
            document.getElementById('location_captured_at').value = new Date().toISOString();

            gpsInfo.textContent = `Location captured: ${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)} (Accuracy: ${position.coords.accuracy.toFixed(2)}m)`;
            gpsInfo.classList.remove('text-danger');
            gpsInfo.classList.add('text-success');
            button.textContent = 'Location Captured';
        },
        function(error) {
            let message = 'Unable to retrieve location.';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Location permission was denied. Please allow location access.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Location information is unavailable.';
                    break;
                case error.TIMEOUT:
                    message = 'Location request timed out.';
                    break;
            }
            gpsInfo.textContent = message;
            gpsInfo.classList.remove('text-success');
            gpsInfo.classList.add('text-danger');
            button.disabled = false;
            button.textContent = 'Capture Location';
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
});
</script>
@endsection
