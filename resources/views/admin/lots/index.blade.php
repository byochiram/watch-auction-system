{{-- admin/lots/index.blade.php --}}
<x-app-layout title="Lot Lelang">
  <div class="py-6" x-data="lotPage('{{ request('tab','scheduled') }}')" x-init="init()">
    <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Event Lelang</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl sm:rounded-lg">

          {{-- HEADER --}}
          <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
              <div>
                  <h1 class="text-lg font-semibold text-slate-900">
                      Daftar Lelang
                  </h1>
                  <p class="text-sm text-slate-500">
                      Kelola jadwal lelang dan status lot.
                  </p>
              </div>

              <div class="flex gap-2">
                  <button type="button"
                          class="rounded-lg bg-blue-600 text-white px-3 py-2 text-sm hover:bg-blue-500"
                          @click="openCreate()">
                      + Tambah Lelang
                  </button>
              </div>
          </div>

        {{-- STAT CARDS --}}
        <div class="px-6 pt-4 pb-1 grid grid-cols-3 md:grid-cols-7 gap-3 text-sm">
            {{-- Akan Dimulai --}}
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-3">
                <div class="text-xs text-slate-500">Akan Dimulai</div>
                <div class="mt-1 text-xl font-semibold text-slate-900">
                    {{ number_format($lotStats['scheduled'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            {{-- Sedang Berjalan --}}
            <div class="rounded-xl border border-blue-100 bg-blue-50/60 px-3 py-3">
                <div class="text-xs text-blue-700">Sedang Berjalan</div>
                <div class="mt-1 text-xl font-semibold text-blue-800">
                    {{ number_format($lotStats['active'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            {{-- Dibatalkan --}}
            <div class="rounded-xl border border-red-100 bg-red-50/60 px-3 py-3">
                <div class="text-xs text-red-700">Dibatalkan</div>
                <div class="mt-1 text-xl font-semibold text-red-800">
                    {{ number_format($lotStats['cancelled'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            {{-- Berakhir Tanpa Bid (netral / abu) --}}
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                <div class="text-xs text-slate-600">Berakhir Tanpa Bid</div>
                <div class="mt-1 text-xl font-semibold text-slate-800">
                    {{ number_format($lotStats['ended_total'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            {{-- Menunggu Bayar (warning / kuning) --}}
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-3">
                <div class="text-xs text-amber-700">Menunggu Bayar</div>
                <div class="mt-1 text-xl font-semibold text-amber-800">
                    {{ number_format($lotStats['pending'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            {{-- Terjual (success / hijau) --}}
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-3">
                <div class="text-xs text-emerald-700">Terjual</div>
                <div class="mt-1 text-xl font-semibold text-emerald-800">
                    {{ number_format($lotStats['awarded'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            {{-- Gagal Bayar (error / merah-rose) --}}
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-3">
                <div class="text-xs text-rose-700">Gagal Bayar</div>
                <div class="mt-1 text-xl font-semibold text-rose-800">
                    {{ number_format($lotStats['unsold'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- TABS STATUS --}}
        <div class="flex justify-center mt-3 mb-2 px-3">
            <div class="inline-flex gap-2 overflow-x-auto max-w-full whitespace-nowrap text-xs sm:text-sm">
                <button
                    @click="changeTab('scheduled')"
                    class="px-2 sm:px-3 py-1 sm:py-2 rounded-full"
                    :class="tab === 'scheduled'
                        ? 'bg-slate-900 text-white'
                        : 'bg-slate-100 text-slate-700'">
                    Akan Dimulai
                </button>

                <button
                    @click="changeTab('active')"
                    class="px-2 sm:px-3 py-1 sm:py-2 rounded-full"
                    :class="tab === 'active'
                        ? 'bg-slate-900 text-white'
                        : 'bg-slate-100 text-slate-700'">
                    Sedang Berjalan
                </button>

                <button
                    @click="changeTab('cancelled')"
                    class="px-2 sm:px-3 py-1 sm:py-2 rounded-full"
                    :class="tab === 'cancelled'
                        ? 'bg-slate-900 text-white'
                        : 'bg-slate-100 text-slate-700'">
                    Dibatalkan
                </button>

                <button
                    @click="changeTab('ended')"
                    class="px-2 sm:px-3 py-1 sm:py-2 rounded-full"
                    :class="tab === 'ended'
                        ? 'bg-slate-900 text-white'
                        : 'bg-slate-100 text-slate-700'">
                    Sudah Berakhir
                </button>
            </div>
        </div>

        {{-- FILTER BAR --}}
        <div class="px-6 pt-4 pb-3">
            <form x-ref="filterForm"
                  class="grid gap-3 md:grid-cols-6 lg:grid-cols-8 text-[15px] items-center"
                  @submit.prevent>
                <input type="hidden" name="tab" :value="tab">

                {{-- search --}}
                <div class="md:col-span-3 lg:col-span-3">
                    <input type="text"
                          name="search"
                          value="{{ request('search') }}"
                          placeholder="Cari brand atau model…"
                          class="w-full rounded-lg border-slate-300 text-[15px]"
                          @input.debounce.500ms="applyFilter()" />
                </div>

                {{-- sort --}}
                <div>
                    <select name="sort"
                            class="w-full rounded-lg border-slate-300 text-[15px]"
                            @change="applyFilter()">
                        <option value="">Urut default</option>
                        <option value="start_asc"  @selected(request('sort') === 'start_asc')>Mulai paling awal</option>
                        <option value="start_desc" @selected(request('sort') === 'start_desc')>Mulai terbaru</option>
                        <option value="end_desc"   @selected(request('sort') === 'end_desc')>Selesai terbaru</option>
                    </select>
                </div>

                {{-- filter status khusus tab ended --}}
                <div x-show="tab === 'ended'">
                    <select name="end_status"
                            class="w-full rounded-lg border-slate-300 text-[15px]"
                            @change="applyFilter()">
                        <option value="">Semua status</option>
                        <option value="ENDED"   @selected(request('end_status') === 'ENDED')>ENDED (tanpa pemenang)</option>
                        <option value="PENDING" @selected(request('end_status') === 'PENDING')>PENDING (menunggu bayar)</option>
                        <option value="AWARDED" @selected(request('end_status') === 'AWARDED')>AWARDED (terjual)</option>
                        <option value="UNSOLD"  @selected(request('end_status') === 'UNSOLD')>UNSOLD (gagal bayar)</option>
                    </select>
                </div>
                {{-- per page --}}
                <div class="flex items-center gap-2">
                    <select name="per"
                            class="rounded-lg border-slate-300 text-[15px]"
                            @change="applyFilter()">
                        @foreach([10,25,50] as $n)
                            <option value="{{ $n }}" @selected((int)request('per',10) === $n)>{{ $n }}/Hal</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        {{-- TABLE WRAPPER --}}
        <div class="px-6 pb-4">
            <div x-ref="tableWrap">
                <div x-show="tab === 'scheduled'">
                    @include('admin.lots._table', [
                        'lots'             => $scheduledLots,
                        'showActions'      => true,
                        'showStatusColumn' => false,
                        'showCancelledAt'  => false,
                    ])
                </div>

                <div x-show="tab === 'active'">
                    @include('admin.lots._table', [
                        'lots'             => $activeLots,
                        'showActions'      => true,
                        'showStatusColumn' => false,
                        'showCancelledAt'  => false,
                    ])
                </div>

                <div x-show="tab === 'cancelled'">
                    @include('admin.lots._table', [
                        'lots'             => $cancelledLots,
                        'showActions'      => false,
                        'showStatusColumn' => false,
                        'showCancelledAt'  => true,   // kolom dibatalkan
                    ])
                </div>

                <div x-show="tab === 'ended'">
                    @include('admin.lots._table', [
                        'lots'             => $endedLots,
                        'showActions'      => false,
                        'showStatusColumn' => true,   // status runtime
                        'showCancelledAt'  => false,
                    ])
                </div>
            </div>
        </div>
      </div>
    </div>


    {{-- MODAL: CREATE --}}
    <div x-show="createOpen"
        x-cloak
        class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto">
        <div class="absolute inset-0 bg-black/50" @click="createOpen = false"></div>
        <div class="relative w-full max-w-xl mx-4 sm:mx-0 mt-10 mb-6 rounded-xl bg-white shadow-xl p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Tambah Lelang Baru</h3>
                <button @click="createOpen = false">✕</button>
            </div>

            <form x-ref="createForm" method="POST" action="{{ route('lots.store') }}" novalidate
                @submit.prevent="confirmAndSubmit($el,'Konfirmasi','Simpan lot baru?','Simpan')">
                @csrf
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-sm font-medium">
                            Produk <span class="text-red-500">*</span>
                        </label>

                        @if($productsAvailable->isEmpty())
                            <select class="w-full rounded border-gray-300 bg-gray-100 text-slate-500" disabled>
                                <option>
                                    Tidak ada produk tersedia.
                                    Silakan tambah produk terlebih dahulu.
                                </option>
                            </select>
                        @else
                            <select name="product_id" class="w-full rounded border-gray-300" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($productsAvailable as $p)
                                    @php
                                        $lastLot = $p->auctionLots->first();
                                        $suffix  = '';

                                        if ($lastLot) {
                                            $st = $lastLot->runtime_status;
                                            if (in_array($st, ['CANCELLED','ENDED','UNSOLD'])) {
                                                $suffix = " ({$st})";
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $p->id }}">
                                        #{{ $p->id }} - {{ $p->brand }} - {{ $p->model }}{{ $suffix }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-medium">
                                Harga Awal <span class="text-red-500">*</span>
                            </label>
                            <input type="number" min="0" step="0.01" name="start_price"
                                class="w-full rounded border-gray-300" required/>
                        </div>
                        <div>
                            <label class="text-sm font-medium">
                                Increment <span class="text-red-500">*</span>
                            </label>
                            <input type="number" min="0.01" step="0.01" name="increment"
                                class="w-full rounded border-gray-300" required/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-medium">
                                Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="start_at"
                                class="w-full rounded border-gray-300" required/>
                        </div>
                        <div>
                            <label class="text-sm font-medium">
                                Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="end_at"
                                class="w-full rounded border-gray-300" required/>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 rounded bg-slate-100" @click="createOpen = false">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 rounded bg-yellow-500 font-semibold text-slate-900
                                @if($productsAvailable->isEmpty()) opacity-60 cursor-not-allowed @endif"
                            @if($productsAvailable->isEmpty()) disabled @endif>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: EDIT --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto">
        <div class="absolute inset-0 bg-black/50" @click="closeEdit()"></div>
        <div class="relative w-full max-w-xl mx-4 sm:mx-0 mt-10 mb-6 rounded-xl bg-white shadow-xl p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Edit Lelang</h3>
                <button @click="closeEdit()">✕</button>
            </div>

            <form x-ref="editForm" method="POST" :action="updateAction" novalidate
                @submit.prevent="confirmAndSubmit($el,'Konfirmasi','Simpan perubahan lot?','Simpan')">
                @csrf @method('PUT')
                <input type="hidden" name="tab" :value="tab">

                <div class="grid grid-cols-1 gap-4">
                    {{-- PRODUK --}}
                    <div>
                        <label class="text-sm font-medium">
                            Produk <span class="text-red-500">*</span>
                        </label>

                        @if($editProducts->isEmpty())
                            <select class="w-full rounded border-gray-300 bg-gray-100 text-slate-500" disabled>
                                <option>Tidak ada produk yang dapat dipilih.</option>
                            </select>
                        @else
                            <select name="product_id"
                                    class="w-full rounded border-gray-300"
                                    x-model="form.product_id"
                                    :disabled="isActiveLot"
                                    required>
                                @foreach($editProducts as $p)
                                    <option value="{{ $p->id }}">
                                        #{{ $p->id }} - {{ $p->brand }} - {{ $p->model }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- HARGA & INCREMENT --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-medium">
                                Harga Awal <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                name="start_price"
                                x-model="form.start_price"
                                class="w-full rounded border-gray-300"
                                required
                                :disabled="isActiveLot"
                                oninput="this.value = this.value
                                        .replace(/[^0-9]/g,'')
                                        .replace(/\B(?=(\d{3})+(?!\d))/g,'.')" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">
                                Increment <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                name="increment"
                                x-model="form.increment"
                                class="w-full rounded border-gray-300"
                                required
                                :disabled="isActiveLot"
                                oninput="this.value = this.value
                                        .replace(/[^0-9]/g,'')
                                        .replace(/\B(?=(\d{3})+(?!\d))/g,'.')" />
                        </div>
                    </div>

                    {{-- TANGGAL --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-medium">
                                Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local"
                                name="start_at"
                                x-model="form.start_at"
                                class="w-full rounded border-gray-300"
                                :disabled="isActiveLot"
                                required />
                        </div>
                        <div>
                            <label class="text-sm font-medium">
                                Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local"
                                name="end_at"
                                x-model="form.end_at"
                                class="w-full rounded border-gray-300"
                                required />
                            @error('end_at')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- INFO ACTIVE --}}
                <p x-show="isActiveLot"
                class="mb-4 mt-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                    Catatan: Lelang sedang berjalan. Anda hanya dapat mengubah
                    <span class="font-semibold">Waktu Selesai</span>.
                </p>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 rounded bg-slate-100" @click="closeEdit()">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded bg-yellow-500 font-semibold text-slate-900">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: CANCEL --}}
    <div x-show="cancelOpen" x-cloak class="fixed inset-0 z-[110] flex items-start justify-center overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" @click="closeCancel()"></div>
    <div class="relative w-full max-w-md mx-4 sm:mx-0 mt-10 mb-6 rounded-xl bg-white shadow-xl p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Batalkan Lelang</h3>
          <button @click="closeCancel()">✕</button>
        </div>

        <form method="POST" :action="cancelAction">
          @csrf
          <input type="hidden" name="tab" :value="tab"> 

          <p class="text-sm text-slate-600 mb-3">
            Silakan isi alasan pembatalan lelang ini.
          </p>

          <div class="mb-4">
            <label class="text-sm font-medium mb-1 block">
              Alasan Pembatalan <span class="text-red-500">*</span>
            </label>
            <textarea name="cancel_reason"
                      x-model="cancelReason"
                      rows="3"
                      class="w-full rounded border-gray-300 text-sm"
                      required></textarea>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button"
                    class="px-4 py-2 rounded bg-slate-100"
                    @click="closeCancel()">Batal</button>

            <button type="submit"
                    class="px-4 py-2 rounded bg-red-600 text-white font-semibold">
              Batalkan Lelang
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>

  {{-- Scripts --}}
    <script>
        function lotPage (initialTab = 'scheduled') {
            return {
                tab: initialTab,

                // modal states
                createOpen: false,
                editOpen: false,
                cancelOpen: false,

                // actions
                cancelAction: '#',
                cancelReason: '',
                updateAction: '#',

                // form state
                form: {},
                isActiveLot: false,

                // base
                baseUrl: "{{ route('lots.index') }}",

                init() {
                    this._wirePagination();

                    // reopen create modal kalau gagal validasi server
                    @if(session('reopen_create'))
                        this.createOpen = true;
                    @endif

                    // reopen edit modal kalau gagal validasi server (kalau kamu sudah pakai flash reopen_edit)
                    @if(session('reopen_edit'))
                        const d = @json(session('reopen_edit'));
                        this.openEdit(d);
                    @endif
                },

                changeTab(newTab) {
                this.tab = newTab;

                if (this.$refs.filterForm) {
                    const tabInput = this.$refs.filterForm.querySelector('input[name="tab"]');
                    if (tabInput) tabInput.value = newTab;
                }

                const url = new URL(window.location.href);
                url.searchParams.set('tab', newTab);
                history.replaceState({}, '', url.toString());
                },

                // ========= helpers =========
                formatNumberString(value) {
                    if (value === null || value === undefined || value === '') return '';
                    const num = Number(value);
                    if (isNaN(num)) return '';
                    const intStr = Math.round(num).toString();
                    return intStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },

                parseMoney(val) {
                    // "1.000.000" -> 1000000
                    if (val === null || val === undefined) return NaN;
                    const digits = String(val).replace(/[^\d]/g, '');
                    return digits ? Number(digits) : NaN;
                },

                toLocalDatetimeValue(d) {
                    const pad = (n) => String(n).padStart(2,'0');
                    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                },

                nowMinValue() {
                    const now = new Date();
                    now.setSeconds(0,0);
                    return this.toLocalDatetimeValue(now);
                },

                // ambil form yang sedang aktif (create/edit) supaya querySelector tidak nyasar
                getFormEls(formEl) {
                    return {
                        start: formEl.querySelector('input[name="start_at"]'),
                        end:   formEl.querySelector('input[name="end_at"]'),
                        sp:    formEl.querySelector('input[name="start_price"]'),
                        inc:   formEl.querySelector('input[name="increment"]'),
                    };
                },

                // set aturan min untuk start/end:
                // - start.min = nowMin (kalau start editable)
                // - end.min = max(nowMin, start.value)
                // - jika end <= min -> dorong ke min
                syncMinDatetime(formEl) {
                    const { start, end } = this.getFormEls(formEl);
                    if (!start || !end) return;

                    const nowMin = this.nowMinValue();

                    // start only editable when scheduled
                    if (!start.disabled) {
                        start.min = nowMin;
                        if (start.value && start.value < nowMin) start.value = nowMin;
                }

                const s = start.value || nowMin;
                const endMin = (s > nowMin) ? s : nowMin;

                end.min = endMin;
                    if (end.value && end.value <= endMin) end.value = endMin;

                    // update end.min saat start berubah (kalau start editable)
                    if (!start.disabled) {
                        start.onchange = () => {
                        const nowMin2 = this.nowMinValue();
                        const s2 = start.value || nowMin2;
                        const m2 = (s2 > nowMin2) ? s2 : nowMin2;
                        end.min = m2;
                        if (end.value && end.value <= m2) end.value = m2;
                        };
                    }
                },

                // ========= open/close modals =========
                openCreate() {
                    this.createOpen = true;

                    this.$nextTick(() => {
                        if (this.$refs.createForm) this.syncMinDatetime(this.$refs.createForm);
                    });
                },

                openEdit(data) {
                    this.form = { ...data };
                    this.isActiveLot = (data.runtime_status === 'ACTIVE');

                    // format angka (edit pakai text dengan titik)
                    this.form.start_price = this.formatNumberString(this.form.start_price);
                    this.form.increment   = this.formatNumberString(this.form.increment);

                    this.updateAction = `/admin/lots/${data.id}`;
                    this.editOpen = true;

                    this.$nextTick(() => {
                        if (this.$refs.editForm) this.syncMinDatetime(this.$refs.editForm);
                    });
                },

                closeEdit() { this.editOpen = false; },

                openCancel(actionUrl) {
                    this.cancelAction = actionUrl;
                    this.cancelReason = '';
                    this.cancelOpen = true;
                },

                closeCancel() { this.cancelOpen = false; },

                // ========= submit with native tooltip (seperti "nilai terlalu kecil") =========
                async confirmAndSubmit(formEl, title, msg, yes='OK') {
                    const { start, end, sp, inc } = this.getFormEls(formEl);

                    // reset semua custom error
                    [start, end, sp, inc].forEach(el => el && el.setCustomValidity(''));

                    const nowMin = this.nowMinValue(); // "YYYY-MM-DDTHH:mm"

                    const isEditable = (el) => el && !el.disabled;

                    const parseNumberField = (el) => {
                        if (!el) return NaN;
                        if (el.type === 'number') return Number(el.value);
                        return this.parseMoney(el.value); // text "1.000.000" -> 1000000
                    };
                    
                    // VALIDASI BISNIS 
                    
                    // START
                    if (isEditable(start)) {
                        if (!start.value) start.setCustomValidity('Waktu mulai wajib diisi.');
                        else if (start.value < nowMin) start.setCustomValidity('Waktu mulai tidak boleh sebelum waktu saat ini.');
                    }

                    // END
                    if (end) {
                        if (!end.value) end.setCustomValidity('Waktu selesai wajib diisi.');
                        else {
                        const s = start ? start.value : null;
                        const e = end.value;

                        if (s && e <= s) end.setCustomValidity('Waktu selesai harus setelah waktu mulai.');
                        else if (e <= nowMin) end.setCustomValidity('Waktu selesai harus setelah waktu saat ini.');
                        }
                    }

                    // START PRICE
                    if (isEditable(sp)) {
                        const v = parseNumberField(sp);
                        if (Number.isNaN(v)) sp.setCustomValidity('Harga awal wajib diisi.');
                        else if (v < 0) sp.setCustomValidity('Harga awal tidak boleh negatif.');
                    }

                    // INCREMENT
                    if (isEditable(inc)) {
                        const v = parseNumberField(inc);
                        if (Number.isNaN(v)) inc.setCustomValidity('Increment wajib diisi.');
                        else {
                        const minInc = (inc.type === 'number') ? 0.01 : 1;
                        if (v < minInc) inc.setCustomValidity(`Increment minimal ${minInc}.`);
                        }
                    }

                    // OVERRIDE VALIDITY NATIVE
                    
                    const forceNativeMessage = (el, map) => {
                        if (!el || !el.validity) return;

                        // kalau sudah ada error custom dari kita, jangan ditimpa
                        if (el.validity.customError) return;

                        if (el.validity.valueMissing && map.valueMissing) {
                        el.setCustomValidity(map.valueMissing);
                        return;
                        }
                        if (el.validity.rangeUnderflow && map.rangeUnderflow) {
                        el.setCustomValidity(map.rangeUnderflow);
                        return;
                        }
                        if (el.validity.stepMismatch && map.stepMismatch) {
                        el.setCustomValidity(map.stepMismatch);
                        return;
                        }
                        if (el.validity.badInput && map.badInput) {
                        el.setCustomValidity(map.badInput);
                        return;
                        }
                    };

                    // datetime-local
                    forceNativeMessage(start, {
                        valueMissing: 'Waktu mulai wajib diisi.',
                        rangeUnderflow: 'Waktu mulai tidak boleh sebelum waktu saat ini.',
                        badInput: 'Format waktu mulai tidak valid.',
                    });

                    forceNativeMessage(end, {
                        valueMissing: 'Waktu selesai wajib diisi.',
                        rangeUnderflow: 'Waktu selesai tidak valid.',
                        badInput: 'Format waktu selesai tidak valid.',
                    });

                    // number/text price & increment
                    forceNativeMessage(sp, {
                        valueMissing: 'Harga awal wajib diisi.',
                        rangeUnderflow: 'Harga awal tidak boleh negatif.',
                        stepMismatch: 'Format harga tidak valid.',
                        badInput: 'Harga awal harus berupa angka.',
                    });

                    forceNativeMessage(inc, {
                        valueMissing: 'Increment wajib diisi.',
                        rangeUnderflow: (inc && inc.type === 'number') ? 'Increment minimal 0.01.' : 'Increment minimal 1.',
                        stepMismatch: 'Format increment tidak valid.',
                        badInput: 'Increment harus berupa angka.',
                    });

                    // STOP kalau invalid (tooltip keluar)
                    if (!formEl.reportValidity()) return;

                    const ok = await Alpine.store('dialog').confirm({
                        title,
                        message: msg,
                        confirmText: yes,
                    });

                    if (ok) formEl.submit();
                },

                // ========= filtering ajax =========
                applyFilter() {
                    const form = this.$refs.filterForm;
                    const params = new URLSearchParams(new FormData(form));
                    this._swap(`${this.baseUrl}?${params.toString()}`);
                },

                async _swap(url) {
                    const res  = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
                    const html = await res.text();
                    const tmp  = document.createElement('div');
                    tmp.innerHTML = html;

                    const newWrap = tmp.querySelector('[x-ref="tableWrap"]');
                    if (newWrap && this.$refs.tableWrap) {
                        this.$refs.tableWrap.innerHTML = newWrap.innerHTML;
                }

                this._wirePagination();
                    history.replaceState({}, '', url);
                },

                _wirePagination() {
                    if (!this.$refs.tableWrap) return;
                    this.$refs.tableWrap
                        .querySelectorAll('a[href*="page="]')
                        .forEach(a => {
                        a.addEventListener('click', (e) => {
                            e.preventDefault();
                            this._swap(a.href);
                        }, { once: true });
                    });
                },
            }
        }
    </script>

</x-app-layout>