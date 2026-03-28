<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Skripsi Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">S</div>
                <h1 class="text-2xl font-bold text-slate-900">Daftar Akun</h1>
                <p class="text-slate-500 text-sm mt-2">Mahasiswa & Dosen</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg border border-red-100">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 text-green-600 text-sm rounded-lg border border-green-100">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap</label>
                    <!-- PERBAIKAN: Value old() -->
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm @error('name') border-red-300 @enderror" 
                           placeholder="Contoh: Budi Santoso">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">NIM / NIP</label>
                        <!-- PERBAIKAN: Value old() -->
                        <input type="text" name="nip_nim" value="{{ old('nip_nim') }}" required 
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm @error('nip_nim') border-red-300 @enderror" 
                               placeholder="Nomor Induk">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Peran</label>
                        <!-- PERBAIKAN: Selected old() -->
                        <select name="role" required 
                                class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm bg-white @error('role') border-red-300 @enderror">
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="lecturer" {{ old('role') == 'lecturer' ? 'selected' : '' }}>Dosen</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Email Universitas</label>
                    <!-- PERBAIKAN: Value old() -->
                    <input type="email" name="email" value="{{ old('email') }}" required 
                           class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm @error('email') border-red-300 @enderror" 
                           placeholder="nama@univ.ac.id">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Password</label>
                    <input type="password" name="password" required 
                           class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm @error('password') border-red-300 @enderror" 
                           placeholder="Minimal 6 karakter">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required 
                           class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm" 
                           placeholder="Ulangi password">
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 transition transform active:scale-95">
                    Daftar Sekarang
                </button>
            </form>
        </div>
        <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center text-sm">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">Login disini</a>
        </div>
    </div>

</body>
</html>