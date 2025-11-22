<nav {{ $attributes->merge(['class' => 'w-full bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center shadow-sm']) }}>
    <div class="text-xl font-semibold">Saku</div>

    <div class="flex items-center gap-4">
        <a href="/home" class="text-sm text-gray-700 hover:text-black">Home</a>
        <a href="/choose" class="text-sm text-gray-700 hover:text-black">Buat baru</a>
        <a href="/settings" class="text-sm text-gray-700 hover:text-black">Setting</a>
    </div>
</nav>