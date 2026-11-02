{{--
    resources/views/rpp_guru/modals.blade.php
    Modularized Modal Structure for RPP & Modul Ajar (Kurikulum Merdeka)
    File-file komponen dipisah ke dalam folder: resources/views/rpp_guru/partials/
--}}

<!-- Modal Tambah Modul Ajar (RPP) Baru -->
@include('rpp_guru.partials.modal_tambah')

<!-- Modal Edit Modul Ajar (RPP) -->
@include('rpp_guru.partials.modal_edit')

<!-- Modal Detail Modul Ajar (RPP) -->
@include('rpp_guru.partials.modal_detail')

<!-- Skrip Interaktif: Validasi Roster Jadwal & Hitung JP Otomatis (1 JP = 45 Menit) -->
@include('rpp_guru.partials.scripts')
