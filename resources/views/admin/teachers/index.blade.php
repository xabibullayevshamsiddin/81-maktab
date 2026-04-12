@extends('admin.layouts.main')

@section('title', 'Ustozlar')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title"><h2>Ustozlar</h2></div>
        </div>
      </div>
    </div>

    <div class="tables-wrapper">
      <div class="row">
        <div class="col-lg-12">
          <div class="card-style mb-30">
            <div class="d-flex justify-content-between align-items-center mb-20">
              <h6 class="mb-0">Ustozlar ro'yxati</h6>
              <a href="{{ route('teachers.create') }}" class="main-btn primary-btn btn-hover btn-sm">Ustoz qo'shish</a>
            </div>

            @include('admin.partials.search-bar', [
              'placeholder' => 'Ism, lavozim, fan, bog\'langan user email/telefon...',
              'action' => route('teachers.index'),
            ])

            <div class="table-wrapper table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th><h6>#</h6></th>
                    <th><h6>Rasm</h6></th>
                    <th><h6>F.I.Sh</h6></th>
                    <th><h6>Lavozim</h6></th>
                    <th><h6>Staj</h6></th>
                    <th><h6>Toifa</h6></th>
                    <th><h6>Bog'langan user</h6></th>
                    <th><h6>Fan</h6></th>
                    <th><h6>Status</h6></th>
                    <th><h6>Amallar</h6></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($teachers as $teacher)
                    <tr>
                      <td><p>{{ $teacher->id }}</p></td>
                      <td>
                        @if($teacher->image)
                          <img src="{{ app_storage_asset($teacher->image) }}" alt="{{ $teacher->full_name }}" style="width:56px;height:56px;object-fit:cover;border-radius:10px;">
                        @else
                          <span class="badge bg-secondary">Rasm yo'q</span>
                        @endif
                      </td>
                      <td><p><strong>{{ $teacher->full_name }}</strong></p></td>
                      <td><p>{{ $teacher->lavozim ?: '—' }}</p></td>
                      <td><p>{{ $teacher->experience_years }} yil</p></td>
                      <td><p>{{ $teacher->toifa ?: '—' }}</p></td>
                      <td>
                        <p>
                          @if($teacher->user)
                            {{ $teacher->user->name }}<br>
                            <small style="color:#64748b;">{{ $teacher->user->email }}</small>
                          @else
                            —
                          @endif
                        </p>
                      </td>
                      <td><p>{{ $teacher->subject ?: '—' }}</p></td>
                      <td>
                        <span class="badge {{ $teacher->is_active ? 'bg-success' : 'bg-danger' }}">
                          {{ $teacher->is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                      </td>
                      <td>
                        <div class="action">
                          <a href="{{ route('teachers.show', $teacher) }}" class="text-primary me-2"><i class="lni lni-eye"></i></a>
                          <a href="{{ route('teachers.edit', $teacher) }}" class="text-warning me-2"><i class="lni lni-pencil"></i></a>
                          <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" style="display:inline;" data-confirm="Ustozni o'chirmoqchimisiz?" data-confirm-title="Ustozni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger" style="background:none;border:none;padding:0;"><i class="lni lni-trash-can"></i></button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10"><p>Hozircha ustozlar qo'shilmagan.</p></td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @if($teachers->hasPages())
              <div class="p-3">
                {{ $teachers->links() }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
