@extends('layouts.app')

@section('title', 'Group Financial Reports - ' . $group->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-yellow-600 to-yellow-800 text-white shadow">
        <div class="max-w-6xl mx-auto py-8 px-4">
            <h1 class="text-3xl font-bold">Financial Reports</h1>
            <p class="text-yellow-100 mt-2">{{ $group->name }}</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto py-12 px-4">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('group-admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Total Loans -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Total Loans Issued</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($stats['total_loans'], 2) }}</p>
            </div>

            <!-- Principal Paid -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Principal Paid</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($stats['total_principal_paid'], 2) }}</p>
            </div>

            <!-- Outstanding -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Outstanding Balance</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($stats['outstanding'], 2) }}</p>
            </div>

            <!-- Total Savings -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Total Savings</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($stats['total_savings'], 2) }}</p>
            </div>

            <!-- Total Members -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Total Members</p>
                <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['total_members'] }}</p>
            </div>

            <!-- Active Loans -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Active Loans</p>
                <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $stats['active_loans'] }}</p>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Financial Summary</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y">
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-700">Total Loans Issued</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-blue-600">{{ number_format($stats['total_loans'], 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-700">Total Principal Repaid</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-green-600">{{ number_format($stats['total_principal_paid'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-700">Outstanding Loan Balance</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-red-600">{{ number_format($stats['outstanding'], 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-700">Total Savings Balance</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-purple-600">{{ number_format($stats['total_savings'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-700">Active Loans</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-yellow-600">{{ $stats['active_loans'] }}</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-700">Total Group Members</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-indigo-600">{{ $stats['total_members'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
