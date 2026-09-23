@php $g = $guru ?? null; @endphp

<div class="mb-3">
    <label>Nama</label>
    <input type="text" name="nama" class="form-control" value="{{ old('nama', data_get($g, 'nama', '')) }}">
    @error('nama') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label>NIP</label>
    <input type="text" name="nip" class="form-control" value="{{ old('nip', data_get($g, 'nip', '')) }}" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/\D/g,'')">
    @error('nip') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', data_get($g, 'email', '')) }}">
    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label>No HP</label>
    <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', data_get($g, 'no_hp', '')) }}" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/\D/g,'')">
</div>

<div class="mb-3">
    <label>Alamat</label>
    <textarea name="alamat" class="form-control">{{ old('alamat', data_get($g, 'alamat', '')) }}</textarea>
</div>

<div class="mb-3">
    <label>Username</label>
    <input type="text" name="username" class="form-control" value="{{ old('username', data_get($g, 'username', '')) }}">
    @error('username') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label>Password {{ $g ? '(kosongkan jika tidak diubah)' : '' }}</label>
    <input type="password" name="password" class="form-control">
</div>

<div class="mb-3">
    <label>Konfirmasi Password</label>
    <input type="password" name="password_confirmation" class="form-control">
</div>