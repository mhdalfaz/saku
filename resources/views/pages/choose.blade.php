<x-layouts.menu title="Tambah">

    <h1 class="text-2xl font-bold mb-4">Pilih Fitur yang Ingin Ditambahkan</h1>

    <div class="grid grid-cols-1 gap-4 mx-auto">

        {{-- Cash Flow --}}
        <a href="/cash-flow/create"
            class="block bg-white border border-gray-200 rounded-md p-6 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <x-bladewind.icon name="arrows-right-left" class="w-10 h-10 text-gray-700" />
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Cash Flow</h2>
                <p class="text-sm text-gray-500">Tambah pemasukan atau pengeluaran</p>
            </div>
        </a>

        {{-- Loans --}}
        <a href="/loans/create"
            class="block bg-white border border-gray-200 rounded-md p-6 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <x-bladewind.icon name="banknotes" class="w-10 h-10 text-gray-700" />
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Pinjaman</h2>
                <p class="text-sm text-gray-500">Tambah pinjaman ke orang lain</p>
            </div>
        </a>

    </div>

</x-layouts.menu>