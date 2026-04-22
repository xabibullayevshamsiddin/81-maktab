@extends('admin.layouts.main')

@section('title', 'Aloqa xabari #' . $message->id)

@section('content')
  @php
    $replySubject = "81-IDUM murojaatiga javob";
    $replyBody = "Assalomu alaykum, {$message->name}.\n\nSiz yuborgan murojaat bo'yicha javob:\n";
  @endphp
  <div class="row">
    <div class="col-lg-10">
      <div class="card-style mb-30">
        <div
          style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;">
          <div>
            <h6 class="mb-5">Xabar #{{ $message->id }}</h6>
            <p class="text-sm mb-0" style="color:#64748b;">{{ $message->created_at->format('d.m.Y H:i') }}</p>
          </div>
          <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-sm btn-outline-secondary">← Ro‘yxatga</a>
        </div>

        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}</div>
        @endif

        <dl class="row mb-0" style="font-size:14px;">
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <h5 class="mb-4 text-primary">Xabar ma'lumotlari</h5>
              <div class="row mb-3">
                <div class="col-md-3 text-muted">Holati:</div>
                <div class="col-md-9">
                  @if($message->is_blocked)
                    <span class="badge bg-danger shadow-sm">Bloklangan</span>
                  @elseif(!$message->read_at)
                    <span class="badge bg-warning text-dark shadow-sm">Yangi</span>
                  @else
                    <span class="badge bg-success shadow-sm">O'qilgan</span>
                  @endif
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 text-muted">Yuboruvchi:</div>
                <div class="col-md-9">
                  <strong>{{ $message->name }}</strong>
                  @if($message->senderUser && !$message->senderUser->isActive())
                    <span class="badge bg-danger ms-2" style="font-size:10px;">🚫 Bloklangan akkaunt</span>
                  @endif
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 text-muted">Email:</div>
                <div class="col-md-9">
                  <a href="{{ gmail_compose_url($message->email, '81-IDUM murojaati bo\'yicha') }}"
                    target="_blank">{{ $message->email }}</a>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 text-muted">Telefon:</div>
                <div class="col-md-9">{{ $message->phone }}</dd>
              </div>
              @if($message->note)
                <div class="row mb-3">
                  <div class="col-md-3 text-muted">Izoh:</div>
                  <div class="col-md-9">{{ $message->note }}</div>
                </div>
              @endif
              <div class="row mb-4">
                <div class="col-md-3 text-muted">Sana:</div>
                <div class="col-md-9">{{ $message->created_at->format('d.m.Y H:i') }}</div>
              </div>

              <hr>

              <h5 class="mb-3 text-primary">Murojaat matni:</h5>
              <div class="p-4 bg-light rounded border mb-4" style="font-size:16px; line-height:1.6; white-space: pre-wrap;">{{ $message->message }}</div>

              @if($message->read_at)
                <div class="alert alert-light border shadow-sm mb-0" style="font-size:13px;">
                  <i class="fa-solid fa-eye me-1"></i>
                  {{ $message->readBy->name ?? 'Admin' }} tomonidan o'qildi ({{ $message->read_at->diffForHumans() }})
                </div>
              @endif
            </div>
          </div>
        </div>

          <div class="mt-4 d-flex flex-wrap gap-2">
            @if(!$message->is_blocked)
              <form method="POST" action="{{ route('admin.contact-messages.block', $message) }}" class="d-inline"
                data-confirm="Bu xabarni bloklaysizmi? (spam yoki arxaiv)" data-confirm-title="Bloklash"
                data-confirm-variant="danger" data-confirm-ok="Bloklash">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning">Bloklash</button>
              </form>
            @else
              <form method="POST" action="{{ route('admin.contact-messages.unblock', $message) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Blokdan chiqarish</button>
              </form>
            @endif
            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="d-inline"
              data-confirm="Butunlay o‘chirilsinmi?" data-confirm-title="Xabarni o'chirish" data-confirm-variant="danger"
              data-confirm-ok="O'chirish">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger">O‘chirish</button>
            </form>
          </div>
      </div>
    </div>
  </div>
@endsection