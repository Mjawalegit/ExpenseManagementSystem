@extends('layouts.app')

@section('title', 'Expenses for Approval')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Expenses for Approval</h1>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('manager.expenses.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
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
                        <th>Employee</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->user->name }}</td>
                            <td>{{ $expense->category->name }}</td>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td>₹{{ number_format($expense->amount, 2) }}</td>
                            <td>
                                @if($expense->status->value === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($expense->status->value === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('manager.expenses.show', $expense) }}" class="btn btn-sm btn-info">View</a>
                                @if($expense->status->value === 'pending' && $expense->user_id !== auth()->id())
                                    <form action="{{ route('manager.expenses.approve', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve this expense?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $expense->id }}">Reject</button>
                                @endif
                            </td>
                        </tr>

                        <div class="modal fade" id="rejectModal{{ $expense->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Expense</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('manager.expenses.reject', $expense) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="rejection_reason_{{ $expense->id }}" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="rejection_reason_{{ $expense->id }}" name="rejection_reason" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $expenses->links() }}
    </div>
</div>
@endsection
