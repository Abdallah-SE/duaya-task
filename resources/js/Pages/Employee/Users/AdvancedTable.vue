<template>
    <AppLayout 
        :user="user" 
        :user-settings="userSettings"
        :initial-settings="initialSettings"
        :can-control-idle-monitoring="canControlIdleMonitoring"
        :is-idle-monitoring-enabled="isIdleMonitoringEnabled"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Advanced Employee Table
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Advanced table with sorting, filtering, and pagination using @tanstack/vue-table
                    </p>
                </div>
            </div>

            <!-- TanStack Table Implementation -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Employees</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Advanced table with sorting and filtering</p>
                </div>
                
                <!-- Search and Filters -->
                <div class="px-4 py-3 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <input 
                                v-model="globalFilter"
                                type="text" 
                                placeholder="Search all columns..."
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="table.resetColumnFilters()"
                                class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Clear Filters
                            </button>
                            <button 
                                @click="table.resetSorting()"
                                class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Reset Sort
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th 
                                    v-for="header in table.getHeaderGroups()[0]?.headers" 
                                    :key="header.id"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="header.column.getToggleSortingHandler()?.($event)"
                                >
                                    <div class="flex items-center space-x-1">
                                        <span>{{ header.column.columnDef.header }}</span>
                                        <span v-if="header.column.getIsSorted() === 'asc'" class="text-indigo-600">↑</span>
                                        <span v-else-if="header.column.getIsSorted() === 'desc'" class="text-indigo-600">↓</span>
                                        <span v-else class="text-gray-400">↕</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-gray-50">
                                <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <component :is="cell.column.columnDef.cell" :cell="cell" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-700">Show</span>
                            <select 
                                v-model="table.getState().pagination.pageSize"
                                @change="table.setPageSize(Number($event.target.value))"
                                class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                            <span class="text-sm text-gray-700">entries</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button 
                                @click="table.setPageIndex(0)"
                                :disabled="!table.getCanPreviousPage()"
                                class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                First
                            </button>
                            <button 
                                @click="table.previousPage()"
                                :disabled="!table.getCanPreviousPage()"
                                class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <span class="text-sm text-gray-700">
                                Page {{ table.getState().pagination.pageIndex + 1 }} of {{ table.getPageCount() }}
                            </span>
                            <button 
                                @click="table.nextPage()"
                                :disabled="!table.getCanNextPage()"
                                class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                            <button 
                                @click="table.setPageIndex(table.getPageCount() - 1)"
                                :disabled="!table.getCanNextPage()"
                                class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Last
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
    useVueTable, 
    getCoreRowModel, 
    getSortedRowModel, 
    getFilteredRowModel, 
    getPaginationRowModel,
    createColumnHelper
} from '@tanstack/vue-table'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object,
    userSettings: Object,
    initialSettings: Object,
    canControlIdleMonitoring: Boolean,
    isIdleMonitoringEnabled: Boolean,
    users: Object,
    stats: Object
})

// Table data
const data = computed(() => props.users?.data || [])

// Global filter
const globalFilter = ref('')

// Column helper
const columnHelper = createColumnHelper()

// Define columns
const columns = [
    columnHelper.accessor('name', {
        header: 'Name',
        cell: (info) => {
            const user = info.row.original
            return `
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${user.name}</div>
                    </div>
                </div>
            `
        }
    }),
    columnHelper.accessor('email', {
        header: 'Email',
        cell: (info) => `<div class="text-sm text-gray-900">${info.getValue()}</div>`
    }),
    columnHelper.accessor('roles', {
        header: 'Role',
        cell: (info) => {
            const roles = info.getValue()
            return roles.map(role => 
                `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-1 ${getRoleBadgeClass(role.name)}">${role.name}</span>`
            ).join('')
        }
    }),
    columnHelper.accessor('activity_logs_count', {
        header: 'Activities',
        cell: (info) => `<div class="text-sm text-gray-900">${info.getValue()}</div>`
    }),
    columnHelper.accessor('idle_sessions_count', {
        header: 'Idle Sessions',
        cell: (info) => `<div class="text-sm text-gray-900">${info.getValue()}</div>`
    }),
    columnHelper.accessor('created_at', {
        header: 'Created',
        cell: (info) => `<div class="text-sm text-gray-500">${formatDate(info.getValue())}</div>`
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
        cell: (info) => {
            const user = info.row.original
            return `
                <div class="flex space-x-2">
                    <button onclick="viewUser(${user.id})" class="text-indigo-600 hover:text-indigo-900 text-sm">View</button>
                    <button onclick="editUser(${user.id})" class="text-green-600 hover:text-green-900 text-sm">Edit</button>
                    <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                </div>
            `
        }
    })
]

// Create table instance
const table = useVueTable({
    get data() { return data.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    get globalFilter() { return globalFilter.value },
    onGlobalFilterChange: (value) => { globalFilter.value = value },
    initialState: {
        pagination: {
            pageSize: 10
        }
    }
})

// Helper functions
const getRoleBadgeClass = (roleName) => {
    const classes = {
        'employee': 'bg-green-100 text-green-800',
        'admin': 'bg-purple-100 text-purple-800'
    }
    return classes[roleName] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString()
}

// Global functions for action buttons
window.viewUser = (id) => console.log('View user:', id)
window.editUser = (id) => console.log('Edit user:', id)
window.deleteUser = (id) => console.log('Delete user:', id)
</script>
