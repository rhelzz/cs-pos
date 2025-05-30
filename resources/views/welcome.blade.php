<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS System</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex flex-col min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md text-center mb-8">
            <h1 class="text-4xl font-bold text-blue-600 mb-2">POS System</h1>
            <p class="text-gray-600 text-lg">Sistem Manajemen Penjualan</p>
        </div>
        
        <div class="w-full max-w-md space-y-4">
            <a href="{{ route('login') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-lg focus:outline-none focus:shadow-outline text-center">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </a>
            
            <a href="{{ route('register') }}" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-4 px-4 rounded-lg focus:outline-none focus:shadow-outline text-center">
                <i class="fas fa-user-plus mr-2"></i> Register
            </a>
        </div>
        
        <div class="mt-12 text-center text-gray-500">
            <p>&copy; {{ date('Y') }} POS System</p>
        </div>
    </div>
</body>
</html>