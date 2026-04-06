@extends('admin.layouts.main')

@section('title', 'Izohlar')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title"><h2>Izohlar</h2></div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Izohlar</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}</div>
    @endif

    <div class="card-style mb-20">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-sm">Manba:</span>
        <a href="{{ route('admin.comments.index', ['type' => 'post']) }}" class="btn btn-sm {{ $type === 'post' ? 'btn-primary' : 'btn-outline-primary' }}">Yangiliklar postlari</a>
        <a href="{{ route('admin.comments.index', ['type' => 'teacher']) }}" class="btn btn-sm {{ $type === 'teacher' ? 'btn-primary' : 'btn-outline-primary' }}">Ustozlar sahifasi</a>
      </div>
    </div>

    @include('admin.partials.search-bar', [
      'placeholder' => $type === 'post' ? 'Izoh, muallif, post sarlavhasi...' : 'Izoh yoki muallif...',
      'action' => route('admin.comments.index'),
      'hidden' => ['type' => $type],
    ])

    <div class="card-style mb-30">
      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th><h6>#</h6></th>
              <th><h6>Matn</h6></th>
              <th><h6>Muallif</h6></th>
              <th><h6>Bog‘liq</h6></th>
              <th><h6>Javob</h6></th>
              <th><h6>Sana</h6></th>
              <th><h6>Amallar</h6></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($comments as $comment)
              <tr>
                <td><p>{{ $comment->id }}</p></td>
                <td><p style="max-width:320px;"><strong>{{ \Illuminate\Support\Str::limit($comment->body, 120) }}</strong></p></td>
                <td>
                  <p>
                    @if($comment->user)
                      {{ $comment->user->name }}<br>
                      <small class="text-muted">{{ $comment->user->email }}</small>
                    @else
                      {{ $comment->author_name ?? 'Mehmon' }}
                    @endif
                  </p>
                </td>
                <td>
                  @if($type === 'post' && $comment->post)
                    <a href="{{ route('post.show', $comment->post->slug) }}" target="_blank" rel="noopener">{{ $comment->post->title }}</a>
                  @elseif($type === 'teacher' && $comment->teacher)
                    <a href="{{ route('teacher.show', $comment->teacher) }}" target="_blank" rel="noopener">{{ $comment->teacher->full_name }}</a>
                  @elseif($type === 'teacher')
                    <span class="text-muted">Biriktirilmagan ustoz</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  @if($comment->parent_id)
                    <span class="badge bg-warning text-dark">Javob #{{ $comment->parent_id }}</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td><p>{{ $comment->created_at?->format('Y-m-d H:i') }}</p></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    @if(auth()->user()->canModerateCommentAuthor($comment->user))
                      <a href="{{ route('admin.comments.edit', ['type' => $type, 'id' => $comment->id]) }}" class="btn btn-sm btn-warning">Tahrirlash</a>
                      <form action="{{ route('admin.comments.destroy', ['type' => $type, 'id' => $comment->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Izohni o‘chirasizmi?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">O‘chirish</button>
                      </form>
                    @else
                      <span class="text-muted small">Huquq yo‘q</span>
                    @endif
                    @if($comment->user && auth()->user()->canManageSystem() && auth()->user()->canManage($comment->user) && (int)$comment->user->id !== (int)auth()->id())
                      <form action="{{ route('admin.comments.block-user', $comment->user) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu foydalanuvchini bloklaysizmi?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-dark">Bloklash</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="7"><p>Hozircha izohlar yo‘q.</p></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($comments->hasPages())
        <div class="p-3">{{ $comments->links() }}</div>
      @endif
    </div>
  </div>
</section>
@endsection
