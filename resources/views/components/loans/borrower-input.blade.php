{{-- Card untuk pilih/tambah peminjam --}}
<x-bladewind::card>
    <label class="block text-sm font-medium mb-1">Peminjam</label>

    <div class="flex items-start gap-2">
        <div class="flex-1">
            <x-bladewind::select html_id="borrowerSelect" name="borrower_id" placeholder="Pilih peminjam"
                label_key="name" value_key="id" searchable="true" image_key="avatar" :data="$borrowers" />
            <p id="error-borrower_id" class="text-red-600 text-sm alert-error" style="margin-top: 16px"></p>
        </div>

        {{-- <x-bladewind::button onclick="showModal('new-borrower')" class="h-[45px] flex px-3">
            <x-bladewind::icon name="plus" type="solid" />
        </x-bladewind::button> --}}
    </div>

    <x-bladewind::modal name="new-borrower" title="Tambah Peminjam" show_action_buttons="false">
        <div>
            <x-bladewind::input label="Nama Peminjam" placeholder="Masukkan nama" id="newBorrowerName" />

            <x-bladewind::button class="mt-4" onclick="saveBorrower()">
                Simpan
            </x-bladewind::button>
        </div>
    </x-bladewind::modal>

</x-bladewind::card>