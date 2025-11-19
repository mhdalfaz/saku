<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 shadow-lg flex justify-around py-2 z-50">
    {{-- Home --}}
    <a href="/home" class="flex-1 flex flex-col items-center text-gray-600 hover:text-black">
        <x-bladewind.icon name="home" class="w-6 h-6" />
        <span class="text-xs mt-1 text-center">Home</span>
    </a>

    {{-- Add --}}
    <a href="#" class="flex-1 flex flex-col items-center text-gray-700">
        <div class="bg-black text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md -mt-6">
            <x-bladewind.icon name="plus" class="w-6 h-6 text-white" />
        </div>
        <span class="text-xs mt-1 text-center">Tambah</span>
    </a>

    {{-- Settings --}}
    <a href="/settings" class="flex-1 flex flex-col items-center text-gray-600 hover:text-black">
        <x-bladewind.icon name="cog-6-tooth" class="w-6 h-6" />
        <span class="text-xs mt-1 text-center">Pengaturan</span>
    </a>
</nav>