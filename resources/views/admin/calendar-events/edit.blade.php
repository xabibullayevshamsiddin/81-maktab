@extends('admin.layouts.main')

@section('title', 'Taqvim: tahrirlash')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-8">
        <div class="card-style mb-30">
          <h6 class="mb-20">Tahrirlash</h6>
          <form method="POST" action="{{ route('calendar-events.update', $event) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
              <label class="form-label">Sarlavha</label>
              <input type="text" name="title" class="form-control" value="{{ old('title', $event->title) }}" required maxlength="255">
            </div>
            <div class="mb-3">
              <label class="form-label">Sana</label>
              <input type="date" name="event_date" class="form-control" value="{{ old('event_date', $event->event_date->toDateString()) }}" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Vaqt (ixtiyoriy)</label>
              <input type="text" name="time_note" class="form-control" value="{{ old('time_note', $event->time_note) }}" maxlength="64">
            </div>
            <div class="mb-3">
              <label class="form-label">Tavsif</label>
              <textarea name="body" class="form-control" rows="4">{{ old('body', $event->body) }}</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Tartib</label>
              <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $event->sort_order) }}" min="0" max="9999">
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
