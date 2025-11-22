<x-layouts.app title="Home">

    <h1 class="text-2xl font-bold mb-4">Home</h1>

    {{-- Total Uang Masuk / Keluar Bulan Ini --}}
    <x-bladewind::statistic number="Rp3.450.000" label="Uang Masuk / Keluar">
        <x-slot name="icon">
            <div class="bg-blue-500 p-3 rounded-full flex items-center justify-center">
                <x-bladewind::icon name="arrows-right-left" class="!h-8 !w-8 text-white" />
            </div>
        </x-slot>
    </x-bladewind::statistic>

    {{-- Total Meminjamkan --}}
    <x-bladewind::statistic number="Rp1.230.000" label="Total Meminjamkan">
        <x-slot name="icon">
            <div class="bg-green-500 p-3 rounded-full flex items-center justify-center">
                <x-bladewind::icon name="banknotes" class="!h-8 !w-8 text-white" />
            </div>
        </x-slot>
    </x-bladewind::statistic>

    {{-- Total Sisa Belum Dibayar --}}
    <x-bladewind::statistic number="Rp570.000" label="Belum Dibayar Peminjam">
        <x-slot name="icon">
            <div class="bg-red-500 p-3 rounded-full flex items-center justify-center">
                <x-bladewind::icon name="clock" class="!h-8 !w-8 text-white" />
            </div>
        </x-slot>
    </x-bladewind::statistic>

    <h1 class="text-2xl font-bold mb-4">Lihat Selengkapnya</h1>
    {{-- Go To --}}
    <div class="grid grid-cols-2 gap-5">
        <x-bladewind::card class="cursor-pointer hover:shadow-gray-300 text-center" onclick="window.location.href='/cash-flow'">
            <div>
                <x-bladewind::icon name="arrows-right-left" class="!h-12 !w-12 text-slate-400" type="solid" />
            </div>
            <span class="font-bold text-slate-400">CASH FLOW</span>
        </x-bladewind::card>

        <x-bladewind::card class="cursor-pointer hover:shadow-gray-300 text-center" onclick="window.location.href='/loans'">
            <div>
                <x-bladewind::icon name="banknotes" class="!h-12 !w-12 text-slate-400" type="solid" />
            </div>
            <span class="font-bold text-slate-400">LOAN</span>
        </x-bladewind::card>
    </div>

    <div class="mb-[70px]"></div>

</x-layouts.app>