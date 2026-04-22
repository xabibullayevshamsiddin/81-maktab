@extends('admin.layouts.main')

@section('title', 'Aloqa xabarlari')

@section('content')
@php
  $canInbox = auth()->user()->canManageInbox();
  $filterStatus = $status ?? 'all';
@endphp
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;">
        <div>
          <h6 class="mb-5">Aloqa xabarlari</h6>
          <p class="text-sm mb-0" style="color:#64748b;">Sayt aloqasi orqali yuborilgan xabarlar. Moderator, editor va adminlar ko‘rishi mumkin.</p>
        </div>
      </div>

      <div class="alert alert-light border mb-4" style="font-size:13px;line-height:1.55;color:#334155;">
        <strong>Nima qilish mumkin:</strong>
        <ul class="mb-0 mt-2 ps-3">
          <li><strong>O‘qilgan:</strong> xabarni ochganingizda (to‘liq ko‘rinish) yoki «O‘qilgan» tugmasi orqali bir marta belgilanadi; keyinroq qayta «yangi» bo‘lib qolmaysiz — holat saqlanadi.</li>
          <li><strong>Bloklash:</strong> spam yoki keraksiz murojaatni arxaiv/ blok qilib belgilash (ro‘yxatda alohida filtr).</li>
          <li><strong>O‘chirish:</strong> xabarni butunlay olib tashlash.</li>
        </ul>
      </div>

      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}</div>
      @endif

      <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
        <span class="text-sm text-muted me-1">Filtr:</span>
        @foreach ([
          'all' => 'Barchasi',
          'unread' => 'O‘qilmagan',
          'read' => 'O‘qilgan',
          'blocked' => 'Bloklangan',
        ] as $key => $label)
          <a
            href="{{ route('admin.contact-messages.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => request('q')])) }}"
            class="btn btn-sm {{ $filterStatus === $key ? 'btn-primary' : 'btn-outline-secondary' }}"
          >{{ $label }}</a>
        @endforeach
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => 'Ism, email, telefon yoki matn bo‘yicha...',
        'action' => route('admin.contact-messages.index'),
        'hidden' => array_filter(['status' => ($filterStatus === 'all' ? null : $filterStatus)]),
      ])

      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Sana</th>
              <th>Holat</th>
              <th>Ism</th>
              <th>Email</th>
              <th>Telefon</th>
              <th>Izoh</th>
              <th>Xabar</th>
              <th style="min-width:200px;">Amallar</th>
            </tr>
          </thead>
          <tbody>
            @forelse($messages as $row)
              @php
                $replySubject = "81-IDUM murojaatiga javob";
                $replyBody = "Assalomu alaykum, {$row->name}.\n\nSiz yuborgan murojaat bo'yicha javob:\n";
              @endphp
              <tr class="{{ $row->is_blocked ? 'table-secondary' : (!$row->read_at ? 'table-warning' : '') }}">
                <td>{{ $row->id }}</td>
                <td><span class="text-sm">{{ $row->created_at->format('d.m.Y H:i') }}</span></td>
                <td>
                  @if($row->is_blocked)
                    <span class="badge bg-danger">Blok</span>
                  @elseif($row->read_at)
                    <span class="badge bg-success">O‘qilgan</span>
                  @else
                    <span class="badge bg-warning text-dark">Yangi</span>
                  @endif
                </td>
                <td>
                  {{ $row->name }}
                  @if($row->senderUser && !$row->senderUser->isActive())
                    <br><span class="badge bg-danger mt-1 shadow-sm" style="font-size:11px; letter-spacing:0.5px;">🚫 Tizimdan bloklangan</span>
                  @endif
                </td>
                <td>
                  <a
                    href="{{ gmail_compose_url($row->email, $replySubject, $replyBody) }}"
                    target="_blank"
                    rel="noopener"
                  >
                    {{ $row->email }}
                  </a>
                </td>
                <td>{{ $row->phone }}</td>
                <td style="max-width:180px;white-space:pre-wrap;word-break:break-word;">{{ \Illuminate\Support\Str::limit($row->note ?: '—', 80) }}</td>
                <td style="max-width:200px;white-space:pre-wrap;word-break:break-word;">{{ \Illuminate\Support\Str::limit($row->message, 120) }}</td>
                <td>
                  <div class="d-flex flex-column gap-1">
                    <a href="{{ route('admin.contact-messages.show', $row) }}" class="btn btn-sm btn-primary">Ko‘rish</a>
                    @if(!$row->read_at)
                      <form method="POST" action="{{ route('admin.contact-messages.read', $row) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success w-100">O‘qilgan qilib belgilash</button>
                      </form>
                    @endif
                    @if(!$row->is_blocked)
                      <form method="POST" action="{{ route('admin.contact-messages.block', $row) }}" class="d-inline" data-confirm="Bloklaysizmi?" data-confirm-title="Bloklash" data-confirm-variant="danger" data-confirm-ok="Bloklash">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning w-100">Bloklash</button>
                      </form>
                    @else
                      <form method="POST" action="{{ route('admin.contact-messages.unblock', $row) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Blokdan chiqarish</button>
                      </form>
                    @endif
                    @if($canInbox)
                      <form method="POST" action="{{ route('admin.contact-messages.destroy', $row) }}" class="d-inline" data-confirm="O‘chirilsinmi?" data-confirm-title="Xabarni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger w-100">O‘chirish</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">Xabar yo‘q.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($messages->hasPages())
        <div class="mt-3">{{ $messages->links() }}</div>
      @endif
    </div>
  </div>
</div>
@endsection
