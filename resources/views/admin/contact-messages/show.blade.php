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
      <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;">
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
        <dt class="col-sm-3 text-muted">Holat</dt>
        <dd class="col-sm-9">
          @if($message->is_blocked)
            <span class="badge bg-danger">Bloklangan</span>
          @elseif($message->read_at)
            <span class="badge bg-success">O‘qilgan</span>
            @if($message->readBy)
              <span class="text-muted text-sm">({{ $message->readBy->first_name ?: $message->readBy->name }}, {{ $message->read_at->format('d.m.Y H:i') }})</span>
            @endif
          @else
            <span class="badge bg-warning text-dark">O‘qilmagan</span>
          @endif
        </dd>
        <dt class="col-sm-3 text-muted">Ism</dt>
        <dd class="col-sm-9">{{ $message->name }}</dd>
        <dt class="col-sm-3 text-muted">Email</dt>
        <dd class="col-sm-9">
          <a href="{{ gmail_compose_url($message->email, $replySubject, $replyBody) }}" target="_blank" rel="noopener">{{ $message->email }}</a>
        </dd>
        <dt class="col-sm-3 text-muted">Telefon</dt>
        <dd class="col-sm-9">{{ $message->phone }}</dd>
        <dt class="col-sm-3 text-muted">Izoh / mavzu</dt>
        <dd class="col-sm-9" style="white-space:pre-wrap;">{{ $message->note ?: '—' }}</dd>
        <dt class="col-sm-3 text-muted">Xabar</dt>
        <dd class="col-sm-9" style="white-space:pre-wrap;word-break:break-word;">{{ $message->message }}</dd>
      </dl>

      <div class="mt-4 d-flex flex-wrap gap-2">
        @if(!$message->is_blocked)
          <form method="POST" action="{{ route('admin.contact-messages.block', $message) }}" class="d-inline" onsubmit="return confirm('Bu xabarni bloklaysizmi? (spam yoki arxaiv)');">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">Bloklash</button>
          </form>
        @else
          <form method="POST" action="{{ route('admin.contact-messages.unblock', $message) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">Blokdan chiqarish</button>
          </form>
        @endif
        <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="d-inline" onsubmit="return confirm('Butunlay o‘chirilsinmi?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-sm btn-danger">O‘chirish</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
