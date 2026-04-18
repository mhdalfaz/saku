@props(['name' => 'loan-tabs'])

<div class="custom-tab-list" data-tab-group="{{ $name }}">
    <button class="custom-tab-item active" data-tab="single-pay" onclick="switchCustomTab('{{ $name }}', 'single-pay')">
        <span class="tab-label">Bayar Pinjaman</span>
    </button>
    <button class="custom-tab-item" data-tab="all-pays" onclick="switchCustomTab('{{ $name }}', 'all-pays')">
        <span class="tab-label">Bayar Semua Pinjaman</span>
    </button>
</div>

<style>
    .custom-tab-list {
        display: flex;
        gap: 8px;
        padding: 8px;
        background: #e2e8f0;
        border-radius: 12px;
        border: solid 1px rgb(226 232 240/var(--tw-border-opacity, 1));
        width: 100%;
    }

    .custom-tab-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border: none;
        border-radius: 10px;
        background: transparent;
        color: #6b7280;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
    }

    .custom-tab-item:hover:not(.active):not(:disabled) {
        background: rgba(255, 255, 255, 0.5);
        color: #374151;
    }

    .custom-tab-item.active {
        background: white;
        color: #1a202c;
    }

    .tab-icon {
        display: flex;
        align-items: center;
    }
</style>

<script>
    function switchCustomTab(groupName, tabName) {
        const container = document.querySelector(`[data-tab-group="${groupName}"]`);
        const buttons = container.querySelectorAll('.custom-tab-item');
        const tabContents = document.querySelectorAll(`[data-tab-content="${groupName}"]`);

        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.tab === tabName) {
                btn.classList.add('active');
            }
        });

        tabContents.forEach(content => {
            content.style.display = 'none';
            if (content.dataset.tab === tabName) {
                content.style.display = 'block';
            }
        });
    }
</script>