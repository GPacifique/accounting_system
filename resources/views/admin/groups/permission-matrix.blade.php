@extends('layouts.admin')

@section('title', 'Permission Matrix - ' . $group->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Permission Matrix - {{ $group->name }}</h1>
            <p class="text-gray-500 mt-1">View what actions each role can perform in this group</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.groups.role-assignments.index', $group) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Manage Role Assignments
            </a>
            <a href="{{ route('admin.groups.members.index', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Members
            </a>
        </div>
    </div>

    <!-- Permission Matrix -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Permission</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                Admin
                            </span>
                        </th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                Treasurer
                            </span>
                        </th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                Member
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $permissionLabels = [
                            'manage_members' => 'Manage Group Members',
                            'manage_finances' => 'Manage Financial Records',
                            'approve_loans' => 'Approve Loan Requests',
                            'approve_savings' => 'Approve Savings Plans',
                            'view_reports' => 'View Financial Reports',
                            'edit_group' => 'Edit Group Settings',
                            'manage_roles' => 'Manage Member Roles',
                            'audit_logs' => 'View Audit Logs',
                        ];
                    @endphp

                    @foreach ($permissionLabels as $permKey => $permLabel)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $permLabel }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($permissionMatrix['admin'][$permKey] ?? false)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-red-400 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($permissionMatrix['treasurer'][$permKey] ?? false)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-red-400 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($permissionMatrix['member'][$permKey] ?? false)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-red-400 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Role Descriptions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Admin Role -->
            <div class="border-l-4 border-purple-500 pl-4">
                <h4 class="font-bold text-purple-900 mb-2">Group Administrator</h4>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>✓ Full control over all group operations</li>
                    <li>✓ Add and remove members</li>
                    <li>✓ Assign roles to members</li>
                    <li>✓ Approve all financial transactions</li>
                    <li>✓ Edit group settings and information</li>
                    <li>✓ View comprehensive audit logs</li>
                </ul>
            </div>

            <!-- Treasurer Role -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h4 class="font-bold text-blue-900 mb-2">Treasurer</h4>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>✓ Manage all financial records</li>
                    <li>✓ Record loans and repayments</li>
                    <li>✓ Process savings deposits/withdrawals</li>
                    <li>✓ Approve loan and savings requests</li>
                    <li>✓ Generate financial reports</li>
                    <li>✓ View transaction history</li>
                </ul>
            </div>

            <!-- Member Role -->
            <div class="border-l-4 border-gray-500 pl-4">
                <h4 class="font-bold text-gray-900 mb-2">Regular Member</h4>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>✓ View own account information</li>
                    <li>✓ Request loans within limits</li>
                    <li>✓ Make savings contributions</li>
                    <li>✓ View personal transactions</li>
                    <li>✓ Participate in group activities</li>
                    <li>✗ No administrative privileges</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Current Members by Role -->
    <div class="mt-8">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Members by Role</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $adminCount = $members->where('role', 'admin')->count();
                $treasurerCount = $members->where('role', 'treasurer')->count();
                $memberCount = $members->where('role', 'member')->count();
            @endphp

            <!-- Admins -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <h4 class="font-bold text-purple-900 mb-3">Administrators ({{ $adminCount }})</h4>
                <ul class="space-y-2 text-sm">
                    @forelse ($members->where('role', 'admin') as $admin)
                        <li class="text-gray-700">
                            <span class="font-medium">{{ $admin->user->name }}</span>
                            <span class="text-xs text-gray-500">({{ $admin->user->email }})</span>
                        </li>
                    @empty
                        <li class="text-gray-500 italic">No administrators assigned</li>
                    @endforelse
                </ul>
            </div>

            <!-- Treasurers -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-bold text-blue-900 mb-3">Treasurers ({{ $treasurerCount }})</h4>
                <ul class="space-y-2 text-sm">
                    @forelse ($members->where('role', 'treasurer') as $treasurer)
                        <li class="text-gray-700">
                            <span class="font-medium">{{ $treasurer->user->name }}</span>
                            <span class="text-xs text-gray-500">({{ $treasurer->user->email }})</span>
                        </li>
                    @empty
                        <li class="text-gray-500 italic">No treasurers assigned</li>
                    @endforelse
                </ul>
            </div>

            <!-- Members -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-bold text-gray-900 mb-3">Members ({{ $memberCount }})</h4>
                <ul class="space-y-2 text-sm max-h-32 overflow-y-auto">
                    @forelse ($members->where('role', 'member')->take(5) as $member)
                        <li class="text-gray-700">
                            <span class="font-medium">{{ $member->user->name }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 italic">No regular members</li>
                    @endforelse
                    @if ($memberCount > 5)
                        <li class="text-xs text-gray-500 pt-2 italic">+{{ $memberCount - 5 }} more...</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
