@extends('admin.layouts.main')

@section('title', 'Aloqa xabarlari')

@section('content')
@php
  $canDeleteMessages = auth()->user()->canManageSystem();
@endphp
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;">
        <div>
          <h6 class="mb-5">Aloqa xabarlari</h6>
          <p class="text-sm mb-0" style="color:#64748b;">Sayt aloqasi orqali yuborilgan xabarlar.</p>
        </div>
      </div>

      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}</div>
      @endif

      @include('admin.partials.search-bar', [
        'placeholder' => 'Ism, email, telefon yoki matn bo‘yicha...',
        'action' => route('admin.contact-messages.index'),
      ])

      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Sana</th>
              <th>Ism</th>
              <th>Email</th>
              <th>Telefon</th>
              <th>shikoyat</th>
              <th>Xabar</th>
              @if($canDeleteMessages)
                <th></th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($messages as $row)
              @php
                $replySubject = "81-IDUM murojaatiga javob";
                $replyBody = "Assalomu alaykum, {$row->name}.\n\nSiz yuborgan murojaat bo'yicha javob:\n";
              @endphp
              <tr>
                <td>{{ $row->id }}</td>
                <td><span class="text-sm">{{ $row->created_at->format('d.m.Y H:i') }}</span></td>
                <td>{{ $row->name }}</td>
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
                <td style="max-width:220px;white-space:pre-wrap;word-break:break-word;">{{ $row->note ?: '—' }}</td>
                <td style="max-width:280px;white-space:pre-wrap;word-break:break-word;">{{ \Illuminate\Support\Str::limit($row->message, 400) }}</td>
                @if($canDeleteMessages)
                  <td>
                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $row) }}" class="d-inline" onsubmit="return confirm('Bu xabar o‘chirilsinmi?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger">O‘chirish</button>
                    </form>
                  </td>
                @endif
              </tr>
            @empty
              <tr>
                <td colspan="{{ $canDeleteMessages ? 8 : 7 }}" class="text-center text-muted py-4">Xabar yo‘q.</td>
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
