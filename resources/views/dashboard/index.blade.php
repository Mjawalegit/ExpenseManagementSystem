@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Expenses</h5>
                <p class="card-text fs-4">₹{{ number_format($stats['total_expenses'] ?? 0, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Pending</h5>
                <p class="card-text fs-4">{{ $stats['pending_expenses'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Approved</h5>
                <p class="card-text fs-4">{{ $stats['approved_expenses'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Rejected</h5>
                <p class="card-text fs-4">{{ $stats['rejected_expenses'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Current Month</h5>
                <p class="card-text fs-4">₹{{ number_format($stats['current_month_expenses'] ?? 0, 2) }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Monthly Expense Summary ({{ date('Y') }})</div>
    <div class="card-body">
        <canvas id="monthlyChart" height="100"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($monthlyData);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [{
                label: 'Expenses (₹)',
                data: monthlyData.map(item => item.total),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection
