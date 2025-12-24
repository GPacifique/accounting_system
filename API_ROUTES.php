<?php

// Add these routes to your routes/api.php file

use App\Http\Controllers\LoanController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Group Routes
    Route::apiResource('groups', GroupController::class);
    Route::post('groups/{group}/members', [GroupController::class, 'addMember']);
    Route::delete('groups/{group}/members/{member}', [GroupController::class, 'removeMember']);

    // Loan Routes
    Route::prefix('groups/{group}/loans')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('loans.index');
        Route::post('/', [LoanController::class, 'store'])->name('loans.store');
        Route::get('{loan}', [LoanController::class, 'show'])->name('loans.show');
        Route::post('{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
        Route::post('{loan}/disburse', [LoanController::class, 'disburse'])->name('loans.disburse');
        Route::post('{loan}/payment', [LoanController::class, 'recordPayment'])->name('loans.payment');
        Route::post('{loan}/default', [LoanController::class, 'markDefault'])->name('loans.default');
        Route::get('statistics', [LoanController::class, 'statistics'])->name('loans.statistics');
        Route::get('report/defaults', [LoanController::class, 'defaultReport'])->name('loans.defaults');
    });

    // Savings Routes
    Route::prefix('groups/{group}/savings')->group(function () {
        Route::get('/', [SavingsController::class, 'index'])->name('savings.index');
        Route::get('member/{member}', [SavingsController::class, 'show'])->name('savings.show');
        Route::post('member/{member}/deposit', [SavingsController::class, 'deposit'])->name('savings.deposit');
        Route::post('member/{member}/withdraw', [SavingsController::class, 'withdraw'])->name('savings.withdraw');
        Route::post('member/{member}/interest', [SavingsController::class, 'addInterest'])->name('savings.interest');
        Route::get('member/{member}/statement', [SavingsController::class, 'memberStatement'])->name('savings.statement');
    });

    // Reporting Routes
    Route::prefix('groups/{group}/reports')->group(function () {
        Route::get('financial-summary', [ReportController::class, 'financialSummary'])->name('reports.financial');
        Route::get('loan-metrics', [ReportController::class, 'loanMetrics'])->name('reports.loans');
        Route::get('member/{member}/statement', [ReportController::class, 'memberStatement'])->name('reports.member');
        Route::get('defaults', [ReportController::class, 'defaultReport'])->name('reports.defaults');
        Route::get('export/excel', [ReportController::class, 'exportExcel'])->name('reports.export');
    });
});

/**
 * API ENDPOINT DOCUMENTATION
 *
 * LOAN ENDPOINTS:
 * ===============
 *
 * 1. List all loans in a group
 *    GET /api/groups/{group}/loans
 *
 * 2. Create a new loan
 *    POST /api/groups/{group}/loans
 *    {
 *        "member_id": 1,
 *        "principal_amount": 10000,
 *        "monthly_charge": 500,
 *        "duration_months": 12,
 *        "notes": "Business loan"
 *    }
 *
 * 3. Get loan details
 *    GET /api/groups/{group}/loans/{loan}
 *
 * 4. Approve a loan
 *    POST /api/groups/{group}/loans/{loan}/approve
 *
 * 5. Disburse loan (give funds to member)
 *    POST /api/groups/{group}/loans/{loan}/disburse
 *
 * 6. Record loan payment
 *    POST /api/groups/{group}/loans/{loan}/payment
 *    {
 *        "principal_paid": 400,
 *        "charges_paid": 100,
 *        "payment_method": "cash",
 *        "notes": "Monthly payment"
 *    }
 *
 * 7. Mark loan as defaulted
 *    POST /api/groups/{group}/loans/{loan}/default
 *    {
 *        "reason": "Member lost job"
 *    }
 *
 * 8. Get loan statistics
 *    GET /api/groups/{group}/loans/statistics
 *
 * 9. Get default report
 *    GET /api/groups/{group}/loans/report/defaults
 *
 *
 * SAVINGS ENDPOINTS:
 * ==================
 *
 * 1. List all savings in group
 *    GET /api/groups/{group}/savings
 *
 * 2. Get member's savings
 *    GET /api/groups/{group}/savings/member/{member}
 *
 * 3. Record deposit
 *    POST /api/groups/{group}/savings/member/{member}/deposit
 *    {
 *        "amount": 1000,
 *        "description": "Monthly contribution"
 *    }
 *
 * 4. Record withdrawal
 *    POST /api/groups/{group}/savings/member/{member}/withdraw
 *    {
 *        "amount": 500,
 *        "description": "Emergency withdrawal"
 *    }
 *
 * 5. Add interest
 *    POST /api/groups/{group}/savings/member/{member}/interest
 *    {
 *        "amount": 50
 *    }
 *
 * 6. Get member statement
 *    GET /api/groups/{group}/savings/member/{member}/statement
 *    Query params: ?from=2025-01-01&to=2025-12-31
 *
 *
 * REPORTING ENDPOINTS:
 * ====================
 *
 * 1. Financial summary
 *    GET /api/groups/{group}/reports/financial-summary
 *
 * 2. Loan metrics
 *    GET /api/groups/{group}/reports/loan-metrics
 *
 * 3. Member statement
 *    GET /api/groups/{group}/reports/member/{member}/statement
 *
 * 4. Default report
 *    GET /api/groups/{group}/reports/defaults
 *
 * 5. Export to Excel
 *    GET /api/groups/{group}/reports/export/excel
 */
