@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div x-data="{
    showPasswordModal: false,
    currentPassword: '',
    password: '',
    password_confirmation: '',
    isProcessing: false
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Profil Pengguna</h1>
        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.
                    </p>
                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-800 mb-4">Informasi Akun</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Nama</p>
                    <p class="font-medium">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Username</p>
                    <p class="font-medium">{{ $user->username }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Email</p>
                    <p class="font-medium">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Role</p>
                    <p class="font-medium">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($user->role) }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="space-x-2">
                <button type="button" @click="showPasswordModal = true" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-key mr-2"></i> Ganti Password
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-800 mb-4">Edit Profil</h2>

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-600">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-600">*</span></label>
                        <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-600">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                </div>

                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div x-show="showPasswordModal" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('profile.password.update') }}" method="POST" @submit="isProcessing = true">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    Ganti Password
                                </h3>
                                <div class="mt-2 space-y-4">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini <span class="text-red-600">*</span></label>
                                        <input type="password" name="current_password" id="current_password" x-model="currentPassword" required 
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                    </div>
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-red-600">*</span></label>
                                        <input type="password" name="password" id="password" x-model="password" required
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-red-600">*</span></label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" x-model="password_confirmation" required
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                                :disabled="isProcessing">
                            <span x-show="!isProcessing">Simpan</span>
                            <span x-show="isProcessing">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Memproses...
                            </span>
                        </button>
                        <button type="button" @click="showPasswordModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection