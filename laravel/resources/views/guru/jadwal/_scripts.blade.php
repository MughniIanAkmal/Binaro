<script>
    function pilihHari(hari) {
        document.querySelectorAll('.tab-btn-hari').forEach(function(btn) {
            btn.style.backgroundColor = '';
            btn.style.color = '';
            btn.style.boxShadow = '';
        });
        var activeBtn = document.getElementById('tab-btn-' + hari);
        if (activeBtn) {
            activeBtn.style.backgroundColor = '#13527D';
            activeBtn.style.color = 'white';
            activeBtn.style.boxShadow = '0 1px 3px rgba(0,0,0,0.15)';
        }
        document.querySelectorAll('.kartu-hari').forEach(function(card) {
            card.style.display = (hari === 'semua' || card.dataset.hari === hari) ? '' : 'none';
        });
    }
</script>
<style>
    @media print {
        aside, .print\:hidden { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        .kartu-hari { break-inside: avoid; margin-bottom: 1.5rem; border: 1px solid #cbd5e1 !important; }
    }
</style>