@extends('admin.layouts.main')

@section('title', 'Taqvim: yangi tadbir')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-8">
        <div class="card-style mb-30">
          <h6 class="mb-20">Yangi tadbir</h6>
          <form method="POST" action="{{ route('calendar-events.store') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Sarlavha</label>
              <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255">
            </div>
            <div class="mb-3">
              <label class="form-label">Sana</label>
              <input type="date" name="event_date" class="form-control" value="{{ old('event_date', now()->toDateString()) }}" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Vaqt (ixtiyoriy, matn)</label>
              <input type="text" name="time_note" class="form-control" value="{{ old('time_note') }}" placeholder="09:00–12:00" maxlength="64">
            </div>
            <div class="mb-3">
              <label class="form-label">Tavsif</label>
              <textarea name="body" class="form-control" rows="4">{{ old('body') }}</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Tartib</label>
              <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0" max="9999">
            </div>
            <button type="submit" class="btn btn-primary">Saqlash</button>
            <a href="{{ route('calendar-events.index') }}" class="btn btn-outline-secondary">Orqaga</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
