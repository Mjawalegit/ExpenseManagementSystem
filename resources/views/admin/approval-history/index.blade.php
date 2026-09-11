@extends('layouts.app')

@section('title', 'Approval History')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.approval-history.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="number" name="expense_id" class="form-control" value="{{ request('expense_id') }}" placeholder="Expense ID">
            </div>
            <div class="col-md-3">
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="submitted" {{ request('action') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="approved" {{ request('action') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('action') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Expense</th>
                        <th>Action</th>
                        <th>Actioned By</th>
                        <th>Previous Status</th>
                        <th>New Status</th>
                        <th>Rejection Reason</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr>
                            <td>#{{ $history->expense_id }}</td>
                            <td>
                                @if($history->action->value === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($history->action->value === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-info">Submitted</span>
                                @endif
                            </td>
                            <td>{{ $history->actionedBy->name }}</td>
                            <td>{{ $history->previous_status ? ucfirst($history->previous_status) : '-' }}</td>
                            <td>{{ $history->new_status ? ucfirst($history->new_status) : '-' }}</td>
                            <td>{{ $history->rejection_reason ? Str::limit($history->rejection_reason, 50) : '-' }}</td>
                            <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $histories->links() }}
    </div>
</div>
@endsection
