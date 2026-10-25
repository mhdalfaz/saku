<x-layouts.app title="Home">

    <h1 class="text-2xl font-bold mb-4">Home</h1>

    {{-- Cash Flow (pemasukan - pengeluaran) --}}
    <x-bladewind::statistic id="statCashFlow" number="-" label="Uang Masuk / Keluar" style="padding: 20px">
        <x-slot name="icon">
            <div class="bg-blue-500 p-3 rounded-full flex items-center justify-center">
                <x-bladewind::icon name="arrows-right-left" class="!h-8 !w-8 text-white" type="solid" />
            </div>
        </x-slot>
    </x-bladewind::statistic>

    {{-- Total Meminjamkan --}}
    <x-bladewind::statistic id="statTotalLent" number="-" label="Total Meminjamkan" style="padding: 20px">
        <x-slot name="icon">
            <div class="bg-green-500 p-3 rounded-full flex items-center justify-center">
                <x-bladewind::icon name="banknotes" class="!h-8 !w-8 text-white" type="solid" />
            </div>
        </x-slot>
    </x-bladewind::statistic>

    {{-- Sisa Belum Dibayar --}}
    <x-bladewind::statistic id="statOutstanding" number="-" label="Belum Dibayar Peminjam" style="padding: 20px">
        <x-slot name="icon">
            <div class="bg-red-500 p-3 rounded-full flex items-center justify-center">
                <x-bladewind::icon name="clock" class="!h-8 !w-8 text-white" type="solid" />
            </div>
        </x-slot>
    </x-bladewind::statistic>

    {{-- Saldo Simpanan --}}
    <x-bladewind::statistic id="statSavings" number="-" label="Saldo Simpanan" style="padding: 20px">
        <x-slot name="icon">
            <div class="bg-indigo-500 p-3 rounded-full flex items-center justify-center">
                <x-bladewind::icon name="archive-box" class="!h-8 !w-8 text-white" type="solid" />
            </div>
        </x-slot>
    </x-bladewind::statistic>

    <h1 class="text-2xl font-bold mb-4">Lihat Selengkapnya</h1>
    {{-- Go To --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-5">
        <x-bladewind::card class="cursor-pointer hover:shadow-gray-300 text-center"
            onclick="window.location.href='/cash-flow'">
            <div>
                <x-bladewind::icon name="arrows-right-left" class="!h-8 !w-8 text-slate-400" type="solid" />
            </div>
            <span class="font-bold text-slate-400 text-sm">CASH FLOW</span>
        </x-bladewind::card>

        <x-bladewind::card class="cursor-pointer hover:shadow-gray-300 text-center"
            onclick="window.location.href='/loans'">
            <div>
                <x-bladewind::icon name="banknotes" class="!h-8 !w-8 text-slate-400" type="solid" />
            </div>
            <span class="font-bold text-slate-400 text-sm">LOAN</span>
        </x-bladewind::card>

        <x-bladewind::card class="cursor-pointer hover:shadow-gray-300 text-center"
            onclick="window.location.href='/savings'">
            <div>
                <x-bladewind::icon name="archive-box" class="!h-8 !w-8 text-slate-400" type="solid" />
            </div>
            <span class="font-bold text-slate-400 text-sm">SIMPANAN</span>
        </x-bladewind::card>
    </div>

    <div class="mb-[70px]"></div>

</x-layouts.app>

<script>
    (async function () {
        const token = localStorage.getItem("token");
        if (!token) return;

        try {
            const res = await fetch("/api/dashboard/summary", {
                headers: { "Authorization": "Bearer " + token }
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || "Gagal memuat ringkasan");

            const setFigure = (id, value) => {
                const el = document.querySelector(`#${id} .figure`);
                if (el) el.textContent = formatIDR(value);
            };

            setFigure("statCashFlow", json.data.cash_flow_net);
            setFigure("statTotalLent", json.data.total_lent);
            setFigure("statOutstanding", json.data.loan_outstanding);
            setFigure("statSavings", json.data.savings_balance);
        } catch (err) {
            console.error(err);
        }
    })();
</script>
