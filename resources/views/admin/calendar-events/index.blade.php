@extends('admin.layouts.main')

@section('title', 'Taqvim tadbirlari')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title"><h2>Taqvim</h2></div>
        </div>
        <div class="col-md-6 text-end">
          <a href="{{ route('calendar-events.create') }}" class="main-btn primary-btn btn-hover">Yangi tadbir</a>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card-style mb-30">
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <div class="table-wrapper table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Sana</th>
                  <th>Sarlavha</th>
                  <th>Vaqt</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($events as $ev)
                  <tr>
                    <td>{{ $ev->event_date->format('d.m.Y') }}</td>
                    <td>{{ $ev->title }}</td>
                    <td>{{ $ev->time_note ?: '—' }}</td>
                    <td>
                      <a href="{{ route('calendar-events.edit', $ev) }}" class="btn btn-sm btn-warning">Tahrirlash</a>
                      <form action="{{ route('calendar-events.destroy', $ev) }}" method="POST" class="d-inline" onsubmit="return confirm('O‘chirilsinmi?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">O‘chirish</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center text-muted">Tadbir yo‘q.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($events->hasPages())
            <div class="mt-3">{{ $events->links() }}</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
