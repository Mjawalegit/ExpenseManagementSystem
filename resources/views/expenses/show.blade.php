@extends('layouts.app')

@section('title', 'Expense Details')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Expense Details</span>
                @if($expense->status->value === 'pending')
                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-warning">Edit</a>
                @endif
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Employee</th>
                        <td>{{ $expense->user->name }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{ $expense->category->name }}</td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td>₹{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $expense->description }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($expense->status->value === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($expense->status->value === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                    </tr>
                    @if($expense->rejection_reason)
                        <tr>
                            <th>Rejection Reason</th>
                            <td>{{ $expense->rejection_reason }}</td>
                        </tr>
                    @endif
                    @if($expense->receipt_path)
                        <tr>
                            <th>Receipt</th>
                            <td>
                                <a href="{{ route('expenses.receipt', $expense) }}" class="btn btn-sm btn-outline-primary" target="_blank">View Receipt</a>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        @if($expense->latitude && $expense->longitude)
            <div class="card mt-3">
                <div class="card-header">GPS Location</div>
                <div class="card-body">
                    <p><strong>Latitude:</strong> {{ $expense->latitude }}</p>
                    <p><strong>Longitude:</strong> {{ $expense->longitude }}</p>
                    <p><strong>Accuracy:</strong> {{ $expense->location_accuracy }}m</p>
                    <p><strong>Captured At:</strong> {{ $expense->location_captured_at->format('Y-m-d H:i:s') }}</p>
                    <a href="https://www.google.com/maps?q={{ $expense->latitude }},{{ $expense->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary">View on Google Maps</a>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Approval History</div>
            <div class="card-body">
                @forelse($expense->approvalHistories as $history)
                    <div class="border-start border-4 ps-3 mb-3 @if($history->action->value === 'approved') border-success @elseif($history->action->value === 'rejected') border-danger @else border-info @endif">
                        <strong>{{ ucfirst($history->action->value) }}</strong>
                        <p class="mb-1">By: {{ $history->actionedBy->name }}</p>
                        <p class="mb-1 text-muted small">{{ $history->created_at->format('Y-m-d H:i') }}</p>
                        @if($history->rejection_reason)
                            <p class="mb-0 text-danger">Reason: {{ $history->rejection_reason }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-muted">No history available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
