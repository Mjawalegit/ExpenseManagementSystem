@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Edit Expense</div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div id="gps-status" class="alert alert-info d-none"></div>

                <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data" id="expense-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('expense_date') is-invalid @enderror" id="expense_date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                        @error('expense_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" required min="0.01" step="0.01">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Receipt is mandatory for expenses above ₹1,000.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description', $expense->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="receipt" class="form-label">Receipt</label>
                        @if($expense->receipt_path)
                            <div class="mb-2">
                                <a href="{{ route('expenses.receipt', $expense) }}" class="btn btn-sm btn-outline-primary">View Current Receipt</a>
                            </div>
                        @endif
                        <input type="file" class="form-control @error('receipt') is-invalid @enderror" id="receipt" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Allowed formats: JPG, PNG, PDF. Max size: 5MB. Leave blank to keep current receipt.</div>
                        @error('receipt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">GPS Location</label>
                        <div id="gps-info" class="form-text">
                            @if($expense->latitude && $expense->longitude)
                                Captured: {{ $expense->latitude }}, {{ $expense->longitude }} ({{ $expense->location_accuracy }}m)
                            @else
                                No location captured.
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-2" id="capture-gps">Recapture Location</button>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $expense->latitude) }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $expense->longitude) }}">
                        <input type="hidden" name="location_accuracy" id="location_accuracy" value="{{ old('location_accuracy', $expense->location_accuracy) }}">
                        <input type="hidden" name="location_captured_at" id="location_captured_at" value="{{ old('location_captured_at', $expense->location_captured_at) }}">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Expense</button>
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
        button.textContent = 'Recapture Location';
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
            button.textContent = 'Recapture Location';
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
