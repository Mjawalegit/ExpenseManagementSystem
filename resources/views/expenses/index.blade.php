@extends('layouts.app')

@section('title', 'My Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>My Expenses</h1>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary">Create Expense</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
            </div>
            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
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
                        <th>Date</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td>{{ $expense->category->name }}</td>
                            <td>₹{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ Str::limit($expense->description, 50) }}</td>
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
                                <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-info">View</a>
                                @if($expense->status->value === 'pending')
                                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
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
